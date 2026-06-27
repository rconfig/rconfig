import { describe, expect, it } from "vitest";

// @ts-ignore — JS module, no type declarations
import inventoryRoutes from "../inventory";

describe("inventory routes", () => {
	it("exports the full set of inventory routes", () => {
		expect(Array.isArray(inventoryRoutes)).toBe(true);
		expect(inventoryRoutes).toHaveLength(11);
	});

	it("exposes the expected path for each named route", () => {
		const byName = Object.fromEntries(
			inventoryRoutes.map((r: any) => [r.name, r.path]),
		);

		expect(byName.inventory).toBe("/inventory/:view?");
		expect(byName.commandgroups).toBe("/commandgroups/:id?");
		expect(byName.devices).toBe("/devices");
		expect(byName["device-view"]).toBe("/device/view/:id");
		expect(byName.tags).toBe("/tags/:id?");
		expect(byName.models).toBe("/models/:id?");
		expect(byName.vendors).toBe("/vendors/:id?");
		expect(byName.commands).toBe("/commands");
		expect(byName["command-compare-options"]).toBe("/command-compare-options");
		expect(byName.templates).toBe("/templates/:id?");
		expect(byName["template-view"]).toBe("/templates/view/:id");
	});

	it("lazy loads every component and sets a page title key", () => {
		for (const route of inventoryRoutes) {
			expect(typeof route.component).toBe("function");
			expect(typeof route.meta.pageTitleKey).toBe("string");
		}
	});

	it("passes props on the detail (view) routes that take an id", () => {
		const deviceView = inventoryRoutes.find(
			(r: any) => r.name === "device-view",
		);
		const templateView = inventoryRoutes.find(
			(r: any) => r.name === "template-view",
		);

		expect(deviceView.props).toBe(true);
		expect(templateView.props).toBe(true);
	});

	it("omits an rbac view name on the bare inventory route", () => {
		const inventory = inventoryRoutes.find((r: any) => r.name === "inventory");

		expect(inventory.meta.rbacViewName).toBeUndefined();
	});

	it("assigns sensible rbac view names to the typed routes", () => {
		const byName = Object.fromEntries(
			inventoryRoutes.map((r: any) => [r.name, r.meta.rbacViewName]),
		);

		expect(byName.devices).toBe("Device");
		expect(byName["device-view"]).toBe("Device");
		expect(byName.vendors).toBe("Vendor");
		expect(byName.commands).toBe("Command");
		expect(byName.templates).toBe("Template");
	});
});
