import { afterEach, describe, expect, it } from "vitest";

// @ts-ignore — router is JS, no type declarations
import router from "../router";

describe("router", () => {
	afterEach(() => {
		document.title = "";
	});

	it("exposes a vue-router instance", () => {
		expect(typeof router.getRoutes).toBe("function");
		expect(typeof router.beforeEach).toBe("function");
		expect(typeof router.push).toBe("function");
	});

	it("registers the standalone auth routes", () => {
		const names = router.getRoutes().map((r) => r.name);

		expect(names).toContain("login");
		expect(names).toContain("logged-out");
		expect(names).toContain("password-reset");
	});

	it("aggregates the per-area route modules (e.g. configs, dashboard)", () => {
		// The router spreads in configs/dashboard/inventory/settings/tasks/users.
		// A non-trivial route count confirms those modules were merged in.
		expect(router.getRoutes().length).toBeGreaterThan(8);
	});

	it("keeps a catch-all PageNotFound route with a lazy component", () => {
		const notFound = router.getRoutes().find((r) => r.name === "PageNotFound");

		expect(notFound).toBeTruthy();
		expect(notFound?.path).toBe("/:catchAll(.*)");
		expect(typeof notFound?.components?.default).toBe("function");
	});

	it("sets the document title from the route on navigation", async () => {
		await router.push("/login");
		await router.isReady();

		// generatePageTitle maps the 'Login' pageTitleKey to a friendly title.
		expect(document.title).toContain("Login");
	});
});
