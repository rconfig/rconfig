import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";

// @ts-ignore — JS module, no type declarations
import { setupGlobalProperties } from "../globalProperties";

function makeApp() {
	return { config: { globalProperties: {} as Record<string, unknown> } };
}

function addMeta(name: string, content: string) {
	const meta = document.createElement("meta");
	meta.setAttribute("name", name);
	meta.setAttribute("content", content);
	document.head.appendChild(meta);
}

describe("setupGlobalProperties", () => {
	afterEach(() => {
		document.head.querySelectorAll("meta").forEach((m) => m.remove());
		const cfg = document.getElementById("app-config");
		if (cfg) {
			cfg.remove();
		}
		vi.restoreAllMocks();
	});

	it("reads user meta tags into the matching global properties", () => {
		addMeta("user-id", "42");
		addMeta("user-name", "Alice");
		addMeta("user-email", "alice@example.com");
		addMeta("user-role", "admin");
		addMeta("user-locale", "en");

		const app = makeApp();
		setupGlobalProperties(app);

		const gp = app.config.globalProperties;
		expect(gp.$userId).toBe("42");
		expect(gp.$userName).toBe("Alice");
		expect(gp.$userEmail).toBe("alice@example.com");
		expect(gp.$userRole).toBe("admin");
		expect(gp.$userLocale).toBe("en");
	});

	it("leaves a property unset when its meta tag is absent", () => {
		const app = makeApp();
		setupGlobalProperties(app);

		expect(app.config.globalProperties.$userId).toBeUndefined();
		expect(app.config.globalProperties.$userName).toBeUndefined();
	});

	it("maps server display meta tags", () => {
		addMeta("server-display-name", "Edge");
		addMeta("server-display-color", "blue");
		addMeta("server-display-size", "lg");

		const app = makeApp();
		setupGlobalProperties(app);

		expect(app.config.globalProperties.$serverDisplayName).toBe("Edge");
		expect(app.config.globalProperties.$serverDisplayColor).toBe("blue");
		expect(app.config.globalProperties.$serverDisplaySize).toBe("lg");
	});

	it("coerces prism-server-enabled of 1 to boolean true", () => {
		addMeta("prism-server-enabled", "1");

		const app = makeApp();
		setupGlobalProperties(app);

		expect(app.config.globalProperties.$prismServerEnabled).toBe(true);
	});

	it("coerces any non-1 prism-server-enabled value to boolean false", () => {
		addMeta("prism-server-enabled", "0");

		const app = makeApp();
		setupGlobalProperties(app);

		expect(app.config.globalProperties.$prismServerEnabled).toBe(false);
	});

	it("parses the app-config JSON script into $config", () => {
		// Use a non-executable type so jsdom does not try to run the content as JS.
		const el = document.createElement("script");
		el.id = "app-config";
		el.setAttribute("type", "application/json");
		el.textContent = JSON.stringify({ feature: true, name: "rconfig" });
		document.body.appendChild(el);

		const app = makeApp();
		setupGlobalProperties(app);

		expect(app.config.globalProperties.$config).toEqual({ feature: true, name: "rconfig" });
	});

	it("falls back to an empty object when the app-config JSON is invalid", () => {
		vi.spyOn(console, "error").mockImplementation(() => {});
		const el = document.createElement("script");
		el.id = "app-config";
		el.setAttribute("type", "application/json");
		el.textContent = "{not valid json";
		document.body.appendChild(el);

		const app = makeApp();
		setupGlobalProperties(app);

		expect(app.config.globalProperties.$config).toEqual({});
		expect(console.error).toHaveBeenCalled();
	});
});
