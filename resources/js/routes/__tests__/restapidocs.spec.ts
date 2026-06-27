import { describe, expect, it } from "vitest";

// @ts-ignore — JS module, no type declarations
import restapiDocsRoutes from "../restapidocs";

describe("rest api docs routes", () => {
	it("exports the full set of doc routes", () => {
		expect(Array.isArray(restapiDocsRoutes)).toBe(true);
		expect(restapiDocsRoutes).toHaveLength(19);
	});

	it("redirects the index child to the get-started page", () => {
		const index = restapiDocsRoutes[0];

		expect(index.path).toBe("");
		expect(index.redirect).toBe("/settings/restapi-docs/get-started");
		expect(index.component).toBeUndefined();
	});

	it("uses relative child paths (no leading slash) on the named routes", () => {
		const named = restapiDocsRoutes.filter((r: any) => r.name);

		expect(named).toHaveLength(18);
		for (const route of named) {
			expect(route.path.startsWith("/")).toBe(false);
		}
	});

	it("prefixes every route name with rest-api-docs-", () => {
		const named = restapiDocsRoutes.filter((r: any) => r.name);

		for (const route of named) {
			expect(route.name.startsWith("rest-api-docs-")).toBe(true);
		}
	});

	it("lazy loads every named component under the Setting rbac view", () => {
		const named = restapiDocsRoutes.filter((r: any) => r.name);

		for (const route of named) {
			expect(typeof route.component).toBe("function");
			expect(route.meta.rbacViewName).toBe("Setting");
			expect(typeof route.meta.pageTitleKey).toBe("string");
		}
	});

	it("keeps route names unique", () => {
		const names = restapiDocsRoutes
			.filter((r: any) => r.name)
			.map((r: any) => r.name);

		expect(new Set(names).size).toBe(names.length);
	});
});
