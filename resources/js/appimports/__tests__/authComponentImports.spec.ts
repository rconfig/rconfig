import { describe, expect, it, vi } from "vitest";

// Stub each auth/loader component to keep the registry lightweight.
vi.mock("@/pages/Auth/LoginComponent.vue", () => ({ default: { name: "LoginComponent" } }));
vi.mock("@/pages/Auth/LoggedOutComponent.vue", () => ({ default: { name: "LoggedOutComponent" } }));
vi.mock("@/pages/Auth/EmailPasswordComponent.vue", () => ({ default: { name: "EmailPasswordComponent" } }));
vi.mock("@/pages/Auth/ResetPasswordComponent.vue", () => ({ default: { name: "ResetPasswordComponent" } }));
vi.mock("@/pages/Shared/Loaders/AuthLoading.vue", () => ({ default: { name: "AuthLoading" } }));
vi.mock("@/pages/Shared/Loaders/Loading.vue", () => ({ default: { name: "MainLoading" } }));
vi.mock("@/pages/Shared/Loaders/LoggoutLoading.vue", () => ({ default: { name: "LoggoutLoading" } }));

import * as authImports from "../authComponentImports";

describe("authComponentImports", () => {
	it("re-exports the expected auth and loader components", () => {
		expect(Object.keys(authImports).sort()).toEqual(
			[
				"AuthLoading",
				"EmailPasswordComponent",
				"LoggedOutComponent",
				"LoggoutLoading",
				"LoginComponent",
				"MainLoading",
				"ResetPasswordComponent",
			].sort(),
		);
	});

	it("exports a truthy value for every key", () => {
		for (const value of Object.values(authImports)) {
			expect(value).toBeTruthy();
		}
	});
});
