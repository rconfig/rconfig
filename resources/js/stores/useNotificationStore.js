// stores/useNotificationStore.js
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { useToaster } from "@/composables/useToaster";

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
					label: "notifications.categories.system",
					description: "notifications.categories.system_desc",
					icon: "Server",
				},
				{
					key: "config",
					label: "notifications.categories.config",
					description: "notifications.categories.config_desc",
					icon: "Settings",
				},
				{
					key: "backup",
					label: "notifications.categories.backup",
					description: "notifications.categories.backup_desc",
					icon: "HardDrive",
				},
				{
					key: "connection",
					label: "notifications.categories.connection",
					description: "notifications.categories.connection_desc",
					icon: "Wifi",
				},
				{
					key: "import",
					label: "notifications.categories.import",
					description: "notifications.categories.import_desc",
					icon: "Upload",
				},
				{
					key: "task",
					label: "notifications.categories.task",
					description: "notifications.categories.task_desc",
					icon: "CheckCircle",
				},
				{
					key: "compliance",
					label: "notifications.categories.compliance",
					description: "notifications.categories.compliance_desc",
					icon: "FileText",
				},
			],
			channels: [
				{
					key: "db",
					label: "notifications.channels.db",
					description: "notifications.channels.db_desc",
					icon: "Database",
					color: "blue",
				},
				{
					key: "mail",
					label: "notifications.channels.mail",
					description: "notifications.channels.mail_desc",
					icon: "Mail",
					color: "green",
				},
				{
					key: "slack",
					label: "notifications.channels.slack",
					description: "notifications.channels.slack_desc",
					icon: "MessageSquare",
					color: "purple",
				},
			],
			types: [
				// System
				{
					key: "system.auth_changed",
					category: "system",
					label: "notifications.types.system_auth_changed",
					description: "notifications.types.system_auth_changed_desc",
					severity: "error",
					channels: ["db", "mail", "slack"],
					slackEnabled: true,
				},
				{
					key: "system.notification_error",
					category: "system",
					label: "notifications.types.system_notification_error",
					description: "notifications.types.system_notification_error_desc",
					severity: "error",
					channels: ["db", "mail", "slack"],
					slackEnabled: false,
				},
				// Config
				{
					key: "config.changed",
					category: "config",
					label: "notifications.types.config_changed",
					description: "notifications.types.config_changed_desc",
					severity: "warning",
					channels: ["db", "mail", "slack"],
					slackEnabled: true,
				},
				{
					key: "config.download_completed",
					category: "config",
					label: "notifications.types.config_download_completed",
					description: "notifications.types.config_download_completed_desc",
					severity: "info",
					channels: ["db", "mail"],
					slackEnabled: false,
				},
				{
					key: "config.purge_completed",
					category: "config",
					label: "notifications.types.config_purge_completed",
					description: "notifications.types.config_purge_completed_desc",
					severity: "info",
					channels: ["db", "mail"],
					slackEnabled: false,
				},
				{
					key: "config.purge_failed_completed",
					category: "config",
					label: "notifications.types.config_purge_failed_completed",
					description: "notifications.types.config_purge_failed_completed_desc",
					severity: "info",
					channels: ["db", "mail"],
					slackEnabled: false,
				},
				{
					key: "config.snmp_trap",
					category: "config",
					label: "notifications.types.config_snmp_trap",
					description: "notifications.types.config_snmp_trap_desc",
					severity: "warning",
					channels: ["db", "mail", "slack"],
					slackEnabled: true,
				},
				{
					key: "snippet.sent",
					category: "config",
					label: "notifications.types.snippet_sent",
					description: "notifications.types.snippet_sent_desc",
					severity: "info",
					channels: ["db", "mail"],
					slackEnabled: false,
				},
				// Backup
				{
					key: "backup.run_completed",
					category: "backup",
					label: "notifications.types.backup_run_completed",
					description: "notifications.types.backup_run_completed_desc",
					severity: "warning",
					channels: ["db", "mail"],
					slackEnabled: false,
				},
				{
					key: "backup.cleanup_completed",
					category: "backup",
					label: "notifications.types.backup_cleanup_completed",
					description: "notifications.types.backup_cleanup_completed_desc",
					severity: "info",
					channels: ["db", "mail"],
					slackEnabled: false,
				},
				// Connection
				{
					key: "connection.device_failure",
					category: "connection",
					label: "notifications.types.connection_device_failure",
					description: "notifications.types.connection_device_failure_desc",
					severity: "error",
					channels: ["db", "mail"],
					slackEnabled: false,
				},
				// Import
				{
					key: "import.bulk_completed",
					category: "import",
					label: "notifications.types.import_bulk_completed",
					description: "notifications.types.import_bulk_completed_desc",
					severity: "info",
					channels: ["db", "mail"],
					slackEnabled: false,
				},
				{
					key: "import.bulk_failed",
					category: "import",
					label: "notifications.types.import_bulk_failed",
					description: "notifications.types.import_bulk_failed_desc",
					severity: "error",
					channels: ["db", "mail"],
					slackEnabled: false,
				},
				// Task
				{
					key: "task.completed",
					category: "task",
					label: "notifications.types.task_completed",
					description: "notifications.types.task_completed_desc",
					severity: "info",
					channels: ["db", "mail"],
					slackEnabled: false,
				},
				{
					key: "task.download_report",
					category: "task",
					label: "notifications.types.task_download_report",
					description: "notifications.types.task_download_report_desc",
					severity: "info",
					channels: ["mail"],
					slackEnabled: false,
				},
				// Compliance
				{
					key: "compliance.report",
					category: "compliance",
					label: "notifications.types.compliance_report",
					description: "notifications.types.compliance_report_desc",
					severity: "info",
					channels: ["mail"],
					slackEnabled: false,
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
