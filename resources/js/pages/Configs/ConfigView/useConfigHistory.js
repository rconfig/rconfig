import axios from "axios";
import { ref, computed, watch } from "vue";
import { useToaster } from "@/composables/useToaster";
import { usePageSettingsStore } from "@/stores/pageSettings";
import { useReload } from "@/composables/tables/useReload";

export function useConfigHistory() {
	// Reactive state
	const configsData = ref(null);
	const isLoading = ref(false);
	const currentPage = ref(1);
	const lastPage = ref(1);
	const isRefreshing = ref(false);

	// UI State
	const expandedConfigs = ref([]);
	const viewingConfigId = ref(null);
	const viewingConfigVersion = ref(null);
	const viewingChangesConfigId = ref(null);
	const changeViewOn = ref(false);

	// Settings and store
	const pageSettings = usePageSettingsStore();
	const perPage = ref(pageSettings.get("useConfigHistory", "perPage", 10));
	const { toastError } = useToaster();

	// Store the current deviceId and command for pagination
	let currentDeviceId = null;
	let currentCommand = null;

	// Computed properties
	const totalPages = computed(() => {
		return configsData.value ? configsData.value.last_page : 0;
	});

	const totalItems = computed(() => {
		return configsData.value ? configsData.value.total : 0;
	});

	const hasNextPage = computed(() => {
		return configsData.value && currentPage.value < configsData.value.last_page;
	});

	const hasPrevPage = computed(() => {
		return currentPage.value > 1;
	});

	const shouldShowLoading = computed(() => {
		return isLoading.value && !isRefreshing.value;
	});

	const shouldShowRefreshSkeleton = computed(() => {
		return isRefreshing.value || isLoading.value;
	});

	// Core functions
	async function fetchConfigHistory(deviceId = currentDeviceId, command = currentCommand) {
		// Don't fetch if we don't have the required parameters
		if (!deviceId || !command) {
			console.warn("Missing deviceId or command for config history fetch");
			return;
		}

		// Update current parameters
		currentDeviceId = deviceId;
		currentCommand = command;

		isLoading.value = true;

		try {
			const response = await axios.get(`/api/configs/config-history/${deviceId}/${command}`, {
				params: {
					page: currentPage.value,
					perPage: perPage.value,
				},
			});

			configsData.value = response.data;
			lastPage.value = response.data.last_page;
		} catch (error) {
			console.error("Config history fetch error:", error);
			toastError("Error", "Could not retrieve config history!");
		} finally {
			isLoading.value = false;
		}
	}

	function initializeConfigHistory(deviceId, command) {
		currentDeviceId = deviceId;
		currentCommand = command;
		currentPage.value = 1; // Reset to first page
		configsData.value = null; // Clear existing data
		fetchConfigHistory(deviceId, command);
	}

	function toggleExpand(configId) {
		const index = expandedConfigs.value.indexOf(configId);
		if (index > -1) {
			// If clicking on already expanded config, collapse it
			expandedConfigs.value.splice(index, 1);
		} else {
			// Close all other expanded configs and open this one (accordion behavior)
			expandedConfigs.value = [configId];
		}
	}

	function viewConfigChanges(configId, emitFn) {
		// Clear any previous config viewing state
		viewingConfigId.value = null;
		viewingConfigVersion.value = null;

		// Check if we're already viewing changes for this specific config
		const isCurrentlyViewingThisConfig = changeViewOn.value && viewingChangesConfigId.value === configId;

		if (isCurrentlyViewingThisConfig) {
			// If we're already viewing changes for this config, turn it off
			changeViewOn.value = false;
			viewingChangesConfigId.value = null;
			emitFn("viewConfigChanges", configId); // Still need to emit when turning off
		} else {
			// If we're not viewing changes for this config, turn it on
			changeViewOn.value = true;
			viewingChangesConfigId.value = configId;
			emitFn("viewConfigChanges", configId);
		}
	}

	function viewConfig(configId, configVersion, emitFn) {
		let version = configVersion || 1;

		// Clear any previous changes viewing state
		changeViewOn.value = false;
		viewingChangesConfigId.value = null;

		// Set the currently viewing config
		viewingConfigId.value = configId;
		viewingConfigVersion.value = version;

		// combine configId and version to view specific config
		const configProps = { id: configId, version: version };
		emitFn("viewConfig", configProps);
	}

	// Helper function to check if a config is currently being viewed
	function isConfigBeingViewed(configId, configVersion) {
		return viewingConfigId.value === configId && viewingConfigVersion.value === configVersion;
	}

	// Helper function to check if this specific config is showing changes
	function isConfigShowingChanges(configId) {
		return changeViewOn.value && viewingChangesConfigId.value === configId;
	}

	function handlePageChange(page, configData) {
		if (configData) {
			currentPage.value = page;
		}
	}

	function handlePerPageChange(itemsPerPage, configData) {
		if (configData) {
			perPage.value = itemsPerPage;
		}
	}

	// Enhanced reload function with smooth transition
	async function handleReload() {
		if (isRefreshing.value) return; // Prevent multiple simultaneous refreshes

		isRefreshing.value = true;
		try {
			await reload();
		} finally {
			// Add small delay to prevent flash
			setTimeout(() => {
				isRefreshing.value = false;
			}, 150);
		}
	}

	// Define reload function that will be used by useReload
	function reloadConfigHistory() {
		if (currentDeviceId && currentCommand) {
			// Reset pagination states
			currentPage.value = 1;
			configsData.value = null; // Clear existing data to prevent flash
			fetchConfigHistory(currentDeviceId, currentCommand);
		}
	}

	const { reload } = useReload(reloadConfigHistory);

	// Watchers
	watch(currentPage, (newPage) => {
		if (currentDeviceId && currentCommand) {
			fetchConfigHistory(currentDeviceId, currentCommand);
		}
	});

	watch(perPage, (newVal) => {
		pageSettings.set("useConfigHistory", "perPage", newVal);

		if (currentDeviceId && currentCommand) {
			currentPage.value = 1; // Reset to first page when changing perPage
			fetchConfigHistory(currentDeviceId, currentCommand);
		}
	});

	// Returns
	return {
		// Data
		configsData,
		isLoading,
		isRefreshing,

		// UI State
		expandedConfigs,
		viewingConfigId,
		viewingConfigVersion,
		viewingChangesConfigId,
		changeViewOn,

		// Pagination state
		currentPage,
		perPage,
		lastPage,

		// Computed properties
		totalPages,
		totalItems,
		hasNextPage,
		hasPrevPage,
		shouldShowLoading,
		shouldShowRefreshSkeleton,

		// Methods
		fetchConfigHistory,
		initializeConfigHistory,
		toggleExpand,
		viewConfigChanges,
		viewConfig,
		isConfigBeingViewed,
		isConfigShowingChanges,
		handlePageChange,
		handlePerPageChange,
		handleReload,
		reload,
	};
}
