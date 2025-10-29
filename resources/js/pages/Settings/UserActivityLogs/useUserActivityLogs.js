import axios from "axios";
import { ref, watch, onMounted } from "vue";
import { useDebounceFn } from "@vueuse/core";
import { useDialogStore } from "@/stores/dialogActions";
import { useToaster } from "@/composables/useToaster";
import { useRouter } from "vue-router";
import { eventBus } from "@/composables/eventBus";
import { useReload } from "@/composables/tables/useReload";
import { useToggleSort } from "@/composables/tables/useToggleSort";
import { usePageSettingsStore } from "@/stores/pageSettings";

export function useUserActivityLogs() {
	const currentPage = ref(1);
	const pageSettings = usePageSettingsStore();
	const perPage = ref(pageSettings.get("useUserActivityLogs", "perPage", 10));
	const sortParam = ref("-id");
	const searchTerm = ref("");
	const filters = ref({});
	const dialogStore = useDialogStore();
	const editId = ref(0);
	const isLoading = ref(false);
	const lastPage = ref(1);
	const newUserModalKey = ref(1);
	const logs = ref([]);
	const showConfirmDelete = ref(false);
	const { openDialog } = dialogStore;
	const { toastSuccess, toastError } = useToaster();
	const router = useRouter();
	const { reload } = useReload(fetchLogs);
	const { toggleSort } = useToggleSort(sortParam, fetchLogs, "useUserActivityLogs");

	onMounted(() => {
		fetchLogs();
	});

	// Fetch Users
	async function fetchLogs(params = {}) {
		isLoading.value = true;
		try {
			const response = await axios.get("/api/users-activity-log", {
				params: {
					page: currentPage.value,
					perPage: perPage.value,
					sort: sortParam.value,
					...filters.value,
				},
			});
			logs.value = response.data;
			lastPage.value = response.data.last_page;
		} catch (error) {
			console.error("Error fetching logs:", error);
			toastError("Error", "Failed to fetch logs.");
		} finally {
			isLoading.value = false;
		}
	}

	// Delete User
	const deleteLogs = async (id) => {
		try {
			await axios.delete(`/api/users/${id}`);
			fetchLogs(); // Refresh users list after deletion
			toastSuccess("User Deleted", "The user has been deleted successfully.");
		} catch (error) {
			console.error("Error deleting user:", error);
			toastError("Error", "Failed to delete user.");
		}
	};

	// Delete Many Vendors
	const deleteManyUserLogs = async (ids) => {
		try {
			await axios.post("/api/users-activity-log/delete-many", { ids });
			fetchLogs(); // Refresh Logs list after deletion
			toastSuccess("Logs Deleted", "The Logs have been deleted successfully.");
			showConfirmDelete.value = false;

			eventBus.emit("deleteManyLogsSuccess");
		} catch (error) {
			if (error.status === 422) {
				toastError("Error", error.response.data.message);
			} else {
				console.error("Error deleting Commands:", error);
				toastError("Error", "Failed to delete Commands.");
			}
			showConfirmDelete.value = false;
		}
	};

	const debouncedFilter = useDebounceFn(() => {
		filters.value[`filter[q]`] = searchTerm.value;
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
		pageSettings.set("useUserActivityLogs", "perPage", newVal);
		fetchLogs();
	});

	// Function to navigate to users activity log
	const navigateToUsers = () => {
		router.push("/settings/users");
	};

	return {
		reload,
		logs,
		isLoading,
		currentPage,
		perPage,
		lastPage,
		searchTerm,
		toggleSort,
		sortParam,
		navigateToUsers,
		showConfirmDelete,
		deleteManyUserLogs,
	};
}
