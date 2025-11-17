import axios from "axios";
import { ref, watch, onMounted, onUnmounted } from "vue";
import { useDebounceFn } from "@vueuse/core";
import { useDialogStore } from "@/stores/dialogActions";
import { useToaster } from "@/composables/useToaster";
import { eventBus } from "@/composables/eventBus";
import { useReload } from "@/composables/tables/useReload";
import { useToggleSort } from "@/composables/tables/useToggleSort";
import { usePageSettingsStore } from "@/stores/pageSettings";

// --- Composable: useConfigReportsTable ---
export function useConfigReportsTable() {
	// --- State ---
	const currentPage = ref(1);
	const pageSettings = usePageSettingsStore();
	const perPage = ref(pageSettings.get("useConfigReportsTable", "perPage", 10));
	const sortParam = ref("-id");
	const searchTerm = ref("");
	const filters = ref({});
	const reports = ref([]);
	const isLoading = ref(false);
	const lastPage = ref(1);
	const totalRecords = ref(0); // Total records for pagination
	const editId = ref(0);
	const newReportModalKey = ref(1);
	const showConfirmDelete = ref(false);

	// --- Modal State ---
	const isReportViewModalOpen = ref(false);
	const currentReportId = ref(null);

	// --- Stores & Utilities ---
	const dialogStore = useDialogStore();
	const { openDialog } = dialogStore;
	const { toastSuccess, toastError } = useToaster();
	const { reload } = useReload(fetchReports);
	const { toggleSort } = useToggleSort(sortParam, fetchReports, "useConfigReportsTable");

	// --- Fetch Reports ---
	async function fetchReports(params = {}) {
		isLoading.value = true;
		try {
			const response = await axios.get("/api/reports", {
				params: {
					page: currentPage.value,
					perPage: perPage.value,
					sort: sortParam.value,
					...filters.value,
				},
			});
			reports.value = response.data;
			lastPage.value = response.data.last_page;
			totalRecords.value = response.data.total; // Update total records for pagination
		} catch (error) {
			console.error("Error fetching reports:", error);
			toastError("Error", "Failed to fetch reports.");
		} finally {
			isLoading.value = false;
		}
	}

	// --- Report Viewer Modal ---
	function openReportViewModal(reportId) {
		currentReportId.value = reportId;
		isReportViewModalOpen.value = true;
	}

	// --- Create Report Dialog ---
	const createReport = async () => {
		editId.value = 0;
		newReportModalKey.value = Math.random(); // Force re-render of the dialog component
		openDialog("DialogNewReport");
	};

	// --- Update Report Dialog ---
	function updateReport(id) {
		editId.value = id;
		newReportModalKey.value = Math.random(); // Force re-render of the dialog component
		openDialog("DialogNewReport");
	}

	// --- Delete Report ---
	const deleteReport = async (id) => {
		try {
			await axios.delete(`/api/reports/${id}`);
			fetchReports();
			toastSuccess("Report Deleted", "The report has been deleted successfully.");
			showConfirmDelete.value = false;
		} catch (error) {
			if (error.status === 422) {
				toastError("Error", error.response.data.message);
			} else {
				console.error("Error deleting Report:", error);
				toastError("Error", "Failed to delete Report.");
			}
		}
	};

	// --- Delete Many Reports ---
	const deleteManyReports = async (ids) => {
		try {
			await axios.post("/api/reports/delete-many", { ids });
			fetchReports();
			toastSuccess("Reports Deleted", "The reports have been deleted successfully.");
			showConfirmDelete.value = false;
			eventBus.emit("deleteManyReportsSuccess");
		} catch (error) {
			if (error.status === 422) {
				toastError("Error", error.response.data.message);
			} else {
				console.error("Error deleting Reports:", error);
				toastError("Error", "Failed to delete Reports.");
			}
			showConfirmDelete.value = false;
		}
	};

	// --- View/Edit Dialog ---
	const viewEditDialog = (id) => {
		editId.value = id;
		newReportModalKey.value = Math.random();
		openDialog("DialogNewReport");
	};

	// --- Handle Save (refresh and re-render) ---
	const handleSave = () => {
		fetchReports();
		newReportModalKey.value = Math.random();
	};

	// --- Keyboard Shortcut ---
	function handleKeyDown(event) {
		if (event.altKey && event.key === "n") {
			event.preventDefault();
			openDialog("DialogNewReport");
		}
	}

	// --- Debounced Filter ---
	const debouncedFilter = useDebounceFn(() => {
		filters.value[`filter[q]`] = searchTerm.value;
		currentPage.value = 1;
		fetchReports();
	}, 500);

	// --- Watchers ---
	watch(currentPage, () => {
		fetchReports();
	});

	watch(searchTerm, () => {
		debouncedFilter();
	});

	watch(perPage, () => {
		pageSettings.set("useConfigReportsTable", "perPage", perPage.value);
		fetchReports();
	});

	// --- Lifecycle ---
	onMounted(() => {
		fetchReports();
		window.addEventListener("keydown", handleKeyDown);
	});

	onUnmounted(() => {
		window.removeEventListener("keydown", handleKeyDown);
	});

	// --- Return ---
	return {
		// State
		reports,
		isLoading,
		currentPage,
		perPage,
		lastPage,
		totalRecords,
		searchTerm,
		showConfirmDelete,
		reload,
		sortParam,

		// Modal State
		isReportViewModalOpen,
		currentReportId,

		// Dialogs & Actions
		deleteReport,
		deleteManyReports,
		viewEditDialog,
		toggleSort,
		openReportViewModal,
	};
}
