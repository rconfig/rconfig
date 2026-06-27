import { beforeEach, describe, expect, it } from "vitest";
import { setActivePinia, createPinia } from "pinia";

// @ts-ignore — store is JS, no type declarations
import { usePanelStore } from "../panelStore";

describe("usePanelStore", () => {
	beforeEach(() => {
		setActivePinia(createPinia());
	});

	it("exposes two null panel refs by default", () => {
		const store = usePanelStore();
		expect(store.panelRef).toBeNull();
		expect(store.panelRef2).toBeNull();
	});

	it("allows assigning panel refs", () => {
		const store = usePanelStore();
		const collapse = () => {};
		store.panelRef = { collapse };
		expect(store.panelRef).not.toBeNull();
		expect(store.panelRef.collapse).toBe(collapse);
	});
});
