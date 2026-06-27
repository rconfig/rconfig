import { beforeEach, describe, expect, it, vi } from "vitest";
import { setActivePinia, createPinia } from "pinia";

const { toastSuccess, toastError } = vi.hoisted(() => ({
	toastSuccess: vi.fn(),
	toastError: vi.fn(),
}));

vi.mock("@/composables/useToaster", () => ({
	useToaster: () => ({ toastSuccess, toastError }),
}));

// Mirror the real config, which nests everything under `notifications`.
vi.mock("@/config/notificationConfig", () => ({
	notificationConfig: {
		notifications: {
			categories: {
				system: { label: "System", description: "System events" },
				config: { label: "Config", description: "Config events" },
				connection: { label: "Connection", description: "Connection events" },
				task: { label: "Task", description: "Task events" },
			},
			channels: {
				db: { label: "In-App", description: "Dashboard" },
				mail: { label: "Email", description: "Email" },
			},
			types: {
				system_notification_error: { label: "Err", description: "d" },
				config_download_completed: { label: "Dl", description: "d" },
				config_purge_completed: { label: "Purge", description: "d" },
				config_purge_failed_completed: { label: "PurgeF", description: "d" },
				config_changed: { label: "Changed", description: "d" },
				connection_device_failure: { label: "Fail", description: "d" },
				task_completed: { label: "Task", description: "d" },
				task_download_report: { label: "Report", description: "d" },
			},
		},
	},
}));

// The store references axios as a bare global rather than importing it.
const axiosGet = vi.fn();
const axiosPatch = vi.fn();
// @ts-ignore — install a global axios for the store under test
globalThis.axios = { get: axiosGet, patch: axiosPatch };

// @ts-ignore — store is JS, no type declarations
import { useNotificationStore } from "../useNotificationStore";

