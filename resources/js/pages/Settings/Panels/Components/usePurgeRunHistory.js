import axios from "axios";
import { eventBus } from "@/composables/eventBus";
import { ref, onMounted, watch, inject } from "vue";
import { useDebounceFn } from "@vueuse/core";
import { useToaster } from "@/composables/useToaster";
import { useReload } from "@/composables/tables/useReload";
import { useToggleSort } from "@/composables/tables/useToggleSort";
import { usePageSettingsStore } from "@/stores/pageSettings";

export function usePurgeRunHistory() {
	const { toastError, toastSuccess } = useToaster();

	const currentPage = ref(1);
	const filters = ref({});
	const isLoading = ref(false);
	const lastPage = ref(1);
	const runs = ref({});
	const pageSettings = usePageSettingsStore();
	const perPage = ref(pageSettings.get("usePurgeRunHistory", "perPage", 10));
	const searchTerm = ref("");
	const statusFilter = ref("all");
	const dryRunFilter = ref("any");
	const showConfirmDelete = ref(false);
	const sortParam = ref("-id");
	const formatters = inject("formatters");

	// Load more state
	const isLoadingMore = ref(false);
	const hasMoreData = ref(true);
	const loadMorePage = ref(1);
	const isLoadMoreMode = ref(false);
	const originalPerPage = ref(null);

	const { reload } = useReload(fetchRuns);
	const { toggleSort } = useToggleSort(sortParam, fetchRuns, "usePurgeRunHistory");

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
		fetchRuns();
	});

	async function fetchRuns(params = {}, append = false) {
		if (append) {
			isLoadingMore.value = true;
		} else {
			isLoading.value = true;
			loadMorePage.value = 1;
			hasMoreData.value = true;
		}

		try {
			let pageToFetch;
			if (isLoadMoreMode.value) {
				pageToFetch = append ? loadMorePage.value + 1 : 1;
			} else {
				pageToFetch = currentPage.value;
			}
			const response = await axios.get("/api/settings/purge-run-history", {
				params: {
					page: pageToFetch,
					perPage: perPage.value,
					sort: sortParam.value,
					loadMore: isLoadMoreMode.value,
					...filters.value,
					...params,
				},
			});

			if (append) {
				runs.value.data = [...runs.value.data, ...response.data.data];
				loadMorePage.value = pageToFetch;
			} else {
				runs.value = response.data;
				loadMorePage.value = 1;
			}

			lastPage.value = response.data.last_page;
			hasMoreData.value = pageToFetch < response.data.last_page;
		} catch (error) {
			console.error("Error fetching purge run history:", error);
			toastError("Error", "Failed to fetch purge run history.");
		} finally {
			isLoading.value = false;
			isLoadingMore.value = false;
		}
	}

	// Load More
	const loadMoreRuns = async () => {
		if (!hasMoreData.value || isLoadingMore.value) return;
		await fetchRuns({}, true);
	};

	// Delete a single run
	const deleteRun = async (id) => {
		try {
			await axios.delete(`/api/settings/purge-run-history/${id}`);
			fetchRuns();
			toastSuccess("Run deleted", "The purge run entry has been deleted.");
			showConfirmDelete.value = false;
		} catch (error) {
			console.error("Error deleting purge run:", error);
			toastError("Error", "Failed to delete purge run.");
			showConfirmDelete.value = false;
		}
	};

	// Delete multiple runs
	const deleteManyRuns = async (ids) => {
		try {
			await axios.post("/api/settings/purge-run-history/delete-many", { ids });
			fetchRuns();
			toastSuccess("Runs deleted", "The selected purge run entries have been deleted.");
			showConfirmDelete.value = false;
			eventBus.emit("deleteManyPurgeRunsSuccess");
		} catch (error) {
			console.error("Error deleting purge runs:", error);
			toastError("Error", "Failed to delete purge runs.");
			showConfirmDelete.value = false;
		}
	};

	const debouncedFilter = useDebounceFn(() => {
		if (searchTerm.value) {
			filters.value[`filter[q]`] = searchTerm.value;
		} else {
			delete filters.value[`filter[q]`];
		}
		currentPage.value = 1;
		fetchRuns();
	}, 400);

	// Watchers for state changes
	watch(currentPage, () => {
		fetchRuns();
	});

	watch(searchTerm, () => {
		debouncedFilter();
	});

	watch(statusFilter, (newVal) => {
		if (newVal && newVal !== "all") {
			filters.value[`filter[status]`] = newVal;
		} else {
			delete filters.value[`filter[status]`];
		}
		fetchRuns();
	});

	watch(dryRunFilter, (newVal) => {
		if (newVal === "any" || newVal === "" || newVal === null || newVal === undefined) {
			delete filters.value[`filter[dry_run]`];
		} else if (newVal === "0" || newVal === "1" || newVal === 0 || newVal === 1) {
			filters.value[`filter[dry_run]`] = Number(newVal);
		} else {
			delete filters.value[`filter[dry_run]`];
		}
		fetchRuns();
	});

	watch(perPage, (newVal) => {
		checkLoadMoreMode(newVal);
		pageSettings.set("usePurgeRunHistory", "perPage", newVal);
		fetchRuns();
	});

	function clearFilters() {
		filters.value = {};
		searchTerm.value = "";
		statusFilter.value = "all";
		dryRunFilter.value = "any";
		fetchRuns();
	}

	return {
		checkLoadMoreMode,
		clearFilters,
		currentPage,
		deleteManyRuns,
		deleteRun,
		dryRunFilter,
		fetchRuns,
		filters,
		formatters,
		hasMoreData,
		isLoading,
		isLoadingMore,
		isLoadMoreMode,
		lastPage,
		loadMoreRuns,
		perPage,
		reload,
		runs,
		searchTerm,
		showConfirmDelete,
		sortParam,
		statusFilter,
		toggleSort,
	};
}
