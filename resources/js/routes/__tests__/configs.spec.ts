import { describe, expect, it } from "vitest";

// @ts-ignore — JS module, no type declarations
import configsRoutes from "../configs";

describe("configs routes", () => {
	it("exports an array of route definitions", () => {
		expect(Array.isArray(configsRoutes)).toBe(true);
		expect(configsRoutes).toHaveLength(6);
	});

	it("exposes the expected paths and names", () => {
		const byName = Object.fromEntries(
			configsRoutes.map((r: any) => [r.name, r]),
		);

		expect(byName.configs.path).toBe("/configs");
		expect(byName["config-view"].path).toBe("/configs/view/:id");
		expect(byName.configsearch.path).toBe("/config-search");
		expect(byName.configreport.path).toBe("/config-report");
		expect(byName.configcompare.path).toBe("/config-compare");
		expect(byName.configcompareoptions.path).toBe("/config-compare-options");
	});

	it("lazy loads every component", () => {
		for (const route of configsRoutes) {
			expect(typeof route.component).toBe("function");
		}
	});

	it("passes props on every route", () => {
		for (const route of configsRoutes) {
			expect(route.props).toBe(true);
		}
	});

	it("tags every route with the Config rbac view and a page title key", () => {
		for (const route of configsRoutes) {
			expect(route.meta.rbacViewName).toBe("Config");
			expect(typeof route.meta.pageTitleKey).toBe("string");
		}
	});

	it("starts each breadcrumb trail at Home then Configs", () => {
		const withBreadcrumb = configsRoutes.filter((r: any) => r.meta.breadcrumb);

		// config-view is the only route without a breadcrumb trail.
		expect(withBreadcrumb).toHaveLength(5);

		for (const route of withBreadcrumb) {
			expect(route.meta.breadcrumb[0]).toEqual({ label: "Home", link: "/" });
			expect(route.meta.breadcrumb[1]).toEqual({
				label: "Configs",
				link: "/configs",
			});
		}
	});
});
