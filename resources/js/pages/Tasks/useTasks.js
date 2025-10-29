import axios from "axios";
import { ref, watch, inject } from "vue";
import { useDebounceFn } from "@vueuse/core";
import { useDialogStore } from "@/stores/dialogActions";
import { useToaster } from "@/composables/useToaster";
import { eventBus } from "@/composables/eventBus";
import { useReload } from "@/composables/tables/useReload";
import { useToggleSort } from "@/composables/tables/useToggleSort";
import { usePageSettingsStore } from "@/stores/pageSettings";

// --- Composable: useTasks ---
export function useTasks() {
	// --- Data ---
	const tasks = ref([]);

	// --- State ---
	const isLoading = ref(false);
	const showConfirmConfirmProceedAlertAlert = ref(false);
	const proceedEditId = ref(null);
	const showConfirmDelete = ref(false);
	const editId = ref(0);
	const historyTaskId = ref(null);
	const newTaskModalKey = ref(1);

	// --- Pagination ---
	const currentPage = ref(1);
	const pageSettings = usePageSettingsStore();
	const perPage = ref(pageSettings.get("useTasks", "perPage", 10));
	const lastPage = ref(1);
	const sortParam = ref("-id");
	const searchTerm = ref("");
	const filters = ref({});

	// --- Utilities ---
	const dialogStore = useDialogStore();
	const { openDialog } = dialogStore;
	const { toastSuccess, toastError } = useToaster();
	const formatters = inject("formatters");
	const { reload } = useReload(fetchTasks);
	const { toggleSort } = useToggleSort(sortParam, fetchTasks, "useTasks");

	// --- Fetch ---
	async function fetchTasks(params = {}) {
		isLoading.value = true;
		try {
			const response = await axios.get("/api/tasks", {
				params: {
					page: currentPage.value,
					perPage: perPage.value,
					sort: sortParam.value,
					...filters.value,
				},
			});
			tasks.value = response.data;
			lastPage.value = response.data.last_page;
		} catch (error) {
			console.error("Error fetching tasks:", error);
			toastError("Error", "Failed to fetch tasks.");
		} finally {
			isLoading.value = false;
		}
	}

	// --- CRUD and UI Methods ---
	const createTask = (async) => {
		editId.value = 0;
		newTaskModalKey.value = Math.random();
		openDialog("DialogNewTask");
	};

	function updateTask(id) {
		editId.value = id;
		newTaskModalKey.value = Math.random();
		openDialog("DialogNewTask");
	}

	const deleteTask = async (id) => {
		try {
			await axios.delete(`/api/tasks/${id}`);
			fetchTasks();
			toastSuccess("Task Deleted", "The task has been deleted successfully.");
		} catch (error) {
			console.error("Error deleting task:", error);
			toastError("Error", "Failed to delete task.");
		}
	};

	const deleteManyTasks = async (ids) => {
		try {
			await axios.post("/api/tasks/delete-many", { ids });
			fetchTasks();
			toastSuccess("Tasks Deleted", "The tasks have been deleted successfully.");
			showConfirmDelete.value = false;
			eventBus.emit("deleteManyTasksSuccess");
		} catch (error) {
			if (error.status === 422) {
				toastError("Error", error.response.data.message);
			} else {
				console.error("Error deleting Tasks:", error);
				toastError("Error", "Failed to delete Tasks.");
			}
			showConfirmDelete.value = false;
		}
	};

	const viewEditDialog = (id) => {
		editId.value = id;
		newTaskModalKey.value = Math.random();
		openDialog("DialogNewTask");
	};

	const viewHistoryDialog = (id) => {
		historyTaskId.value = id;
		openDialog("DialogViewHistory");
	};

	const handleSave = () => {
		fetchTasks();
		newTaskModalKey.value = Math.random();
	};

	function handleKeyDown(event) {
		if (event.altKey && event.key === "n") {
			event.preventDefault();
			openDialog("DialogNewTask");
		}
	}

	const debouncedFilter = useDebounceFn(() => {
		filters.value[`filter[q]`] = searchTerm.value;
		currentPage.value = 1;
		fetchTasks();
	}, 500);

	function runTaskConfirm(id) {
		showConfirmConfirmProceedAlertAlert.value = true;
		proceedEditId.value = id;
	}

	function runTaskNow(id) {
		if (!id) {
			return;
		}
		axios
			.post("/api/tasks/run-manual-task", {
				id: id,
			})
			.then((response) => {
				showConfirmConfirmProceedAlertAlert.value = false;
				toastSuccess("Task Run", "The task has been run successfully.");
			})
			.catch((error) => {
				console.error("Error running task:", error);
				toastError("Error", "Failed to run task.");
			});
	}

	function pauseTask(id) {
		axios.get(`/api/tasks/toggle-pause-task/${id}`).then(() => {
			fetchTasks();
		});
	}

	// --- Watchers ---
	watch(currentPage, () => {
		fetchTasks();
	});

	watch(searchTerm, () => {
		debouncedFilter();
	});

	watch(perPage, (newVal) => {
		pageSettings.set("useTasks", "perPage", newVal);
		fetchTasks();
	});

	// --- Return ---
	return {
		// Data
		tasks,

		// State
		isLoading,
		showConfirmConfirmProceedAlertAlert,
		proceedEditId,
		showConfirmDelete,
		editId,
		newTaskModalKey,

		// Pagination
		currentPage,
		perPage,
		lastPage,
		sortParam,
		searchTerm,
		filters,

		// Methods - CRUD
		fetchTasks,
		createTask,
		updateTask,
		deleteTask,
		deleteManyTasks,

		// Methods - UI interaction
		openDialog,
		handleSave,
		handleKeyDown,
		runTaskConfirm,
		runTaskNow,
		pauseTask,
		viewEditDialog,
		viewHistoryDialog,
		historyTaskId,
		toggleSort,
		formatters,
		reload,
	};
}
