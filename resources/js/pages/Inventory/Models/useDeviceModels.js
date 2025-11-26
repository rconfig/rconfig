import axios from "axios";
import { ref, watch } from "vue";
import { useDebounceFn } from "@vueuse/core";
import { useDialogStore } from "@/stores/dialogActions";
import { useToaster } from "@/composables/useToaster";
import { eventBus } from "@/composables/eventBus";
import { useReload } from "@/composables/tables/useReload";
import { useToggleSort } from "@/composables/tables/useToggleSort";
import { usePageSettingsStore } from "@/stores/pageSettings";

// --- Composable: useDeviceModels ---
export function useDeviceModels() {
	// --- State ---
	const currentPage = ref(1);
	const pageSettings = usePageSettingsStore();
	const perPage = ref(pageSettings.get("useDeviceModels", "perPage", 15));
	const sortParam = ref("name");
	const searchTerm = ref("");
	const filters = ref({});
	const deviceModels = ref([]);
	const isLoading = ref(false);
	const lastPage = ref(1);
	const editModelName = ref("");
	const newModelModalKey = ref(1);
	const showConfirmDelete = ref(false);
	const editId = ref(0);

	// --- Stores & Utilities ---
	const dialogStore = useDialogStore();
	const { openDialog } = dialogStore;
	const { toastSuccess, toastError } = useToaster();
	const { reload } = useReload(fetchDeviceModels);
	const { toggleSort } = useToggleSort(sortParam, fetchDeviceModels, "useDeviceModels");

	// --- Fetch Device Models ---
	async function fetchDeviceModels(params = {}) {
		isLoading.value = true;
		try {
			const response = await axios.get("/api/device-models", {
				params: {
					page: currentPage.value,
					perPage: perPage.value,
					sort: sortParam.value,
					...filters.value,
				},
			});
			deviceModels.value = response.data;
			lastPage.value = response.data.last_page;
		} catch (error) {
			console.error("Error fetching device models:", error);
			toastError("Error", error.response.data.message || "Failed to fetch device models.");
		} finally {
			isLoading.value = false;
		}
	}

	// --- Create Device Model Dialog ---
	const createDeviceModel = async (id) => {
		editModelName.value = "";
		editId.value = id || 0;
		newModelModalKey.value = Math.random(); // Force re-render of the dialog component
		openDialog("DialogNewDeviceModel");
	};

	// --- Update Device Model Dialog ---
	function updateDeviceModel(modelName) {
		editModelName.value = modelName;
		newModelModalKey.value = Math.random(); // Force re-render of the dialog component
		openDialog("DialogNewDeviceModel");
	}

	// --- Delete Device Model ---
	const deleteDeviceModel = async (modelName) => {
		try {
			await axios.delete(`/api/device-models/${encodeURIComponent(modelName)}`);
			fetchDeviceModels();
			toastSuccess("Device Model Deleted", "The device model has been deleted successfully.");
			showConfirmDelete.value = false;
		} catch (error) {
			if (error.status === 422) {
				toastError("Error", error.response.data.message);
			} else if (error.status === 409) {
				toastError("Error", error.response.data.message);
			} else {
				console.error("Error deleting Device Model:", error);
				toastError("Error", "Failed to delete Device Model.");
			}
		}
	};

	// --- Delete Many Device Models ---
	const deleteManyDeviceModels = async (ids) => {
		try {
			await axios.post("/api/device-models/delete-many", { ids });
			fetchDeviceModels();
			toastSuccess("Device Models Deleted", "The device models have been deleted successfully.");
			showConfirmDelete.value = false;
			eventBus.emit("deleteManyDeviceModelsSuccess");
		} catch (error) {
			if (error.status === 422) {
				toastError("Error", error.response.data.message);
			} else {
				console.error("Error deleting Device Models:", error);
				toastError("Error", "Failed to delete Device Models.");
			}
			showConfirmDelete.value = false;
		}
	};

	// --- View/Edit Dialog ---
	const viewEditDialog = (id) => {
		editId.value = id;
		newModelModalKey.value = Math.random();
		openDialog("DialogNewDeviceModel");
	};

	// --- View Devices Dialog ---
	const viewDevicesDialog = (modelName) => {
		// Navigate to devices page with model filter
		window.open(`/devices?filter[device_model]=${encodeURIComponent(modelName)}`, "_blank");
	};

	// --- Get Devices for Model ---
	const getDevicesForModel = async (modelName) => {
		try {
			const response = await axios.get(`/api/device-models/${encodeURIComponent(modelName)}/devices`);
			return response.data.data;
		} catch (error) {
			console.error("Error fetching devices for model:", error);
			toastError("Error", "Failed to fetch devices for this model.");
			return null;
		}
	};

	// --- Handle Save (refresh and re-render) ---
	const handleSave = () => {
		fetchDeviceModels();
		newModelModalKey.value = Math.random();
	};

	// --- Keyboard Shortcut ---
	function handleKeyDown(event) {
		if (event.altKey && event.key === "n") {
			event.preventDefault();
			openDialog("DialogNewDeviceModel");
		}
	}

	// --- Debounced Filter ---
	const debouncedFilter = useDebounceFn(() => {
		filters.value[`filter[q]`] = searchTerm.value;
		currentPage.value = 1;
		fetchDeviceModels();
	}, 500);

	// --- Watchers ---
	watch(currentPage, () => {
		fetchDeviceModels();
	});

	watch(searchTerm, () => {
		debouncedFilter();
	});

	watch(perPage, (newVal) => {
		pageSettings.set("useDeviceModels", "perPage", newVal);
		fetchDeviceModels();
	});

	// --- Return ---
	return {
		// State
		reload,
		deviceModels,
		isLoading,
		currentPage,
		perPage,
		lastPage,
		editModelName,
		newModelModalKey,
		searchTerm,
		showConfirmDelete,
		editId,

		// Dialogs & Actions
		openDialog,
		fetchDeviceModels,
		createDeviceModel,
		updateDeviceModel,
		deleteDeviceModel,
		deleteManyDeviceModels,
		handleSave,
		handleKeyDown,
		viewEditDialog,
		viewDevicesDialog,
		getDevicesForModel,
		toggleSort,
		sortParam,
	};
}
