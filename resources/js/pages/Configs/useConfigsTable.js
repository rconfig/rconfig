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

export function useConfigsTable(props) {
	// Core state
	const currentPage = ref(1);
	const pageSettings = usePageSettingsStore();
	const perPage = ref(pageSettings.get("useConfigsTable", "perPage", 10));
	const sortParam = ref("-id");
	const searchTerm = ref("");
	const filters = ref({});
	const dialogStore = useDialogStore();
	const isLoading = ref(false);
	const lastPage = ref(1);
	const configs = ref([]);
	const showConfirmDelete = ref(false);
	const filterStatus = ref([]);
	const filterCommand = ref([]);
	const router = useRouter();
	const currentRouteName = useRoute().name;
	const showDownloadedConfigs = ref(false);
	const { reload } = useReload(getTabledata);
	const { toggleSort } = useToggleSort(sortParam, getTabledata, "useConfigsTable");

	// Load more state
	const isLoadingMore = ref(false);
	const hasMoreData = ref(true);
	const loadMorePage = ref(1);
	const isLoadMoreMode = ref(false);
	const originalPerPage = ref(null);

	// Dialog and utility
	const { openDialog, isDialogOpen } = dialogStore;
	const { toastSuccess, toastError } = useToaster();
	const formatters = inject("formatters");

	// --- Initialization ---
	const initialPerPage = parseInt(localStorage.getItem("ConfigPerPage") || "10");
	if (initialPerPage >= 10000000) {
		checkLoadMoreMode(initialPerPage);
	}

	// Data Fetching
	function getTabledata(append = false) {
		if (filterStatus.value.length > 0) {
			const ids = filterStatus.value.map((item) => item.id);
			filters.value[`filter[download_status]`] = ids.join(",");
		}

		if (props.configsId === 0) {
			fetchAllConfigs({}, append);
		} else {
			getTabledataByDeviceId({}, append);
		}
	}

	// Fetch all configs (global)
	async function fetchAllConfigs(params = {}, append = false) {
		if (append) {
			isLoadingMore.value = true;
		} else {
			isLoading.value = true;
			loadMorePage.value = 1;
			hasMoreData.value = true;
		}

		try {
			const pageToFetch = append ? loadMorePage.value : currentPage.value;
			const response = await axios.get(`/api/configs/`, {
				params: {
					page: pageToFetch,
					perPage: perPage.value,
					sort: sortParam.value,
					loadMore: isLoadMoreMode.value,
					...filters.value,
				},
			});

			if (append) {
				configs.value = [...configs.value, ...response.data.data];
				loadMorePage.value++;
			} else {
				configs.value = response.data.data;
			}

			lastPage.value = response.data.last_page;
			hasMoreData.value = pageToFetch < response.data.last_page;
		} catch (error) {
			console.error("Error fetching configs:", error);
			toastError("Error", "Failed to fetch configs.");
		} finally {
			isLoading.value = false;
			isLoadingMore.value = false;
		}
	}

	// Fetch configs by device id
	async function getTabledataByDeviceId(params = {}, append = false) {
		if (append) {
			isLoadingMore.value = true;
		} else {
			isLoading.value = true;
			loadMorePage.value = 1;
			hasMoreData.value = true;
		}

		filters.value[`filter[device_id]`] = props.configsId;

		try {
			const pageToFetch = append ? loadMorePage.value : currentPage.value;
			const response = await axios.get(`/api/configs/all-by-deviceid/${props.configsId}`, {
				params: {
					page: pageToFetch,
					perPage: perPage.value,
					sort: sortParam.value,
					loadMore: isLoadMoreMode.value,
					...filters.value,
				},
			});

			if (append) {
				configs.value = [...configs.value, ...response.data.data];
				loadMorePage.value++;
			} else {
				configs.value = response.data.data;
			}

			lastPage.value = response.data.last_page;
			hasMoreData.value = pageToFetch < response.data.last_page;
		} catch (error) {
			console.error("Error fetching configs:", error);
			toastError("Error", "Failed to fetch configs.");
		} finally {
			isLoading.value = false;
			isLoadingMore.value = false;
		}
	}

	// Business Logic
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

	function setDates(dates) {
		let startDate = "";
		let endDate = "";

		console.log(dates.start);
		console.log(dates.end);

		if (dates.start && dates.end) {
			// Use CalendarDate's toString() method which gives YYYY-MM-DD format
			startDate = dates.start.toString();
			endDate = dates.end.toString();
		}

		if (startDate && endDate) {
			filters.value[`filter[created_at_between]`] = `${startDate},${endDate}`;
		}
		getTabledata();
	}

	// --- Load More ---
	const loadMoreConfigs = async () => {
		if (!hasMoreData.value || isLoadingMore.value) return;
		getTabledata(true);
	};

	const deleteConfig = async (id) => {
		try {
			await axios.delete(`/api/configs/${id}`);
			getTabledata();
			toastSuccess("Config Deleted", "The config has been deleted successfully.");
			showConfirmDelete.value = false;
		} catch (error) {
			console.error("Error deleting config:", error);
			toastError("Error", "Failed to delete config.");
			showConfirmDelete.value = false;
		}
	};

	const deleteManyConfigs = async (ids) => {
		try {
			await axios.post("/api/configs/delete-many", { ids });
			getTabledata();
			toastSuccess("Configs Deleted", "The configs have been deleted successfully.");
			showConfirmDelete.value = false;
			eventBus.emit("deleteManyConfigsSuccess");
		} catch (error) {
			console.error("Error deleting configs:", error);
			toastError("Error", "Failed to delete configs.");
			showConfirmDelete.value = false;
		}
	};

	function handleKeyDown(event) {
		if (event.altKey && event.key === "n") {
			event.preventDefault();
			openDialog("DialogNewConfig");
		}
	}

	function toggleLatestVersion(e) {
		if (e) {
			filters.value[`filter[latest_version]`] = 1;
		} else {
			delete filters.value[`filter[latest_version]`];
		}
		getTabledata();
	}

	function toggleDownloaded(e) {
		if (e) {
			filters.value[`filter[config_downloaded]`] = 1;
			showDownloadedConfigs.value = true;
		} else {
			delete filters.value[`filter[config_downloaded]`];
			showDownloadedConfigs.value = false;
		}
		getTabledata();
	}

	function clearFilters() {
		filters.value = {};
		filterStatus.value = [];
		filterCommand.value = [];
		searchTerm.value = "";
		getTabledata();
	}

	function viewDetailsPane(id) {
		let referringRouteName = "configs";

		if (currentRouteName === "device-view") {
			referringRouteName = "device-view";
		}

		router.push({ name: "config-view", params: { id: parseInt(id) }, query: { ref: referringRouteName } });
	}

	// Watches & Computed
	// --- Filtering, Sorting, and Watchers ---
	const debouncedFilter = useDebounceFn(() => {
		filters.value[`filter[q]`] = searchTerm.value;
		currentPage.value = 1;
		getTabledata();
	}, 500);

	watch(currentPage, () => {
		if (!isLoadMoreMode.value) {
			getTabledata();
		}
	});

	watch(perPage, (newVal, oldVal) => {
		pageSettings.set("useConfigsTable", "perPage", perPage.value);

		if (newVal >= 10000000 && !isLoadMoreMode.value) {
			isLoadMoreMode.value = true;
			originalPerPage.value = newVal;
			const nextTick = () => {
				perPage.value = 50;
				pageSettings.set("useConfigsTable", "perPage", 50);
			};
			nextTick();
			return;
		}

		if (newVal < 10000000 && isLoadMoreMode.value && originalPerPage.value !== null) {
			if (newVal !== 50 || oldVal < 10000000) {
				isLoadMoreMode.value = false;
				originalPerPage.value = null;
			}
		}

		if (!isLoadMoreMode.value || (newVal < 10000000 && newVal !== 50)) {
			pageSettings.set("useConfigsTable", "perPage", newVal);
		}

		if (!(newVal >= 10000000 && !isLoadMoreMode.value)) {
			getTabledata();
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
				filters.value[`filter[download_status]`] = ids.join(",");
			} else {
				delete filters.value[`filter[download_status]`];
			}
			getTabledata();
		},
		{ deep: true }
	);

	watch(
		filterCommand,
		(newVal, oldVal) => {
			if (newVal && newVal.length > 0) {
				const commands = newVal.map((item) => item.command);
				filters.value[`filter[command]`] = commands.join(",");
			} else {
				delete filters.value[`filter[command]`];
			}
			getTabledata();
		},
		{ deep: true }
	);

	return {
		// State
		configs,
		currentPage,
		perPage,
		lastPage,
		isLoading,
		isLoadingMore,
		hasMoreData,
		isLoadMoreMode,
		showConfirmDelete,
		searchTerm,
		filters,
		filterStatus,
		filterCommand,
		sortParam,
		showDownloadedConfigs,
		formatters,

		// Methods
		isDialogOpen,
		clearFilters,
		openDialog,
		deleteConfig,
		deleteManyConfigs,
		getTabledata,
		handleKeyDown,
		loadMoreConfigs,
		reload,
		setDates,
		toggleDownloaded,
		toggleLatestVersion,
		toggleSort,
		viewDetailsPane,
	};
}
