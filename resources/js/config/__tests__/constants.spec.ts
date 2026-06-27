import { describe, expect, it } from "vitest";

// @ts-ignore — JS module, no type declarations
import { RCONFIG_DOCS_URL } from "../constants";

describe("constants", () => {
	it("exposes the docs URL", () => {
		expect(RCONFIG_DOCS_URL).toBe("https://v8coredocs.rconfig.com");
	});

	it("is an https URL", () => {
		expect(RCONFIG_DOCS_URL.startsWith("https://")).toBe(true);
	});
});
