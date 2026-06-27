import { describe, expect, it } from "vitest";

// @ts-ignore — JS module, no type declarations
import settingsRoutes from "../settings";
// @ts-ignore — JS module, no type declarations
import restapiDocsRoutes from "../restapidocs";

describe("settings routes", () => {
	it("exports the full set of settings routes", () => {
		expect(Array.isArray(settingsRoutes)).toBe(true);
		expect(settingsRoutes).toHaveLength(15);
	});

	it("nests the imported rest api doc routes under the docs parent", () => {
		const docsParent = settingsRoutes.find(
			(r: any) => r.path === "/settings/restapi-docs",
		);

		expect(docsParent).toBeDefined();
		expect(typeof docsParent.component).toBe("function");
		expect(docsParent.meta.pageTitleKey).toBe("SettingsRestAPIDocs");
		// children should be the very array exported by restapidocs.
		expect(docsParent.children).toBe(restapiDocsRoutes);
	});

	it("defines the base settings route with a breadcrumb", () => {
		const settings = settingsRoutes.find((r: any) => r.name === "settings");

		expect(settings.path).toBe("/settings");
		expect(typeof settings.component).toBe("function");
		expect(settings.meta.breadcrumb).toEqual([
			{ label: "Home", link: "/" },
			{ label: "System Settings", link: "/settings" },
		]);
	});

	it("passes a boolean view flag prop on the panel routes", () => {
		const start = settingsRoutes.find((r: any) => r.name === "settings-start");
		const system = settingsRoutes.find((r: any) => r.name === "settings-system");
		const about = settingsRoutes.find((r: any) => r.name === "settings-about");

		expect(start.props).toEqual({ start: true });
		expect(system.props).toEqual({ system: true });
		expect(about.props).toEqual({ about: true });
	});

	it("maps both data-migration and import-export to the importExport prop", () => {
		const dataMigration = settingsRoutes.find(
			(r: any) => r.name === "settings-data-migration",
		);
		const importExport = settingsRoutes.find(
			(r: any) => r.name === "settings-import-export",
		);

		expect(dataMigration.props).toEqual({ importExport: true });
		expect(importExport.props).toEqual({ importExport: true });
	});

	it("makes the credentials route id parameter optional", () => {
		const credentials = settingsRoutes.find(
			(r: any) => r.name === "settings-credentials",
		);

		expect(credentials.path).toBe("/settings/credentials/:id?");
	});

	it("tags every settings route with the Setting rbac view", () => {
		for (const route of settingsRoutes) {
			expect(route.meta.rbacViewName).toBe("Setting");
		}
	});

	it("lazy loads every named settings component", () => {
		const named = settingsRoutes.filter((r: any) => r.name);

		for (const route of named) {
			expect(typeof route.component).toBe("function");
			expect(typeof route.meta.pageTitleKey).toBe("string");
		}
	});
});
