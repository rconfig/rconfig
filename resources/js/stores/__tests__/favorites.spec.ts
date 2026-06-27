import { beforeEach, describe, expect, it } from "vitest";
import { setActivePinia, createPinia } from "pinia";

// @ts-ignore — store is JS, no type declarations
import { useFavoritesStore } from "../favorites";

describe("useFavoritesStore", () => {
	beforeEach(() => {
		localStorage.clear();
		setActivePinia(createPinia());
	});

	it("starts empty when localStorage has no favorites", () => {
		const store = useFavoritesStore();
		expect(store.favorites.size).toBe(0);
	});

	it("hydrates from localStorage on creation", () => {
		localStorage.setItem("favorites", JSON.stringify([{ id: 1, label: "Saved" }]));
		const store = useFavoritesStore();
		expect(store.favorites.size).toBe(1);
		expect(store.isFavorite(1)).toBe(true);
	});

	it("toggleFavorite adds a new view", () => {
		const store = useFavoritesStore();
		store.toggleFavorite({ id: 7, label: "View 7" });
		expect(store.isFavorite(7)).toBe(true);
	});

	it("toggleFavorite removes an existing view by id", () => {
		const store = useFavoritesStore();
		const view = { id: 7, label: "View 7" };
		store.toggleFavorite(view);
		store.toggleFavorite({ id: 7 });
		expect(store.isFavorite(7)).toBe(false);
	});

	it("toggleFavorite persists to localStorage", () => {
		const store = useFavoritesStore();
		store.toggleFavorite({ id: 3, label: "Three" });
		const stored = JSON.parse(localStorage.getItem("favorites") || "[]");
		expect(stored).toEqual([{ id: 3, label: "Three" }]);
	});

	it("isFavorite returns false for unknown ids", () => {
		const store = useFavoritesStore();
		expect(store.isFavorite(999)).toBe(false);
	});

	it("updateFavLabel renames an existing favorite and persists it", () => {
		const store = useFavoritesStore();
		store.toggleFavorite({ id: 5, label: "Old" });
		store.updateFavLabel(5, "New");
		const stored = JSON.parse(localStorage.getItem("favorites") || "[]");
		expect(stored[0].label).toBe("New");
	});

	it("updateFavLabel does nothing for an unknown id", () => {
		const store = useFavoritesStore();
		store.toggleFavorite({ id: 5, label: "Old" });
		store.updateFavLabel(404, "New");
		const stored = JSON.parse(localStorage.getItem("favorites") || "[]");
		expect(stored[0].label).toBe("Old");
	});
});
