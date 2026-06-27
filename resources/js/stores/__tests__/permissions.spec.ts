import { beforeEach, describe, expect, it, vi } from "vitest";
import { setActivePinia, createPinia } from "pinia";

const { get } = vi.hoisted(() => ({ get: vi.fn() }));

vi.mock("axios", () => ({
	default: { get },
}));

// @ts-ignore — store is JS, no type declarations
import { usePermissionsStore } from "../permissions";

describe("usePermissionsStore", () => {
	beforeEach(() => {
		get.mockReset();
		sessionStorage.clear();
		setActivePinia(createPinia());
	});

	it("starts with empty state defaults", () => {
		const store = usePermissionsStore();
		expect(store.permissions).toEqual([]);
		expect(store.lastLoaded).toBeNull();
		expect(store.isLoading).toBe(false);
	});

	it("permissionsCount reflects the number of permissions", () => {
		const store = usePermissionsStore();
		store.permissions = ["a", "b", "c"];
		expect(store.permissionsCount).toBe(3);
	});

	it("loadPermissions fetches and stores permissions keyed to the session id", async () => {
		sessionStorage.setItem("session-id", "sess-1");
		get.mockResolvedValue({ data: { permissions: ["view", "edit"] } });

		const store = usePermissionsStore();
		await store.loadPermissions(42);

		expect(get).toHaveBeenCalledWith("/api/users/permissions/42");
		expect(store.permissions).toEqual(["view", "edit"]);
		expect(store.lastLoaded).toBe("sess-1");
		expect(store.isLoading).toBe(false);
	});

	it("loadPermissions skips refetching for the same session", async () => {
		sessionStorage.setItem("session-id", "sess-1");
		get.mockResolvedValue({ data: { permissions: ["view"] } });

		const store = usePermissionsStore();
		await store.loadPermissions(42);
		await store.loadPermissions(42);

		expect(get).toHaveBeenCalledTimes(1);
	});

	it("loadPermissions swallows errors and resets loading", async () => {
		get.mockRejectedValue(new Error("boom"));
		const store = usePermissionsStore();

		await store.loadPermissions(1);

		expect(store.permissions).toEqual([]);
		expect(store.isLoading).toBe(false);
	});

	it("forceReloadPermissions always fetches and updates state", async () => {
		sessionStorage.setItem("session-id", "sess-9");
		get.mockResolvedValue({ data: { permissions: ["admin"] } });

		const store = usePermissionsStore();
		store.permissions = ["stale"];
		store.lastLoaded = "sess-9";

		await store.forceReloadPermissions(7);

		expect(get).toHaveBeenCalledWith("/api/users/permissions/7");
		expect(store.permissions).toEqual(["admin"]);
		expect(store.lastLoaded).toBe("sess-9");
	});

	it("clearPermissions resets permissions and lastLoaded", () => {
		const store = usePermissionsStore();
		store.permissions = ["x"];
		store.lastLoaded = "sess";
		store.clearPermissions();
		expect(store.permissions).toEqual([]);
		expect(store.lastLoaded).toBeNull();
	});

	it("resetOnLogout resets state and clears session storage", () => {
		sessionStorage.setItem("session-id", "sess");
		sessionStorage.setItem("pinia-permissions", "blob");
		const store = usePermissionsStore();
		store.permissions = ["x"];

		store.resetOnLogout();

		expect(store.permissions).toEqual([]);
		expect(sessionStorage.getItem("session-id")).toBeNull();
		expect(sessionStorage.getItem("pinia-permissions")).toBeNull();
	});
});
