import { describe, expect, it } from "vitest";

import { cn } from "../utils";

describe("cn", () => {
	it("joins multiple class strings", () => {
		expect(cn("px-2", "py-1")).toBe("px-2 py-1");
	});

	it("keeps an object's truthy conditional classes and drops falsy ones", () => {
		expect(cn("base", { active: true, hidden: false })).toBe("base active");
	});

	it("flattens array inputs", () => {
		expect(cn(["text-sm", "font-bold"])).toBe("text-sm font-bold");
	});

	it("ignores falsy values like undefined, null and false", () => {
		expect(cn("a", undefined, null, false, "b")).toBe("a b");
	});

	it("lets the later tailwind class win on a conflict", () => {
		// twMerge resolves padding conflicts, last one declared wins.
		expect(cn("p-2", "p-4")).toBe("p-4");
	});

	it("resolves directional tailwind conflicts", () => {
		expect(cn("px-2", "px-6")).toBe("px-6");
	});

	it("keeps non conflicting tailwind utilities together", () => {
		expect(cn("text-red-500", "bg-blue-500")).toBe("text-red-500 bg-blue-500");
	});

	it("merges conditional and conflicting classes in one call", () => {
		expect(cn("p-2", { "p-4": true })).toBe("p-4");
	});

	it("returns an empty string when given no meaningful input", () => {
		expect(cn()).toBe("");
		expect(cn(false, null, undefined)).toBe("");
	});
});
