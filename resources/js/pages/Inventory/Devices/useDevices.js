import axios from "axios";
import { ref, watch, inject } from "vue";
import { useDebounceFn } from "@vueuse/core";
import { useDialogStore } from "@/stores/dialogActions";
import { useToaster } from "@/composables/useToaster";
import { eventBus } from "@/composables/eventBus";
import { useRoute, useRouter } from "vue-router";
import { useReload } from "@/composables/tables/useReload";
import { useToggleSort } from "@/composables/tables/useToggleSort";
import { usePageSettingsStore } from "@/stores/pageSettings";

// --- State and stores ---
export function useDevices() {
	// Core state
	const currentPage = ref(1);
	const pageSettings = usePageSettingsStore();
	const perPage = ref(pageSettings.get("useDevices", "perPage", 10));
	const sortParam = ref("-id");
	const searchTerm = ref("");
	const filters = ref({});
	const dialogStore = useDialogStore();
	const editId = ref(0);
	const isLoading = ref(false);
	const lastPage = ref(1);
	const totalRecords = ref(0);
	const newDeviceModalKey = ref(1);
	const newBulkEditModalKey = ref(1);
	const devices = ref([]);
	const showConfirmDelete = ref(false);
	const filterStatus = ref([]);
	const filterCategories = ref([]);
	const filterTags = ref([]);
	const filterVendor = ref([]);
	const filterModel = ref([]);
	const router = useRouter();
	const formatters = inject("formatters");
	const inventoryIsLoading = ref(false);
	const downloadLink = ref(null);
	const { reload } = useReload(fetchDevices);
	const { toggleSort } = useToggleSort(sortParam, fetchDevices, "useDevices");

	// Load more state
	const isLoadingMore = ref(false);
	const hasMoreData = ref(true);
	const loadMorePage = ref(1);
	const isLoadMoreMode = ref(false);
	const originalPerPage = ref(null);

	// Dialog and utility
	const { openDialog } = dialogStore;
	const { toastSuccess, toastError } = useToaster();

	// --- Load More Mode Check ---
	const checkLoadMoreMode = (newPerPage) => {
		if (newPerPage >= 10000000) {
			isLoadMoreMode.value = true;
			originalPerPage.value = newPerPage;
			loadMorePage.value = 1;
			hasMoreData.value = true;
		} else {
			isLoadMoreMode.value = false;
			originalPerPage.value = null;
		}
	};

	// --- Fetching Data ---
	async function fetchDevices(params = {}, append = false) {
		if (append) {
			isLoadingMore.value = true;
		} else {
			isLoading.value = true;
			loadMorePage.value = 1;
			hasMoreData.value = true;
		}

		if (filterStatus.value.length > 0) {
			const ids = filterStatus.value.map((item) => item.id);
			filters.value[`filter[status]`] = ids.join(",");
		}

		try {
			const pageToFetch = append ? loadMorePage.value : currentPage.value;
			const response = await axios.get("/api/devices", {
				params: {
					page: pageToFetch,
					perPage: perPage.value,
					sort: sortParam.value,
					loadMore: isLoadMoreMode.value,
					...filters.value,
				},
			});

			if (append) {
				devices.value = [...devices.value, ...response.data.data];
				loadMorePage.value++;
			} else {
				devices.value = response.data.data;
			}

			lastPage.value = response.data.last_page;
			hasMoreData.value = pageToFetch < response.data.last_page;
			totalRecords.value = response.data.total;
		} catch (error) {
			console.error("Error fetching devices:", error);
			toastError("Error", "Failed to fetch devices.");
		} finally {
			isLoading.value = false;
			isLoadingMore.value = false;
		}
	}

	function exportInventoryReport() {
		inventoryIsLoading.value = true;

		axios
			.get("/api/device/inventory/export/")
			.then((response) => {
				const rawHtml = response.data.data.downloadLink;
				const tempDiv = document.createElement("div");
				tempDiv.innerHTML = rawHtml;

				const anchor = tempDiv.querySelector("a");
				if (!anchor || !anchor.href) {
					throw new Error("Download link not found in response.");
				}

				// trigger browser download - hacky , could fix the controller here
				const downloadUrl = anchor.href;
				const link = document.createElement("a");
				link.href = downloadUrl;
				link.setAttribute("download", "inventory_report.csv");
				document.body.appendChild(link);
				link.click();
				link.remove();

				inventoryIsLoading.value = false;
				toastSuccess("Export Successful", "The inventory report has been successfully exported.");
			})
			.catch((error) => {
				inventoryIsLoading.value = false;
				toastError("Error", "Failed to export inventory report.");
				console.error("Error exporting inventory report:", error);
			});
	}

	// --- Load More ---
	const loadMoreDevices = async () => {
		if (!hasMoreData.value || isLoadingMore.value) return;
		await fetchDevices({}, true);
	};

	// --- CRUD Actions ---
	const createDevice = (async) => {
		editId.value = 0;
		newDeviceModalKey.value = Math.random();
		openDialog("DialogNewDevice");
	};

	function updateDevice(id) {
		editId.value = id;
		newDeviceModalKey.value = Math.random();
		openDialog("DialogNewDevice");
	}

	function openBulkEditDialog() {
		newBulkEditModalKey.value = Math.random();
		openDialog("DeviceBulkEditDialog");
	}

	function purgeDeviceConfigs(id) {
		axios
			.post("/api/device/purge-failed-configs", { device_id: id })
			.then(() => {
				toastSuccess("Device Configurations Purged", "The device failed configurations purge jobs has been successfully sent to the queue.");
			})
			.catch((error) => {
				console.error("Error Purge device configs:", error);
				toastError("Error", "Failed to purge device configurations.");
			});
	}

	const deleteDevice = async (id) => {
		try {
			await axios.delete(`/api/devices/${id}`);
			fetchDevices();
			toastSuccess("Device Deleted", "The device has been deleted successfully.");
		} catch (error) {
			console.error("Error deleting device:", error);
			toastError("Error", "Failed to delete device.");
		}
	};

	const deleteManyDevices = async (ids) => {
		try {
			await axios.post("/api/devices/delete-many", { ids });
			fetchDevices();
			toastSuccess("Devices Deleted", "The devices have been deleted successfully.");
			showConfirmDelete.value = false;
			eventBus.emit("deleteManyDevicesSuccess");
		} catch (error) {
			console.error("Error deleting devices:", error);
			toastError("Error", "Failed to delete devices.");
			showConfirmDelete.value = false;
		}
	};

	const disableDevice = async (id, returnAllDevices = true) => {
		try {
			await axios.get(`/api/device/disable/${id}`);
			if (returnAllDevices) {
				fetchDevices();
			}
			toastSuccess("Device Disabled", "The device has been disabled successfully.");
		} catch (error) {
			console.error("Error disabling device:", error);
			toastError("Error", "Failed to disable device.");
		}
	};

	const enableDevice = async (id, returnAllDevices = true) => {
		try {
			await axios.get(`/api/device/enable/${id}`);
			if (returnAllDevices) {
				fetchDevices();
			}
			toastSuccess("Device Enabled", "The device has been enabled successfully.");
		} catch (error) {
			console.error("Error enabling device:", error);
			toastError("Error", "Failed to enable device.");
		}
	};

	const disableManyDevices = async (ids) => {
		try {
			ids.forEach(async (id) => {
				disableDevice(id, false);
			});
			setTimeout(() => {
				fetchDevices();
			}, 1000);
			toastSuccess("Devices Disabled", "The devices have been disabled successfully.");
		} catch (error) {
			console.error("Error disabling devices:", error);
			toastError("Error", "Failed to disable devices.");
		}
	};

	const enableManyDevices = async (ids) => {
		try {
			ids.forEach(async (id) => {
				enableDevice(id, false);
			});
			setTimeout(() => {
				fetchDevices();
			}, 1000);
			toastSuccess("Devices Enabled", "The devices have been enabled successfully.");
		} catch (error) {
			console.error("Error enabling devices:", error);
			toastError("Error", "Failed to enable devices.");
		}
	};

	const downloadManyDevices = async (ids) => {
		try {
			const response = await axios.post("/api/device/download-many", { device_ids: ids });
			toastSuccess("Download Queued", "The download job for the selected devices has been queued successfully.");
			toastSuccess("Download Queued", "Check the download status in the Queue Manager.");
		} catch (error) {
			console.error("Error downloading devices:", error);
			toastError("Error", "Failed to download devices.");
		}
	};

	const viewEditDialog = (id) => {
		editId.value = id;
		newDeviceModalKey.value = Math.random();
		openDialog("DialogNewDevice");
	};

	const handleSave = () => {
		fetchDevices();
		newDeviceModalKey.value = Math.random();
	};

	function handleKeyDown(event) {
		if (event.altKey && event.key === "n") {
			event.preventDefault();
			openDialog("DialogNewDevice");
		}
	}

	// --- Filtering, Sorting, and Watchers ---
	const debouncedFilter = useDebounceFn(() => {
		filters.value[`filter[q]`] = searchTerm.value;
		currentPage.value = 1;
		fetchDevices();
	}, 500);

	watch([currentPage], () => {
		if (!isLoadMoreMode.value) {
			fetchDevices();
		}
	});

	watch(perPage, (newVal, oldVal) => {
		if (newVal >= 10000000 && !isLoadMoreMode.value) {
			isLoadMoreMode.value = true;
			originalPerPage.value = newVal;
			pageSettings.set("useDevices", "perPage", 10000000);
			const nextTick = () => {
				pageSettings.set("useDevices", "perPage", 50);
				localStorage.setItem("DevicePerPage", "50");
			};
			nextTick();
			return;
		}

		if (newVal < 10000000 && isLoadMoreMode.value && originalPerPage.value !== null) {
			if (newVal !== 50 || oldVal < 10000000) {
				isLoadMoreMode.value = false;
				originalPerPage.value = null;
				pageSettings.set("useDevices", "perPage", newVal);
			}
		}

		if (!isLoadMoreMode.value || (newVal < 10000000 && newVal !== 50)) {
			pageSettings.set("useDevices", "perPage", newVal);
		}

		if (!(newVal >= 10000000 && !isLoadMoreMode.value)) {
			fetchDevices();
		}
	});

	watch(searchTerm, () => {
		debouncedFilter();
	});

	watch(
		filterStatus,
		(newVal, oldVal) => {
			if (newVal && newVal.length > 0) {
				const ids = newVal.map((item) => item.id);
				filters.value[`filter[status]`] = ids.join(",");
			} else {
				delete filters.value[`filter[status]`];
			}
			fetchDevices();
		},
		{ deep: true }
	);

	watch(
		filterCategories,
		(newVal, oldVal) => {
			if (newVal && newVal.length > 0) {
				const ids = newVal.map((item) => item.id);
				filters.value[`filter[category]`] = ids.join(",");
			} else {
				delete filters.value[`filter[category]`];
			}
			fetchDevices();
		},
		{ deep: true }
	);

	watch(
		filterTags,
		(newVal, oldVal) => {
			if (newVal && newVal.length > 0) {
				const ids = newVal.map((item) => item.id);
				filters.value[`filter[tag]`] = ids.join(",");
			} else {
				delete filters.value[`filter[tag]`];
			}
			fetchDevices();
		},
		{ deep: true }
	);

	watch(
		filterVendor,
		(newVal, oldVal) => {
			if (newVal && newVal.length > 0) {
				const ids = newVal.map((item) => item.id);
				filters.value[`filter[vendor]`] = ids.join(",");
			} else {
				delete filters.value[`filter[vendor]`];
			}
			fetchDevices();
		},
		{ deep: true }
	);

	watch(
		filterModel,
		(newVal, oldVal) => {
			if (newVal && newVal.length > 0) {
				const ids = newVal.map((item) => item.name);
				filters.value[`filter[device_model]`] = ids.join(",");
			} else {
				delete filters.value[`filter[device_model]`];
			}
			fetchDevices();
		},
		{ deep: true }
	);

	function clearFilters() {
		filters.value = {};
		filterStatus.value = [];
		filterCategories.value = [];
		filterTags.value = [];
		filterVendor.value = [];
		filterModel.value = [];
		searchTerm.value = "";
		fetchDevices();
	}

	function viewDeviceDetailsPane(id) {
		router.push({ name: "device-view", params: { id: parseInt(id) } });
	}

	// --- Initialization ---
	const initialPerPage = parseInt(localStorage.getItem("DevicePerPage") || "10");
	if (initialPerPage >= 10000000) {
		checkLoadMoreMode(initialPerPage);
	}

	function openSendSnippetModal() {
		openDialog("SendSnippetModal");
	}

	// --- Return ---
	return {
		// --- State ---
		devices,
		currentPage,
		perPage,
		lastPage,
		totalRecords,
		isLoading,
		isLoadingMore,
		hasMoreData,
		isLoadMoreMode,
		showConfirmDelete,
		editId,
		newDeviceModalKey,
		newBulkEditModalKey,
		searchTerm,
		filterStatus,
		filterCategories,
		filterTags,
		filterVendor,
		filterModel,
		sortParam,
		formatters,
		inventoryIsLoading,
		downloadLink,

		// --- Methods: CRUD & Actions ---
		createDevice,
		deleteDevice,
		deleteManyDevices,
		disableDevice,
		enableDevice,
		exportInventoryReport,
		handleKeyDown,
		handleSave,
		reload,
		viewDeviceDetailsPane,
		viewEditDialog,
		openBulkEditDialog,
		disableManyDevices,
		enableManyDevices,
		downloadManyDevices,
		openSendSnippetModal,

		// --- Methods: Table & Filtering ---
		fetchDevices,
		loadMoreDevices,
		clearFilters,
		toggleSort,
	};
}