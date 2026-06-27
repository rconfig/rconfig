import { beforeEach, describe, expect, it } from "vitest";
import { setActivePinia, createPinia } from "pinia";

// @ts-ignore — store is JS, no type declarations
import { useExternalLinksStore } from "../externalLinksStore";

describe("useExternalLinksStore", () => {
	beforeEach(() => {
		setActivePinia(createPinia());
	});

	it("starts with an empty links array", () => {
		const store = useExternalLinksStore();
		expect(store.links).toEqual([]);
	});

	it("setLinks replaces the links array", () => {
		const store = useExternalLinksStore();
		const links = [{ name: "Docs", url: "https://example.com" }];
		store.setLinks(links);
		expect(store.links).toEqual(links);
	});
});
