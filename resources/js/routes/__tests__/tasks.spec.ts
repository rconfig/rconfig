import { describe, expect, it } from "vitest";

// @ts-ignore — JS module, no type declarations
import tasksRoutes from "../tasks";

describe("tasks routes", () => {
	it("exports a single route definition", () => {
		expect(Array.isArray(tasksRoutes)).toBe(true);
		expect(tasksRoutes).toHaveLength(1);
	});

	it("defines the tasks route with a lazy component", () => {
		const route = tasksRoutes[0];

		expect(route.path).toBe("/tasks");
		expect(route.name).toBe("tasks");
		expect(typeof route.component).toBe("function");
	});

	it("carries the Task rbac view and breadcrumb", () => {
		const { meta } = tasksRoutes[0];

		expect(meta.rbacViewName).toBe("Task");
		expect(meta.pageTitleKey).toBe("Tasks");
		expect(meta.breadcrumb).toEqual([
			{ label: "Home", link: "/" },
			{ label: "Tasks", link: "/tasks" },
		]);
	});
});
