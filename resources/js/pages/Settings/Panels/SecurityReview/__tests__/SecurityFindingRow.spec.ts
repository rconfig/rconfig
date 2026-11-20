import { mount } from "@vue/test-utils";
import { describe, expect, it } from "vitest";
import SecurityFindingRow from "../SecurityFindingRow.vue";

interface FindingFixture {
	id: string;
	group: string;
	title: string;
	severity: string;
	status: string;
	message: string;
	fix?: string | null;
	docs_url?: string;
	references?: string[];
	rationale?: string;
	evidence?: Record<string, unknown>;
}

function finding(overrides: Partial<FindingFixture> = {}): FindingFixture {
	return {
		id: "data.config_file_modes",
		group: "data",
		title: "Saved configs are private to rConfig",
		severity: "high",
		status: "fail",
		message: "1 of 2 sampled configs can be read by any local user.",
		fix: "Run: cd /var/www/html/rconfig && php artisan rconfig:set-config-permissions",
		docs_url: "https://v8coredocs.rconfig.com/security/checks/data/#data-config-file-modes",
		references: ["NIST AC-6"],
		rationale: "Device configs hold secrets.",
		evidence: { sampled: 2, readable_by_others: 1 },
		...overrides,
	};
}

describe("SecurityFindingRow", () => {
	it("shows the status, title and message collapsed", () => {
		const wrapper = mount(SecurityFindingRow, { props: { row: finding() } });

		expect(wrapper.get('[data-testid="finding-status"]').text()).toBe("Failed");
		expect(wrapper.text()).toContain("Saved configs are private to rConfig");
		expect(wrapper.text()).toContain("1 of 2 sampled configs");
		expect(wrapper.find('[data-testid="finding-details"]').exists()).toBe(false);
	});

	it("expands to why it matters, the fix, evidence and docs link", async () => {
		const wrapper = mount(SecurityFindingRow, { props: { row: finding() } });

		await wrapper.get("button").trigger("click");

		expect(wrapper.text()).toContain("Why it matters");
		expect(wrapper.get('[data-testid="finding-fix"]').text()).toContain("rconfig:set-config-permissions");
		expect(wrapper.get('[data-testid="finding-evidence"]').text()).toContain("readable by others");
		expect(wrapper.get('[data-testid="finding-docs"]').attributes("href")).toBe("https://v8coredocs.rconfig.com/security/checks/data/#data-config-file-modes");
	});

	it("does not offer a fix for a passing check", async () => {
		const wrapper = mount(SecurityFindingRow, { props: { row: finding({ status: "pass", fix: "something" }) } });

		await wrapper.get("button").trigger("click");

		expect(wrapper.find('[data-testid="finding-fix"]').exists()).toBe(false);
	});
});
