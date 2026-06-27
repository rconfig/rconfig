import { beforeEach, describe, expect, it } from "vitest";
import { setActivePinia, createPinia } from "pinia";

// @ts-ignore — store is JS, no type declarations
import { useDialogStore } from "../dialogActions";

describe("useDialogStore", () => {
	beforeEach(() => {
		setActivePinia(createPinia());
	});

	it("starts with an empty dialogs object", () => {
		const store = useDialogStore();
		expect(store.dialogs).toEqual({});
	});

	it("openDialog sets the named dialog to true", () => {
		const store = useDialogStore();
		store.openDialog("settings");
		expect(store.dialogs.settings).toBe(true);
	});

	it("closeDialog sets the named dialog to false", () => {
		const store = useDialogStore();
		store.openDialog("settings");
		store.closeDialog("settings");
		expect(store.dialogs.settings).toBe(false);
	});

	it("toggleDialog flips an unset dialog to true", () => {
		const store = useDialogStore();
		store.toggleDialog("help");
		expect(store.dialogs.help).toBe(true);
	});

	it("toggleDialog flips an open dialog back to false", () => {
		const store = useDialogStore();
		store.openDialog("help");
		store.toggleDialog("help");
		expect(store.dialogs.help).toBe(false);
	});

	it("isDialogOpen returns false for unknown dialogs", () => {
		const store = useDialogStore();
		expect(store.isDialogOpen("ghost")).toBe(false);
	});

	it("isDialogOpen reflects an open dialog", () => {
		const store = useDialogStore();
		store.openDialog("modal");
		expect(store.isDialogOpen("modal")).toBe(true);
	});
});
