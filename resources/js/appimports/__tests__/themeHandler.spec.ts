import { afterEach, beforeEach, describe, expect, it } from "vitest";

// @ts-ignore — JS module, no type declarations
import { applyStoredTheme } from "../themeHandler";

describe("applyStoredTheme", () => {
	beforeEach(() => {
		localStorage.clear();
		document.documentElement.removeAttribute("data-theme");
	});

	afterEach(() => {
		localStorage.clear();
		document.documentElement.removeAttribute("data-theme");
	});

	it("sets data-theme from the stored value", () => {
		localStorage.setItem("theme", "dark");

		applyStoredTheme();

		expect(document.documentElement.getAttribute("data-theme")).toBe("dark");
	});

	it("does nothing when no theme is stored", () => {
		applyStoredTheme();

		expect(document.documentElement.hasAttribute("data-theme")).toBe(false);
	});
});
