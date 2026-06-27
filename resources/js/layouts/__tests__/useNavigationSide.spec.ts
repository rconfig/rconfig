import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { defineComponent, h, nextTick } from "vue";

// Axios is called as a global inside the composable.
const { get, post } = vi.hoisted(() => ({ get: vi.fn(), post: vi.fn() }));

// Router push spy.
const push = vi.fn();
vi.mock("vue-router", () => ({
	useRouter: () => ({ push }),
}));

// Toaster spies.
const toastSuccess = vi.fn();
const toastError = vi.fn();
vi.mock("@/composables/useToaster", () => ({
	useToaster: () => ({ toastSuccess, toastError }),
}));

// Stores. externalLinksStore holds links + a setter; panelStore exposes panelRef.
const externalLinksStoreState = vi.hoisted(() => ({ links: [] as unknown[], setLinks: vi.fn() }));
vi.mock("@/stores/externalLinksStore", () => ({
	useExternalLinksStore: () => externalLinksStoreState,
}));

const favoritesStoreState = vi.hoisted(() => ({ favorites: [] }));
vi.mock("@/stores/favorites", () => ({
	useFavoritesStore: () => favoritesStoreState,
}));

// The composable assigns its internal ref to panelStore.panelRef in onMounted,
// then immediately calls panelRef.expand()/collapse() on it. We expose a panelRef
// whose setter is ignored so it always resolves to a stub with those methods,
// keeping the breakpoint handler from throwing.
const panelExpand = vi.fn();
const panelCollapse = vi.fn();
const panelStoreState = vi.hoisted(() => {
	const stub = { expand: () => {}, collapse: () => {} };
	return {
		get panelRef() {
			return stub;
		},
		set panelRef(_v: unknown) {
			// ignore the composable overwriting it
		},
	};
});
panelStoreState.panelRef.expand = panelExpand;
panelStoreState.panelRef.collapse = panelCollapse;
vi.mock("@/stores/panelStore", () => ({
	usePanelStore: () => panelStoreState,
}));

const openSheet = vi.fn();
vi.mock("@/stores/sheetActions", () => ({
	useSheetStore: () => ({ openSheet }),
}));

// @ts-ignore — JS module, no type declarations
import { useNavigationSide } from "../useNavigationSide";

// Mount a throwaway component so onMounted/onUnmounted hooks run, capturing the
// composable's return value.
function withSetup(composable: () => unknown) {
	let result: any;
	const Comp = defineComponent({
		setup() {
			result = composable();
			return () => h("div");
		},
	});
	// vue-test-utils is not assumed present; mount manually via createApp.
	const { createApp } = require("vue");
	const el = document.createElement("div");
	const app = createApp(Comp);
	app.mount(el);
	return { result, unmount: () => app.unmount() };
}

beforeEach(() => {
	localStorage.clear();
	get.mockReset();
	post.mockReset();
	push.mockClear();
	toastSuccess.mockClear();
	toastError.mockClear();
	externalLinksStoreState.links = [];
	externalLinksStoreState.setLinks.mockClear();
	panelExpand.mockClear();
	panelCollapse.mockClear();
	openSheet.mockClear();

	// Provide a global axios as the composable expects.
	(globalThis as any).axios = { get, post };

	// jsdom has no matchMedia by default.
	window.matchMedia = vi.fn().mockReturnValue({
		matches: false,
		addEventListener: vi.fn(),
		removeEventListener: vi.fn(),
	}) as any;
});

afterEach(() => {
	vi.restoreAllMocks();
	delete (globalThis as any).axios;
});

