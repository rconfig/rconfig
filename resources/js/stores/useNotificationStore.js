// stores/useNotificationStore.js
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { useToaster } from "@/composables/useToaster";
import { notificationConfig } from "@/config/notificationConfig";

export const useNotificationStore = defineStore("notifications", () => {
	const { toastSuccess, toastError } = useToaster();

	// State
	const preferences = ref({});
	const loading = ref(false);
	const updating = ref({});
	const enums = ref({
		categories: [],
		types: [],
		channels: [],
	});

	// Load enums from backend
	const loadEnums = async () => {
		try {
			const response = await axios.get("/api/notification-enums");
			enums.value = response.data;
		} catch (error) {
			console.error("Failed to load notification enums:", error);
			// Fallback to hardcoded values if API fails
			loadFallbackEnums();
		}
	};

	// Fallback enums (in case API is not ready yet)
	const loadFallbackEnums = () => {
		enums.value = {
			categories: [
				{
					key: "system",
					label: notificationConfig.categories.system.label,
					description: notificationConfig.categories.system.description,
					icon: "Server",
				},
				{
					key: "config",
					label: notificationConfig.categories.config.label,
					description: notificationConfig.categories.config.description,
					icon: "Settings",
				},
				{
					key: "connection",
					label: notificationConfig.categories.connection.label,
					description: notificationConfig.categories.connection.description,
					icon: "Wifi",
				},
				{
					key: "task",
					label: notificationConfig.categories.task.label,
					description: notificationConfig.categories.task.description,
					icon: "CheckCircle",
				},
			],
			channels: [
				{
					key: "db",
					label: notificationConfig.channels.db.label,
					description: notificationConfig.channels.db.description,
					icon: "Database",
					color: "blue",
				},
				{
					key: "mail",
					label: notificationConfig.channels.mail.label,
					description: notificationConfig.channels.mail.description,
					icon: "Mail",
					color: "green",
				},
			],
			types: [
				// System
				{
					key: "system.notification_error",
					category: "system",
					label: notificationConfig.types.system_notification_error.label,
					description: notificationConfig.types.system_notification_error.description,
					severity: "info",
					channels: ["db", "mail"],
				},
				// Config
				{
					key: "config.changed",
					category: "config",
					label: notificationConfig.types.config_changed.label,
					description: notificationConfig.types.config_changed.description,
					severity: "warning",
					channels: ["db", "mail"],
				},
				{
					key: "config.download_completed",
					category: "config",
					label: notificationConfig.types.config_download_completed.label,
					description: notificationConfig.types.config_download_completed.description,
					severity: "info",
					channels: ["db", "mail"],
				},
				{
					key: "config.purge_completed",
					category: "config",
					label: notificationConfig.types.config_purge_completed.label,
					description: notificationConfig.types.config_purge_completed.description,
					severity: "info",
					channels: ["db", "mail"],
				},
				{
					key: "config.purge_failed_completed",
					category: "config",
					label: notificationConfig.types.config_purge_failed_completed.label,
					description: notificationConfig.types.config_purge_failed_completed.description,
					severity: "info",
					channels: ["db", "mail"],
				},
				// Connection
				{
					key: "connection.device_failure",
					category: "connection",
					label: notificationConfig.types.connection_device_failure.label,
					description: notificationConfig.types.connection_device_failure.description,
					severity: "warning",
					channels: ["db", "mail"],
				},
				// Task
				{
					key: "task.completed",
					category: "task",
					label: notificationConfig.types.task_completed.label,
					description: notificationConfig.types.task_completed.description,
					severity: "info",
					channels: ["db", "mail"],
				},
				{
					key: "task.download_report",
					category: "task",
					label: notificationConfig.types.task_download_report.label,
					description: notificationConfig.types.task_download_report.description,
					severity: "info",
					channels: ["db", "mail"],
				},
			],
		};
	};

	// Computed getters
	const notificationCategories = computed(() => {
		return enums.value.categories.map((category) => ({
			...category,
			types: enums.value.types.filter((type) => type.category === category.key),
		}));
	});

	const channels = computed(() => enums.value.channels);

	const totalNotificationTypes = computed(() => enums.value.types.length);

	const enabledNotifications = computed(() => {
		let count = 0;
		Object.keys(preferences.value).forEach((type) => {
			Object.keys(preferences.value[type] || {}).forEach((channel) => {
				if (preferences.value[type][channel]) count++;
			});
		});
		return count;
	});

	// Actions
	const loadPreferences = async () => {
		try {
			loading.value = true;
			const response = await axios.get("/api/user/notification-preferences");
			preferences.value = response.data.preferences || {};
		} catch (error) {
			console.error("Failed to load notification preferences:", error);
			toastError("Error", "Failed to load notification preferences.");
			throw error;
		} finally {
			loading.value = false;
		}
	};

	const updatePreference = async (notificationType, channel, enabled) => {
		const updateKey = `${notificationType}.${channel}`;

		try {
			updating.value[updateKey] = true;

			await axios.patch("/api/user/notification-preferences", {
				notification_type: notificationType,
				channel: channel,
				enabled: enabled,
			});

			// Update local state
			if (!preferences.value[notificationType]) {
				preferences.value[notificationType] = {};
			}
			preferences.value[notificationType][channel] = enabled;

			toastSuccess("Success", `Notification preference for "${notificationType}" (${channel}) updated successfully.`);
			return { success: true };
		} catch (error) {
			console.error("Failed to update preference:", error);

			// Revert local state on error
			if (preferences.value[notificationType]) {
				preferences.value[notificationType][channel] = !enabled;
			}
			toastError("Error", `Failed to update notification preference for "${notificationType}" (${channel}).`);
			throw error;
		} finally {
			updating.value[updateKey] = false;
		}
	};

	const getPreference = (notificationType, channel) => {
		return preferences.value[notificationType]?.[channel] ?? false;
	};

	const isUpdating = (notificationType, channel) => {
		return updating.value[`${notificationType}.${channel}`] || false;
	};

	const getSeverityVariant = (severity) => {
		switch (severity) {
			case "error":
				return "danger";
			case "warning":
				return "warning";
			case "info":
				return "info";
			default:
				return "secondary";
		}
	};

	const getChannelColor = (channelKey) => {
		const channel = channels.value.find((c) => c.key === channelKey);
		return channel?.color || "gray";
	};

	const isSlackEnabled = (notificationType) => {
		const type = enums.value.types.find((t) => t.key === notificationType);
		return type?.slackEnabled || false;
	};

	// Initialize
	const initialize = async () => {
		await loadEnums();
		await loadPreferences();
	};

	return {
		// State
		preferences,
		loading,
		updating,
		enums,

		// Computed
		notificationCategories,
		channels,
		totalNotificationTypes,
		enabledNotifications,

		// Actions
		loadEnums,
		loadPreferences,
		updatePreference,
		getPreference,
		isUpdating,
		getSeverityVariant,
		getChannelColor,
		isSlackEnabled,
		initialize,
	};
});