describe("useNotificationStore", () => {
	beforeEach(() => {
		toastSuccess.mockClear();
		toastError.mockClear();
		axiosGet.mockReset();
		axiosPatch.mockReset();
		setActivePinia(createPinia());
	});

	it("starts with empty state defaults", () => {
		const store = useNotificationStore();
		expect(store.preferences).toEqual({});
		expect(store.loading).toBe(false);
		expect(store.updating).toEqual({});
		expect(store.enums).toEqual({ categories: [], types: [], channels: [] });
	});

	describe("computed getters", () => {
		it("notificationCategories nests types under their category", () => {
			const store = useNotificationStore();
			store.enums = {
				categories: [{ key: "config", label: "Config" }],
				channels: [],
				types: [
					{ key: "config.a", category: "config" },
					{ key: "task.b", category: "task" },
				],
			};
			const result = store.notificationCategories;
			expect(result).toHaveLength(1);
			expect(result[0].types).toEqual([{ key: "config.a", category: "config" }]);
		});

		it("channels returns the enum channels", () => {
			const store = useNotificationStore();
			store.enums.channels = [{ key: "db" }];
			expect(store.channels).toEqual([{ key: "db" }]);
		});

		it("totalNotificationTypes counts the types", () => {
			const store = useNotificationStore();
			store.enums.types = [{ key: "a" }, { key: "b" }];
			expect(store.totalNotificationTypes).toBe(2);
		});

		it("enabledNotifications counts truthy channel preferences", () => {
			const store = useNotificationStore();
			store.preferences = {
				"config.download_completed": { db: true, mail: false },
				"task.completed": { db: true },
			};
			expect(store.enabledNotifications).toBe(2);
		});
	});

	describe("loadEnums", () => {
		it("stores the enums returned by the API", async () => {
			const payload = { categories: [{ key: "system" }], types: [], channels: [] };
			axiosGet.mockResolvedValue({ data: payload });
			const store = useNotificationStore();

			await store.loadEnums();

			expect(axiosGet).toHaveBeenCalledWith("/api/notification-enums");
			expect(store.enums).toEqual(payload);
		});

		it("falls back to local enums when the API fails", async () => {
			axiosGet.mockRejectedValue(new Error("down"));
			const store = useNotificationStore();

			await store.loadEnums();

			expect(store.enums.categories).toHaveLength(4);
			expect(store.enums.channels).toHaveLength(2);
			expect(store.enums.types).toHaveLength(8);
		});
	});

	describe("loadPreferences", () => {
		it("stores returned preferences and clears loading", async () => {
			axiosGet.mockResolvedValue({ data: { preferences: { "task.completed": { db: true } } } });
			const store = useNotificationStore();

			await store.loadPreferences();

			expect(store.preferences).toEqual({ "task.completed": { db: true } });
			expect(store.loading).toBe(false);
		});

		it("toasts and rethrows on failure", async () => {
			axiosGet.mockRejectedValue(new Error("boom"));
			const store = useNotificationStore();

			await expect(store.loadPreferences()).rejects.toThrow("boom");
			expect(toastError).toHaveBeenCalled();
			expect(store.loading).toBe(false);
		});
	});

	describe("updatePreference", () => {
		it("patches the API and updates local state on success", async () => {
			axiosPatch.mockResolvedValue({});
			const store = useNotificationStore();

			const result = await store.updatePreference("task.completed", "db", true);

			expect(axiosPatch).toHaveBeenCalledWith("/api/user/notification-preferences", {
				notification_type: "task.completed",
				channel: "db",
				enabled: true,
			});
			expect(store.preferences["task.completed"].db).toBe(true);
			expect(result).toEqual({ success: true });
			expect(toastSuccess).toHaveBeenCalled();
		});

		it("reverts local state and rethrows on failure", async () => {
			axiosPatch.mockRejectedValue(new Error("nope"));
			const store = useNotificationStore();
			store.preferences = { "task.completed": { db: true } };

			await expect(store.updatePreference("task.completed", "db", false)).rejects.toThrow("nope");
			expect(store.preferences["task.completed"].db).toBe(true);
			expect(toastError).toHaveBeenCalled();
		});

		it("clears the updating flag when finished", async () => {
			axiosPatch.mockResolvedValue({});
			const store = useNotificationStore();

			await store.updatePreference("task.completed", "mail", true);

			expect(store.isUpdating("task.completed", "mail")).toBe(false);
		});
	});

	describe("plain helpers", () => {
		it("getPreference reads a stored value and defaults to false", () => {
			const store = useNotificationStore();
			store.preferences = { "task.completed": { db: true } };
			expect(store.getPreference("task.completed", "db")).toBe(true);
			expect(store.getPreference("task.completed", "mail")).toBe(false);
			expect(store.getPreference("missing", "db")).toBe(false);
		});

		it("isUpdating reflects the updating map", () => {
			const store = useNotificationStore();
			store.updating["a.db"] = true;
			expect(store.isUpdating("a", "db")).toBe(true);
			expect(store.isUpdating("a", "mail")).toBe(false);
		});

		it("getSeverityVariant maps severities to variants", () => {
			const store = useNotificationStore();
			expect(store.getSeverityVariant("error")).toBe("danger");
			expect(store.getSeverityVariant("warning")).toBe("warning");
			expect(store.getSeverityVariant("info")).toBe("info");
			expect(store.getSeverityVariant("unknown")).toBe("secondary");
		});

		it("getChannelColor returns the channel color or gray", () => {
			const store = useNotificationStore();
			store.enums.channels = [{ key: "db", color: "blue" }];
			expect(store.getChannelColor("db")).toBe("blue");
			expect(store.getChannelColor("mail")).toBe("gray");
		});

		it("isSlackEnabled reflects the type flag", () => {
			const store = useNotificationStore();
			store.enums.types = [{ key: "task.completed", slackEnabled: true }];
			expect(store.isSlackEnabled("task.completed")).toBe(true);
			expect(store.isSlackEnabled("missing")).toBe(false);
		});
	});

	it("initialize loads enums then preferences", async () => {
		axiosGet
			.mockResolvedValueOnce({ data: { categories: [], types: [], channels: [] } })
			.mockResolvedValueOnce({ data: { preferences: {} } });
		const store = useNotificationStore();

		await store.initialize();

		expect(axiosGet).toHaveBeenNthCalledWith(1, "/api/notification-enums");
		expect(axiosGet).toHaveBeenNthCalledWith(2, "/api/user/notification-preferences");
	});
});
