import { describe, expect, it, vi } from "vitest";
import { mount } from "@vue/test-utils";

/**
 * REST API token management is Admin only, because every token has full Admin rights.
 * The server enforces it. The panel must not mount the token list for any other
 * account, so no request is made that would come back as 403.
 */

vi.mock("vue-router", () => ({
	useRouter: () => ({ push: vi.fn() }),
}));

// Stand in for the shadcn/reka-ui primitives, which add nothing to what is under test.
// The factories are hoisted above the imports, so the helper is defined inside each.
vi.mock("@/components/ui/card", async () => {
	const { defineComponent, h } = await import("vue");
	const passThrough = (name: string) =>
		defineComponent({ name, setup: (_props, { slots }) => () => h("div", slots.default?.()) });

	return {
		Card: passThrough("Card"),
		CardContent: passThrough("CardContent"),
		CardDescription: passThrough("CardDescription"),
		CardHeader: passThrough("CardHeader"),
		CardTitle: passThrough("CardTitle"),
	};
});

vi.mock("@/components/ui/tabs", async () => {
	const { defineComponent, h } = await import("vue");
	const passThrough = (name: string) =>
		defineComponent({ name, setup: (_props, { slots }) => () => h("div", slots.default?.()) });

	return {
		Tabs: passThrough("Tabs"),
		TabsContent: passThrough("TabsContent"),
		TabsList: passThrough("TabsList"),
		TabsTrigger: passThrough("TabsTrigger"),
	};
});

vi.mock("@/pages/Settings/Panels/Components/RestApiToken.vue", async () => {
	const { defineComponent, h } = await import("vue");

	return {
		default: defineComponent({
			name: "RestApiToken",
			setup: () => () => h("div", { "data-testid": "rest-api-token-list" }),
		}),
	};
});

// @ts-ignore — SFC, no type declarations
import RestApiPanel from "../RestApiPanel.vue";

function mountPanel(userRole?: string) {
	return mount(RestApiPanel, {
		global: {
			config: {
				globalProperties: userRole === undefined ? {} : { $userRole: userRole },
			},
		},
	});
}

describe("RestApiPanel", () => {
	it("shows token management to an Admin account", () => {
		const wrapper = mountPanel("Admin");

		expect(wrapper.find('[data-testid="rest-api-token-list"]').exists()).toBe(true);
		expect(wrapper.find('[data-testid="rest-api-admin-only"]').exists()).toBe(false);
	});

	it("matches the Admin role case insensitively", () => {
		const wrapper = mountPanel("admin");

		expect(wrapper.find('[data-testid="rest-api-token-list"]').exists()).toBe(true);
	});

	it("shows the Admin only notice to a User account and does not mount the token list", () => {
		const wrapper = mountPanel("User");

		expect(wrapper.find('[data-testid="rest-api-token-list"]').exists()).toBe(false);
		expect(wrapper.find('[data-testid="rest-api-admin-only"]').text()).toContain(
			"Only Admin accounts can manage API tokens",
		);
	});

	it("treats a missing role as not Admin", () => {
		const wrapper = mountPanel();

		expect(wrapper.find('[data-testid="rest-api-token-list"]').exists()).toBe(false);
		expect(wrapper.find('[data-testid="rest-api-admin-only"]').exists()).toBe(true);
	});
});
