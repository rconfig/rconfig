import { describe, expect, it, vi } from "vitest";

// Stub the layout components so the registry does not pull in heavy component trees.
vi.mock("@/layouts/NavigationTop.vue", () => ({ default: { name: "NavigationTop" } }));
vi.mock("@/layouts/NavigationSide.vue", () => ({ default: { name: "NavigationSide" } }));

import * as navImports from "../navComponentImports";

describe("navComponentImports", () => {
	it("re-exports the two navigation components", () => {
		expect(Object.keys(navImports).sort()).toEqual(["NavigationSide", "NavigationTop"]);
	});

	it("exports objects, not undefined", () => {
		expect(navImports.NavigationTop).toBeTruthy();
		expect(navImports.NavigationSide).toBeTruthy();
	});
});
