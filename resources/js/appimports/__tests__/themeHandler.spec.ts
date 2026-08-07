import { afterEach, beforeEach, describe, expect, it } from "vitest";

// @ts-ignore — JS module, no type declarations
import { applyStoredTheme } from "../themeHandler";

function resetRoot() {
	localStorage.clear();
	document.documentElement.removeAttribute("data-theme");
	document.documentElement.removeAttribute("data-force-dark");
	document.documentElement.classList.remove("dark");
	document.documentElement.style.colorScheme = "";
}

describe("applyStoredTheme", () => {
	beforeEach(resetRoot);
	afterEach(resetRoot);

	it("applies the dark class from the stored value", () => {
		localStorage.setItem("theme", "dark");

		applyStoredTheme();

		expect(document.documentElement.getAttribute("data-theme")).toBe("dark");
		expect(document.documentElement.classList.contains("dark")).toBe(true);
		expect(document.documentElement.style.colorScheme).toBe("dark");
	});

	it("removes the dark class when light is stored", () => {
		document.documentElement.classList.add("dark");
		localStorage.setItem("theme", "light");

		applyStoredTheme();

		expect(document.documentElement.getAttribute("data-theme")).toBe("light");
		expect(document.documentElement.classList.contains("dark")).toBe(false);
		expect(document.documentElement.style.colorScheme).toBe("light");
	});

	it("reads the vueuse key when no explicit theme is stored", () => {
		localStorage.setItem("vueuse-color-scheme", "light");

		applyStoredTheme();

		expect(document.documentElement.getAttribute("data-theme")).toBe("light");
		expect(document.documentElement.classList.contains("dark")).toBe(false);
	});

	it("defaults to dark when nothing is stored", () => {
		applyStoredTheme();

		expect(document.documentElement.getAttribute("data-theme")).toBe("dark");
		expect(document.documentElement.classList.contains("dark")).toBe(true);
	});

	it("defaults to dark when the stored value is not a supported theme", () => {
		localStorage.setItem("vueuse-color-scheme", "auto");

		applyStoredTheme();

		expect(document.documentElement.getAttribute("data-theme")).toBe("dark");
		expect(document.documentElement.classList.contains("dark")).toBe(true);
	});

	it("forces dark when the layout opts in, even with light stored", () => {
		document.documentElement.setAttribute("data-force-dark", "true");
		localStorage.setItem("theme", "light");

		applyStoredTheme();

		expect(document.documentElement.getAttribute("data-theme")).toBe("dark");
		expect(document.documentElement.classList.contains("dark")).toBe(true);
		expect(document.documentElement.style.colorScheme).toBe("dark");
	});
});
