import { beforeEach, describe, expect, it, vi } from "vitest";

const highlightElement = vi.fn();

vi.mock("highlight.js", () => ({
	default: {
		highlightElement: (...args: unknown[]) => highlightElement(...args),
	},
}));

// @ts-ignore — JS module, no type declarations
import { highlightDirective } from "../highlightDirective";

function makeEl(html: string): HTMLElement {
	const el = document.createElement("pre");
	el.innerHTML = html;
	return el;
}

describe("highlightDirective", () => {
	beforeEach(() => {
		vi.clearAllMocks();
	});

	it("highlights every code node inside the bound element on mount", () => {
		const el = makeEl("<code>a</code><code>b</code>");

		highlightDirective.mounted(el, { value: undefined });

		const codes = el.querySelectorAll("code");
		expect(highlightElement).toHaveBeenCalledTimes(2);
		expect(highlightElement).toHaveBeenCalledWith(codes[0]);
		expect(highlightElement).toHaveBeenCalledWith(codes[1]);
	});

	it("uses the current highlightElement API, not the removed highlightBlock", () => {
		// Importing highlight.js here would re-import the mock; assert the directive
		// only ever reaches for highlightElement.
		const el = makeEl("<code>x</code>");

		highlightDirective.mounted(el, { value: undefined });

		expect(highlightElement).toHaveBeenCalledOnce();
	});

	it("sets the code text content when the binding value is a string", () => {
		const el = makeEl("<code>old</code>");

		highlightDirective.mounted(el, { value: "new content" });

		expect(el.querySelector("code")?.textContent).toBe("new content");
	});

	it("leaves existing content untouched when no string value is bound", () => {
		const el = makeEl("<code>keep</code>");

		highlightDirective.mounted(el, { value: undefined });

		expect(el.querySelector("code")?.textContent).toBe("keep");
	});

	it("re-highlights on update so reactive content stays highlighted", () => {
		const el = makeEl("<code>v1</code>");

		highlightDirective.mounted(el, { value: undefined });
		highlightDirective.updated(el, { value: undefined });

		expect(highlightElement).toHaveBeenCalledTimes(2);
	});

	it("clears the highlighted flag before re-highlighting updated content", () => {
		const el = makeEl("<code>v1</code>");
		const code = el.querySelector("code") as HTMLElement;
		code.dataset.highlighted = "yes";

		highlightDirective.updated(el, { value: undefined });

		expect(code.dataset.highlighted).toBeUndefined();
		expect(highlightElement).toHaveBeenCalledWith(code);
	});

	it("does nothing when there are no code nodes", () => {
		const el = makeEl("<span>no code here</span>");

		highlightDirective.mounted(el, { value: undefined });

		expect(highlightElement).not.toHaveBeenCalled();
	});
});
