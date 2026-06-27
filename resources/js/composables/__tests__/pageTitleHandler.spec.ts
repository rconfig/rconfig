import { describe, expect, it } from "vitest";

// @ts-ignore — composable is JS, no type declarations
import { generatePageTitle } from "../pageTitleHandler";

// VITE_APP_NAME is not defined under vitest, so the default title is used.
const DEFAULT_TITLE = "rConfigV8 Core - NCM";

describe("generatePageTitle", () => {
	it("maps a known pageTitleKey to its friendly title", () => {
		const title = generatePageTitle({ meta: { pageTitleKey: "configSearch" } });
		expect(title).toBe(`Config Search | ${DEFAULT_TITLE}`);
	});

	it("falls back to the route name, spacing out camelCase", () => {
		const title = generatePageTitle({ name: "deviceView" });
		expect(title).toBe(`Device View | ${DEFAULT_TITLE}`);
	});

	it("prefers a known pageTitleKey over the route name", () => {
		const title = generatePageTitle({ name: "somethingElse", meta: { pageTitleKey: "dashboard" } });
		expect(title).toBe(`Dashboard | ${DEFAULT_TITLE}`);
	});

	it("uses the route name when the pageTitleKey is unknown", () => {
		const title = generatePageTitle({ name: "customPage", meta: { pageTitleKey: "doesNotExist" } });
		expect(title).toBe(`Custom Page | ${DEFAULT_TITLE}`);
	});

	it("returns just the default title when there is no key or name", () => {
		expect(generatePageTitle({})).toBe(DEFAULT_TITLE);
	});
});
