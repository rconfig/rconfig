import { describe, expect, it } from "vitest";

// @ts-ignore — JS module, no type declarations
import { notificationConfig } from "../notificationConfig";

describe("notificationConfig", () => {
	const { categories, channels, types } = notificationConfig.notifications;

	it("defines the four notification categories", () => {
		expect(Object.keys(categories)).toEqual(["system", "config", "connection", "task"]);
	});

	it("defines only the in-app and email channels", () => {
		expect(Object.keys(channels)).toEqual(["db", "mail"]);
		expect(channels.db.label).toBe("In-App");
		expect(channels.mail.label).toBe("Email");
	});

	it("gives every category a label and description", () => {
		for (const category of Object.values(categories)) {
			expect(typeof category.label).toBe("string");
			expect(category.label.length).toBeGreaterThan(0);
			expect(typeof category.description).toBe("string");
			expect(category.description.length).toBeGreaterThan(0);
		}
	});

	it("gives every channel a label and description", () => {
		for (const channel of Object.values(channels)) {
			expect(typeof channel.label).toBe("string");
			expect(typeof channel.description).toBe("string");
		}
	});

	it("gives every type a label and description", () => {
		for (const type of Object.values(types)) {
			expect(typeof type.label).toBe("string");
			expect(type.label.length).toBeGreaterThan(0);
			expect(typeof type.description).toBe("string");
			expect(type.description.length).toBeGreaterThan(0);
		}
	});

	it("includes the expected notification type keys", () => {
		expect(Object.keys(types)).toEqual([
			"system_notification_error",
			"config_download_completed",
			"config_purge_completed",
			"config_purge_failed_completed",
			"config_changed",
			"connection_device_failure",
			"task_completed",
			"task_download_report",
		]);
	});

	it("looks up a specific type by key", () => {
		expect(types.task_completed.label).toBe("Task Completed");
		expect(types.connection_device_failure.label).toBe("Device Connection Failure");
	});
});
