import { beforeEach, describe, expect, it } from "vitest";
import { setActivePinia, createPinia } from "pinia";

// @ts-ignore — store is JS, no type declarations
import { useSheetStore } from "../sheetActions";

describe("useSheetStore", () => {
	beforeEach(() => {
		setActivePinia(createPinia());
	});

	it("starts with an empty sheets object", () => {
		const store = useSheetStore();
		expect(store.sheets).toEqual({});
	});

	it("openSheet sets the named sheet to true", () => {
		const store = useSheetStore();
		store.openSheet("help");
		expect(store.sheets.help).toBe(true);
	});

	it("closeSheet sets the named sheet to false", () => {
		const store = useSheetStore();
		store.openSheet("help");
		store.closeSheet("help");
		expect(store.sheets.help).toBe(false);
	});

	it("toggleSheet flips an unset sheet to true", () => {
		const store = useSheetStore();
		store.toggleSheet("nav");
		expect(store.sheets.nav).toBe(true);
	});

	it("toggleSheet flips an open sheet back to false", () => {
		const store = useSheetStore();
		store.openSheet("nav");
		store.toggleSheet("nav");
		expect(store.sheets.nav).toBe(false);
	});

	it("isSheetOpen returns false for unknown sheets", () => {
		const store = useSheetStore();
		expect(store.isSheetOpen("ghost")).toBe(false);
	});

	it("isSheetOpen reflects an open sheet", () => {
		const store = useSheetStore();
		store.openSheet("panel");
		expect(store.isSheetOpen("panel")).toBe(true);
	});
});
