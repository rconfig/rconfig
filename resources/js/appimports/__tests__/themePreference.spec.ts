import { describe, expect, it } from "vitest";

// @ts-ignore — JS module, no type declarations
import { normalizeStoredTheme, resolveThemePreference } from "../themePreference";

describe("normalizeStoredTheme", () => {
	it("keeps the supported theme values", () => {
		expect(normalizeStoredTheme("light")).toBe("light");
		expect(normalizeStoredTheme("dark")).toBe("dark");
	});

	it("rejects anything that is not a supported theme", () => {
		expect(normalizeStoredTheme("auto")).toBeNull();
		expect(normalizeStoredTheme("")).toBeNull();
		expect(normalizeStoredTheme(null)).toBeNull();
		expect(normalizeStoredTheme(undefined)).toBeNull();
	});
});

describe("resolveThemePreference", () => {
	it("returns the stored preference when it is supported", () => {
		expect(resolveThemePreference("light")).toBe("light");
		expect(resolveThemePreference("dark")).toBe("dark");
	});

	it("falls back to dark rather than the system preference", () => {
		expect(resolveThemePreference("auto")).toBe("dark");
		expect(resolveThemePreference(null)).toBe("dark");
		expect(resolveThemePreference(undefined)).toBe("dark");
	});
});
