import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";

// @ts-ignore — JS module, no type declarations
import { initializeLoadingHandlers, isFirstVisit } from "../loadingHandlers";

const AUTH_LOADER_LS_KEY = "rconfig.authLoaderSeen.v1";

afterEach(() => {
	localStorage.clear();
	document.body.innerHTML = "";
	vi.useRealTimers();
	vi.restoreAllMocks();
});

beforeEach(() => {
	localStorage.clear();
	document.body.innerHTML = "";
});

describe("isFirstVisit", () => {
	it("is true when the auth loader has never been seen", () => {
		expect(isFirstVisit()).toBe(true);
	});

	it("is false once the seen flag is stored", () => {
		localStorage.setItem(AUTH_LOADER_LS_KEY, "1");
		expect(isFirstVisit()).toBe(false);
	});
});

describe("initializeLoadingHandlers - logout page", () => {
	function setupLogoutDom() {
		document.body.innerHTML = `
			<div id="logout-loading-container"></div>
			<div id="logout-main-content" class="hidden"></div>
		`;
	}

	it("reveals the logout content after 2 seconds and dispatches the complete event", () => {
		vi.useFakeTimers();
		setupLogoutDom();
		const onComplete = vi.fn();
		window.addEventListener("auth-loading-complete", onComplete);

		initializeLoadingHandlers();

		const container = document.getElementById("logout-loading-container")!;
		const main = document.getElementById("logout-main-content")!;

		expect(container.classList.contains("hidden")).toBe(false);

		vi.advanceTimersByTime(2000);

		expect(container.classList.contains("hidden")).toBe(true);
		expect(main.classList.contains("hidden")).toBe(false);
		expect(onComplete).toHaveBeenCalledTimes(1);

		window.removeEventListener("auth-loading-complete", onComplete);
	});
});

describe("initializeLoadingHandlers - login page", () => {
	function setupLoginDom() {
		document.body.innerHTML = `
			<div id="auth-loading-container"></div>
			<div id="auth-main-content" class="hidden"></div>
		`;
	}

	it("shows login immediately on a subsequent visit", () => {
		localStorage.setItem(AUTH_LOADER_LS_KEY, "1");
		setupLoginDom();

		initializeLoadingHandlers();

		expect(document.getElementById("auth-loading-container")!.classList.contains("hidden")).toBe(true);
		expect(document.getElementById("auth-main-content")!.classList.contains("hidden")).toBe(false);
	});

	it("shows branded loading for 3 seconds on a first visit, then marks it seen", () => {
		vi.useFakeTimers();
		setupLoginDom();
		const onComplete = vi.fn();
		window.addEventListener("auth-loading-complete", onComplete);

		initializeLoadingHandlers();

		const container = document.getElementById("auth-loading-container")!;
		expect(container.classList.contains("hidden")).toBe(false);
		expect(localStorage.getItem(AUTH_LOADER_LS_KEY)).toBeNull();

		vi.advanceTimersByTime(3000);

		expect(container.classList.contains("hidden")).toBe(true);
		expect(document.getElementById("auth-main-content")!.classList.contains("hidden")).toBe(false);
		expect(localStorage.getItem(AUTH_LOADER_LS_KEY)).toBe("1");
		expect(onComplete).toHaveBeenCalledTimes(1);

		window.removeEventListener("auth-loading-complete", onComplete);
	});
});

describe("initializeLoadingHandlers - main app", () => {
	function setupMainDom() {
		document.body.innerHTML = `
			<div id="main-loading-container"></div>
			<div id="main-content" class="hidden"></div>
		`;
	}

	it("hides the main loader 500ms after the app-ready event", () => {
		vi.useFakeTimers();
		setupMainDom();

		initializeLoadingHandlers();

		const container = document.getElementById("main-loading-container")!;
		const content = document.getElementById("main-content")!;

		// Nothing happens until app-ready fires.
		expect(container.classList.contains("hidden")).toBe(false);

		window.dispatchEvent(new CustomEvent("app-ready"));
		vi.advanceTimersByTime(500);

		expect(container.classList.contains("hidden")).toBe(true);
		expect(content.classList.contains("hidden")).toBe(false);
	});
});
