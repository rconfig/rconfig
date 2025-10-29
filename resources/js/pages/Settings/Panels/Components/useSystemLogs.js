import axios from "axios";
import { eventBus } from "@/composables/eventBus";
import { ref, onMounted, watch, inject } from "vue";
import { useDebounceFn } from "@vueuse/core";
import { useToaster } from "@/composables/useToaster"; // Import the composable
import { useReload } from "@/composables/tables/useReload";
import { useToggleSort } from "@/composables/tables/useToggleSort";
import { usePageSettingsStore } from "@/stores/pageSettings";

export function useSystemLogs() {
	const { toastDefault, toastSuccess, toastError, toastInfo, toastWarning } = useToaster(); // Using toaster for notifications

	const currentPage = ref(1);
	const filters = ref({});
	const isLoading = ref(false);
	const lastPage = ref(1);
	const logs = ref({});
	const pageSettings = usePageSettingsStore();
	const perPage = ref(pageSettings.get("useSystemLogs", "perPage", 10));
	const searchTerm = ref("");
	const showConfirmDelete = ref(false);
	const sortParam = ref("-id");
	const formatters = inject("formatters");
	const filterSeverity = ref([]);

	// Load more state
	const isLoadingMore = ref(false);
	const hasMoreData = ref(true);
	const loadMorePage = ref(1);
	const isLoadMoreMode = ref(false);
	const originalPerPage = ref(null);

	const { reload } = useReload(fetchLogs);
	const { toggleSort } = useToggleSort(sortParam, fetchLogs, "useSystemLogs");

	// Load More Mode Check
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

	// Initialize load more mode on component setup (in case perPage is loaded from cache)
	checkLoadMoreMode(perPage.value);

	onMounted(() => {
		fetchLogs();
	});

	async function fetchLogs(params = {}, append = false) {
		if (append) {
			isLoadingMore.value = true;
		} else {
			isLoading.value = true;
			loadMorePage.value = 1;
			hasMoreData.value = true;
		}

		try {
			const pageToFetch = append ? loadMorePage.value : currentPage.value;
			const response = await axios.get("/api/activitylogs", {
				params: {
					page: pageToFetch,
					perPage: perPage.value,
					sort: sortParam.value,
					loadMore: isLoadMoreMode.value,
					...filters.value,
				},
			});

			if (append) {
				logs.value.data = [...logs.value.data, ...response.data.data];
				loadMorePage.value++;
			} else {
				logs.value = response.data;
			}

			lastPage.value = response.data.last_page;
			hasMoreData.value = pageToFetch < response.data.last_page;
		} catch (error) {
			console.error("Error fetching logs:", error);
			toastError("Error", "Failed to fetch logs.");
		} finally {
			isLoading.value = false;
			isLoadingMore.value = false;
		}
	}

	// Load More
	const loadMoreLogs = async () => {
		if (!hasMoreData.value || isLoadingMore.value) return;
		await fetchLogs({}, true);
	};

	watch(
		filterSeverity,
		(newVal, oldVal) => {
			if (newVal && newVal.length > 0) {
				const names = newVal.map((item) => item.name);
				filters.value[`filter[log_name]`] = names.join(",");
			} else {
				delete filters.value[`filter[log_name]`];
			}
			fetchLogs();
		},
		{ deep: true }
	);

	// Delete Log
	const deleteLog = async (id) => {
		try {
			await axios.delete(`/api/activitylogs/${id}`);
			fetchLogs(); // Refresh logs list after deletion
			toastError("Log Deleted", "The log has been deleted successfully.");
			toastDefault("Log Deleted", "The log has been deleted successfully.");
			toastSuccess("Log Deleted", "The log has been deleted successfully.");
			toastInfo("Log Deleted", "The log has been deleted successfully.");
			toastWarning("Log Deleted", "The log has been deleted successfully.");
			showConfirmDelete.value = false;
		} catch (error) {
			console.error("Error deleting log:", error);
			toastError("Error", "Failed to delete log.");
			showConfirmDelete.value = false;
		}
	};

	// Delete Many Tags
	const deleteManyLogs = async (ids) => {
		try {
			await axios.post("/api/activitylogs/delete-many", { ids });
			fetchLogs(); // Refresh logs list after deletion
			toastSuccess("Logs Deleted", "The logs have been deleted successfully.");
			showConfirmDelete.value = false;
			eventBus.emit("deleteManyLogsSuccess");
		} catch (error) {
			console.error("Error deleting logs:", error);
			toastError("Error", "Failed to delete logs.");
			showConfirmDelete.value = false;
		}
	};

	const debouncedFilter = useDebounceFn(() => {
		filters.value[`filter[description]`] = searchTerm.value;
		currentPage.value = 1;
		fetchLogs();
	}, 500);

	// Watchers for state changes
	watch(currentPage, () => {
		fetchLogs();
	});

	watch(searchTerm, () => {
		debouncedFilter();
	});

	watch(perPage, (newVal) => {
		checkLoadMoreMode(newVal);
		pageSettings.set("useSystemLogs", "perPage", newVal);
		fetchLogs();
	});

	function clearFilters() {
		filters.value = {};
		filterSeverity.value = [];
		searchTerm.value = "";
		fetchLogs();
	}

	return {
		checkLoadMoreMode,
		clearFilters,
		currentPage,
		deleteLog,
		deleteManyLogs,
		fetchLogs,
		filters,
		filterSeverity,
		formatters,
		hasMoreData,
		isLoading,
		isLoadingMore,
		isLoadMoreMode,
		lastPage,
		loadMoreLogs,
		logs,
		perPage,
		reload,
		searchTerm,
		showConfirmDelete,
		sortParam,
		toggleSort,
	};
}
