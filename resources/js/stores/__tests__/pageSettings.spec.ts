import { beforeEach, describe, expect, it } from "vitest";
import { setActivePinia, createPinia } from "pinia";

// @ts-ignore — store is JS, no type declarations
import { usePageSettingsStore } from "../pageSettings";

describe("usePageSettingsStore", () => {
	beforeEach(() => {
		localStorage.clear();
		setActivePinia(createPinia());
	});

	it("get returns the default when nothing is stored", () => {
		const store = usePageSettingsStore();
		expect(store.get("devices", "perPage", 10)).toBe(10);
	});

	it("set then get round-trips a value", () => {
		const store = usePageSettingsStore();
		store.set("devices", "perPage", 50);
		expect(store.get("devices", "perPage", 10)).toBe(50);
	});

	it("set persists to localStorage under rConfigPageSettings", () => {
		const store = usePageSettingsStore();
		store.set("configs", "sort", "desc");
		const raw = JSON.parse(localStorage.getItem("rConfigPageSettings") || "{}");
		expect(raw.configs.sort).toBe("desc");
	});

	it("keeps separate settings per page key", () => {
		const store = usePageSettingsStore();
		store.set("devices", "perPage", 25);
		store.set("configs", "perPage", 100);
		expect(store.get("devices", "perPage", 10)).toBe(25);
		expect(store.get("configs", "perPage", 10)).toBe(100);
	});

	it("falls back to the default for an unknown setting on a known page", () => {
		const store = usePageSettingsStore();
		store.set("devices", "perPage", 25);
		expect(store.get("devices", "columns", "all")).toBe("all");
	});

	it("get hydrates from existing localStorage state", () => {
		localStorage.setItem("rConfigPageSettings", JSON.stringify({ tasks: { perPage: 5 } }));
		const store = usePageSettingsStore();
		expect(store.get("tasks", "perPage", 10)).toBe(5);
	});
});
