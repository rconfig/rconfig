import { describe, expect, it } from "vitest";

// @ts-ignore — JS module, no type declarations
import usersRoutes from "../users";

describe("users routes", () => {
	it("exports two route definitions", () => {
		expect(Array.isArray(usersRoutes)).toBe(true);
		expect(usersRoutes).toHaveLength(2);
	});

	it("defines the users list route with an optional user id", () => {
		const users = usersRoutes[0];

		expect(users.path).toBe("/settings/users/:userId?");
		expect(users.name).toBe("users");
		expect(typeof users.component).toBe("function");
		expect(users.meta.rbacViewName).toBe("User");
		expect(users.meta.pageTitleKey).toBe("Users");
	});

	it("defines the user profile route with an empty beforeEnter guard list", () => {
		const profile = usersRoutes[1];

		expect(profile.path).toBe("/settings/my-profile/:userId?");
		expect(profile.name).toBe("user-profile");
		expect(typeof profile.component).toBe("function");
		expect(Array.isArray(profile.beforeEnter)).toBe(true);
		expect(profile.beforeEnter).toHaveLength(0);
	});

	it("omits an rbac view name on the user profile route", () => {
		const profile = usersRoutes[1];

		expect(profile.meta.rbacViewName).toBeUndefined();
		expect(profile.meta.pageTitleKey).toBe("UserProfile");
	});

	it("starts both breadcrumb trails at Home", () => {
		for (const route of usersRoutes) {
			expect(route.meta.breadcrumb[0]).toEqual({ label: "Home", link: "/" });
		}
	});
});
