import { describe, expect, it } from "vitest";

// @ts-ignore — JS module, no type declarations
import integrationsRoutes from "../integrations";

describe("integrations routes", () => {
	it("exports a single route definition", () => {
		expect(Array.isArray(integrationsRoutes)).toBe(true);
		expect(integrationsRoutes).toHaveLength(1);
	});

	it("defines the integrations route", () => {
		const route = integrationsRoutes[0];

		expect(route.path).toBe("/settings/integrations");
		expect(route.name).toBe("integrations");
		expect(route.props).toBe(true);
		expect(typeof route.component).toBe("function");
	});

	it("carries the IntegrationOption rbac view and breadcrumb", () => {
		const { meta } = integrationsRoutes[0];

		expect(meta.rbacViewName).toBe("IntegrationOption");
		expect(meta.pageTitleKey).toBe("Integrations");
		expect(meta.breadcrumb).toEqual([
			{ label: "Home", link: "/" },
			{ label: "Integrations", link: "/settings/integrations" },
		]);
	});
});
