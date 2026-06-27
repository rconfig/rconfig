import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { ref } from "vue";

// app.js is the application bootstrap. It has heavy side effects (component
// registration, plugin setup, mounting). We stub the wiring modules so the test
// exercises the orchestration in app.js itself: that it mounts, exposes the
// debug helpers, and signals readiness without throwing.
const registerComponents = vi.fn();
const setupPlugins = vi.fn();
const applyStoredTheme = vi.fn();
const setupGlobalProperties = vi.fn();
const initializeLoadingHandlers = vi.fn();

vi.mock("../appimports/bootstrap", () => ({}));
vi.mock("../appimports/authComponentImports", () => ({}));
vi.mock("../appimports/uiComponentImports", () => ({}));
vi.mock("../appimports/navComponentImports", () => ({ NavigationSide: {}, NavigationTop: {} }));
vi.mock("../appimports/themeHandler", () => ({ applyStoredTheme }));
vi.mock("../appimports/loadingHandlers", () => ({ initializeLoadingHandlers }));
vi.mock("../appimports/registerComponents", () => ({ registerComponents }));
vi.mock("../appimports/globalProperties", () => ({ setupGlobalProperties }));
vi.mock("../appimports/setupPlugins", () => ({ setupPlugins }));
vi.mock("@vueuse/core", () => ({ useColorMode: () => ref("light") }));

describe("app bootstrap", () => {
	beforeEach(() => {
		document.body.innerHTML = '<div id="app"></div>';
		vi.clearAllMocks();
	});

	afterEach(() => {
		vi.resetModules();
	});

	it("wires up the app, mounts it, and dispatches app-ready", async () => {
		const ready = vi.fn();
		window.addEventListener("app-ready", ready);

		await import("../app.js");

		// Wiring modules were invoked during bootstrap.
		expect(registerComponents).toHaveBeenCalled();
		expect(setupPlugins).toHaveBeenCalled();
		expect(applyStoredTheme).toHaveBeenCalled();
		expect(setupGlobalProperties).toHaveBeenCalled();

		// Public globals are exposed and the ready event fired.
		expect((window as any).vueApp).toBeTruthy();
		expect(typeof (window as any).dd).toBe("function");
		expect(ready).toHaveBeenCalledTimes(1);

		window.removeEventListener("app-ready", ready);
	});
});
