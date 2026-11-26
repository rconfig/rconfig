import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { defineComponent, h, nextTick } from "vue";
import { flushPromises, mount } from "@vue/test-utils";

const { get, patch, post } = vi.hoisted(() => ({ get: vi.fn(), patch: vi.fn(), post: vi.fn() }));
vi.mock("axios", () => ({
	default: { get, patch, post },
}));

const toastError = vi.fn();
const toastSuccess = vi.fn();
vi.mock("@/composables/useToaster", () => ({
	useToaster: () => ({ toastError, toastSuccess }),
}));

// @ts-ignore — composable is JS, no type declarations
import { useDataPurgePanel } from "../useDataPurgePanel";

const settingsResponse = () => ({
	data: {
		current_page: 1,
		per_page: 25,
		total: 3,
		last_page: 1,
		data: [
			{ id: 1, target: "activity_log", retention_days: 90, options: { batch_size: 1000, cleanup_frequency: "daily", enabled: false } },
			{
				id: 2,
				target: "configs",
				retention_days: 1825,
				options: { batch_size: 1000, cleanup_frequency: "daily", enabled: false, min_versions_per_device_command: 3, non_downloaded_retention_days: 30 },
			},
			{ id: 3, target: "tracked_jobs", retention_days: 30, options: '{"batch_size":500,"cleanup_frequency":"weekly","enabled":true}' },
		],
	},
});

async function mountPanel() {
	let panel: any;
	const wrapper = mount(
		defineComponent({
			setup() {
				panel = useDataPurgePanel();
				return () => h("div");
			},
		}),
		{ global: { provide: { formatters: {} } } },
	);
	await flushPromises();
	return { panel, wrapper };
}

describe("useDataPurgePanel", () => {
	beforeEach(() => {
		vi.useFakeTimers();
		get.mockReset().mockResolvedValue(settingsResponse());
		patch.mockReset().mockResolvedValue({ data: { success: true } });
		post.mockReset().mockResolvedValue({ data: {} });
		toastError.mockClear();
		toastSuccess.mockClear();
	});

	afterEach(() => {
		vi.useRealTimers();
	});

	it("splits the configs setting from the general table and reads options in either form", async () => {
		const { panel } = await mountPanel();

		expect(panel.configsSetting.value.target).toBe("configs");
		expect(panel.otherSettings.value.map((s: any) => s.target)).toEqual(["activity_log", "tracked_jobs"]);
		expect(panel.totalRecords.value).toBe(2);
		expect(panel.getOptionValue(panel.otherSettings.value[1], "batch_size", 1000)).toBe(500);
		expect(panel.getOptionValue(panel.otherSettings.value[0], "enabled", false)).toBe(false);
		expect(panel.configsEditForm.enabled).toBe(false);
		expect(panel.configsEditForm.min_versions_per_device_command).toBe(3);
	});

	it("does not save the config purge card just because the page loaded", async () => {
		await mountPanel();
		await vi.advanceTimersByTimeAsync(1000);

		expect(patch).not.toHaveBeenCalled();
	});

	it("auto-saves the config purge card after a change", async () => {
		const { panel } = await mountPanel();

		panel.configsEditForm.enabled = true;
		panel.configsEditForm.retention_days = 365;
		await nextTick();
		await vi.advanceTimersByTimeAsync(400);

		expect(patch).toHaveBeenCalledTimes(1);
		expect(patch.mock.calls[0][0]).toBe("/api/settings/purge/2");
		expect(patch.mock.calls[0][1].retention_days).toBe(365);
		expect(JSON.parse(patch.mock.calls[0][1].options)).toEqual({
			batch_size: 1000,
			cleanup_frequency: "daily",
			non_downloaded_retention_days: 30,
			min_versions_per_device_command: 3,
			enabled: true,
		});
	});

	it("does not send an invalid config purge value and reports a failed save", async () => {
		const { panel } = await mountPanel();

		panel.configsEditForm.retention_days = "";
		await nextTick();
		await vi.advanceTimersByTimeAsync(400);

		expect(patch).not.toHaveBeenCalled();
		expect(panel.configsSaveError.value).toContain("whole number");

		patch.mockRejectedValueOnce({ response: { data: { message: "Server said no" } } });
		panel.configsEditForm.retention_days = 10;
		await nextTick();
		await vi.advanceTimersByTimeAsync(400);

		expect(panel.configsSaveError.value).toBe("Server said no");
		expect(toastError).toHaveBeenCalledWith("Error", "Server said no");
	});

	it("keeps the config purge card when a filter leaves configs out of the list", async () => {
		const { panel } = await mountPanel();

		get.mockResolvedValue({ data: { current_page: 1, per_page: 25, total: 1, last_page: 1, data: [settingsResponse().data.data[2]] } });
		panel.filters.enabledOnly = true;
		panel.applyEnabledFilter();
		await flushPromises();

		expect(get.mock.calls.at(-1)[1].params["filter[enabled_only]"]).toBe(1);
		expect(panel.otherSettings.value).toHaveLength(1);
		expect(panel.configsSetting.value.target).toBe("configs");
	});

	it("toggles a table by sending only the enabled flag", async () => {
		const { panel } = await mountPanel();

		panel.toggleEnableDisable(panel.otherSettings.value[0]);
		await flushPromises();

		expect(patch.mock.calls[0][0]).toBe("/api/settings/purge/1");
		expect(JSON.parse(patch.mock.calls[0][1].options)).toEqual({ enabled: true });
		expect(toastSuccess).toHaveBeenCalledWith("Setting Updated", "Purge setting enabled successfully.");
	});

	it("saves an inline edit and rejects a bad retention value", async () => {
		const { panel } = await mountPanel();

		panel.startEdit(panel.otherSettings.value[1]);
		expect(panel.editForm.batch_size).toBe(500);
		expect(panel.editForm.enabled).toBe(true);

		panel.editForm.retention_days = 0;
		panel.saveEdit();
		expect(patch).not.toHaveBeenCalled();
		expect(toastError).toHaveBeenCalledWith("Validation Error", "Retention days must be a positive integer.");

		panel.editForm.retention_days = 14;
		panel.saveEdit();
		await flushPromises();

		expect(patch.mock.calls[0][0]).toBe("/api/settings/purge/3");
		expect(patch.mock.calls[0][1].retention_days).toBe(14);
		expect(panel.editingId.value).toBeNull();
	});

	it("purges all failed configs through the existing device endpoint", async () => {
		const { panel } = await mountPanel();

		await panel.purgeAllFailedConfigs();

		expect(post).toHaveBeenCalledWith("/api/device/purge-failed-configs", { device_id: "--all" });
		expect(toastSuccess).toHaveBeenCalledWith("Purge Started", "Failed configs purge job has been queued for all devices.");
	});
});
