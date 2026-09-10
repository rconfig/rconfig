import { describe, expect, it } from "vitest";

// @ts-ignore — resolved through the "@" alias in vitest.config.js
import { escapeHtml, escapeRegExp, highlightTerms } from "../htmlHighlight";

// The payload from the stored XSS report, rconfig/rconfig#368.
const XSS_PAYLOAD = '<img src=x onerror="window.__xss=1">';

describe("escapeHtml", () => {
	it("neutralises every character that carries meaning in markup", () => {
		expect(escapeHtml("<script>alert(1)</script>")).toBe("&lt;script&gt;alert(1)&lt;/script&gt;");
		expect(escapeHtml(`& < > " '`)).toBe("&amp; &lt; &gt; &quot; &#39;");
	});

	it("escapes ampersands before the entities it introduces", () => {
		expect(escapeHtml("&lt;")).toBe("&amp;lt;");
	});

	it("coerces non-string input rather than throwing", () => {
		expect(escapeHtml(null)).toBe("");
		expect(escapeHtml(undefined)).toBe("");
		expect(escapeHtml(42)).toBe("42");
	});
});

describe("escapeRegExp", () => {
	it("escapes regular expression metacharacters", () => {
		expect(escapeRegExp("a+b")).toBe("a\\+b");
		expect(escapeRegExp("10.0.0.1")).toBe("10\\.0\\.0\\.1");
	});
});

describe("highlightTerms", () => {
	it("escapes the payload when a term matches", () => {
		const result = highlightTerms(`hostname sw1\n${XSS_PAYLOAD}`, ["hostname"], { tag: "span", className: "highlightMatch" });

		expect(result).not.toContain("<img");
		expect(result).not.toContain("onerror=\"");
		expect(result).toContain("&lt;img src=x onerror=&quot;window.__xss=1&quot;&gt;");
	});

	it("escapes the payload when there are no terms at all", () => {
		// The pre-fix code returned this path completely unescaped.
		const result = highlightTerms(XSS_PAYLOAD, []);

		expect(result).not.toContain("<img");
		expect(result).toBe(escapeHtml(XSS_PAYLOAD));
	});

	it("escapes the payload when terms are present but none match", () => {
		const result = highlightTerms(XSS_PAYLOAD, ["nomatch"]);

		expect(result).not.toContain("<img");
		expect(result).toBe(escapeHtml(XSS_PAYLOAD));
	});

	it("escapes a search term that itself contains markup", () => {
		const result = highlightTerms(`prefix ${XSS_PAYLOAD} suffix`, [XSS_PAYLOAD], { tag: "span", className: "highlightMatch" });

		expect(result).not.toContain("<img");
		expect(result).toContain('<span class="highlightMatch">&lt;img src=x onerror=&quot;window.__xss=1&quot;&gt;</span>');
	});

	it("escapes markup that only appears in the unmatched tail", () => {
		const result = highlightTerms(`hostname sw1 ${XSS_PAYLOAD}`, ["hostname"]);

		expect(result).not.toContain("<img");
		expect(result).toContain("<mark>hostname</mark>");
	});

	it("wraps matches in the requested tag and class", () => {
		expect(highlightTerms("show run", ["run"], { tag: "span", className: "highlightMatch" })).toBe('show <span class="highlightMatch">run</span>');
		expect(highlightTerms("show run", ["run"])).toBe("show <mark>run</mark>");
	});

	it("matches case-insensitively while preserving the original casing", () => {
		expect(highlightTerms("Hostname", ["hostname"])).toBe("<mark>Hostname</mark>");
	});

	it("prefers the longest term when one is a prefix of another", () => {
		expect(highlightTerms("interface", ["inter", "interface"])).toBe("<mark>interface</mark>");
	});

	it("treats regex metacharacters in a term literally", () => {
		expect(highlightTerms("ip 10.0.0.1", ["10.0.0.1"])).toBe("ip <mark>10.0.0.1</mark>");
		expect(highlightTerms("ip 10x0y0z1", ["10.0.0.1"])).toBe("ip 10x0y0z1");
	});

	it("ignores blank, duplicate and non-string terms", () => {
		expect(highlightTerms("show run", ["  run  ", "run", "", null, undefined])).toBe("show <mark>run</mark>");
	});

	it("leaves newlines intact so callers can convert them to <br>", () => {
		expect(highlightTerms("a\nb", ["a"])).toBe("<mark>a</mark>\nb");
	});

	it("handles empty and nullish source text", () => {
		expect(highlightTerms("", ["a"])).toBe("");
		expect(highlightTerms(null, ["a"])).toBe("");
	});
});