describe("useNavigationSide", () => {
	it("defaults the collapsible open states to true when localStorage is empty", () => {
		get.mockResolvedValue({ data: [] });
		const { result, unmount } = withSetup(() => useNavigationSide(1));

		expect(result.sideNavExtLinksIsOpen.value).toBe(true);
		expect(result.sideNavFavLinksIsOpen.value).toBe(true);
		expect(result.sideNavSettingsIsOpen.value).toBe(true);
		unmount();
	});

	it("reads persisted collapsible states from localStorage", () => {
		localStorage.setItem("sideNavExtLinksIsOpen", "false");
		localStorage.setItem("sideNavFavLinksIsOpen", "false");
		get.mockResolvedValue({ data: [] });

		const { result, unmount } = withSetup(() => useNavigationSide(1));

		expect(result.sideNavExtLinksIsOpen.value).toBe(false);
		expect(result.sideNavFavLinksIsOpen.value).toBe(false);
		expect(result.sideNavSettingsIsOpen.value).toBe(true);
		unmount();
	});

	it("persists collapsible state changes back to localStorage", async () => {
		get.mockResolvedValue({ data: [] });
		const { result, unmount } = withSetup(() => useNavigationSide(1));

		result.sideNavSettingsIsOpen.value = false;
		await nextTick();

		expect(localStorage.getItem("sideNavSettingsIsOpen")).toBe("false");
		unmount();
	});

	it("uses links already in the store without calling the API", () => {
		externalLinksStoreState.links = [{ name: "Google" }];
		const { result, unmount } = withSetup(() => useNavigationSide(7));

		expect(result.externalLinks.value).toEqual([{ name: "Google" }]);
		expect(get).not.toHaveBeenCalled();
		unmount();
	});

	it("fetches external links from the API when the store is empty", async () => {
		const apiLinks = [{ name: "Docs", url: "https://docs" }];
		get.mockResolvedValue({ data: apiLinks });

		const { result, unmount } = withSetup(() => useNavigationSide(99));

		// loadLinksFromStoreOrDb runs in onMounted.
		await nextTick();
		await Promise.resolve();

		expect(get).toHaveBeenCalledWith("/api/user/get-external-links/99");
		expect(externalLinksStoreState.setLinks).toHaveBeenCalledWith(apiLinks);
		expect(result.externalLinks.value).toEqual(apiLinks);
		unmount();
	});

	it("navToSettingsUpgrade routes to the settings-upgrade page", () => {
		get.mockResolvedValue({ data: [] });
		const { result, unmount } = withSetup(() => useNavigationSide(1));

		result.navToSettingsUpgrade();

		expect(push).toHaveBeenCalledWith({ name: "settings-upgrade" });
		unmount();
	});

	it("notificationsCount updates the notifications length ref", () => {
		get.mockResolvedValue({ data: [] });
		const { result, unmount } = withSetup(() => useNavigationSide(1));

		result.notificationsCount(5);

		expect(result.notificationsLength.value).toBe(5);
		unmount();
	});

	it("closeExtDialog bumps the dialog key and reloads links", () => {
		externalLinksStoreState.links = [{ name: "Cached" }];
		const { result, unmount } = withSetup(() => useNavigationSide(1));

		const before = result.externalLinksDialogKey.value;
		result.closeExtDialog();

		expect(result.externalLinksDialogKey.value).toBe(before + 1);
		unmount();
	});

	it("removeExternalLink posts the encoded name and toasts success", async () => {
		externalLinksStoreState.links = [{ name: "Keep" }];
		post.mockResolvedValue({ data: [{ name: "Keep" }] });
		const { result, unmount } = withSetup(() => useNavigationSide(1));

		await result.removeExternalLink("My Link");

		expect(post).toHaveBeenCalledWith("/api/user/remove-external-link", { name: "My%20Link" });
		expect(toastSuccess).toHaveBeenCalledWith("External Link", "Link removed successfully");
		unmount();
	});

	it("removeExternalLink toasts an error when the request rejects", async () => {
		vi.spyOn(console, "error").mockImplementation(() => {});
		get.mockResolvedValue({ data: [] });
		post.mockRejectedValue(new Error("boom"));
		const { result, unmount } = withSetup(() => useNavigationSide(1));

		await result.removeExternalLink("Bad");

		expect(toastError).toHaveBeenCalledWith("External Link", "Error removing link");
		unmount();
	});

	it("exposes openSheet from the sheet store", () => {
		get.mockResolvedValue({ data: [] });
		const { result, unmount } = withSetup(() => useNavigationSide(1));

		expect(result.openSheet).toBe(openSheet);
		unmount();
	});
});
