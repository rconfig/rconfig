import { describe, expect, it } from "vitest";

// @ts-ignore — JS module, no type declarations
import dashboardRoutes from "../dashboard";

describe("dashboard routes", () => {
	it("exports two route definitions", () => {
		expect(Array.isArray(dashboardRoutes)).toBe(true);
		expect(dashboardRoutes).toHaveLength(2);
	});

	it("defines the dashboard route with a lazy component", () => {
		const dashboard = dashboardRoutes[0];

		expect(dashboard.path).toBe("/dashboard");
		expect(dashboard.name).toBe("Dashboard");
		expect(typeof dashboard.component).toBe("function");
		expect(dashboard.meta).toEqual({
			rbacViewName: "Setting",
			pageTitleKey: "Dashboard",
		});
	});

	it("redirects the root path to the dashboard", () => {
		const root = dashboardRoutes[1];

		expect(root.path).toBe("/");
		expect(root.redirect).toBe("/dashboard");
		expect(root.component).toBeUndefined();
		expect(root.meta.rbacViewName).toBe("Setting");
	});
});
