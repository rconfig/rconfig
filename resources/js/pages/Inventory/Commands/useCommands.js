import axios from "axios";
import { ref, watch } from "vue";
import { useDebounceFn } from "@vueuse/core";
import { useDialogStore } from "@/stores/dialogActions";
import { useToaster } from "@/composables/useToaster"; // Import the composable
import { eventBus } from "@/composables/eventBus";
import { useReload } from "@/composables/tables/useReload";
import { useToggleSort } from "@/composables/tables/useToggleSort";
import { usePageSettingsStore } from "@/stores/pageSettings";
import { useRouter } from "vue-router";

export function useCommands() {
	const currentPage = ref(1);
	const pageSettings = usePageSettingsStore();
	const perPage = ref(pageSettings.get("useCommands", "perPage", 10));
	const sortParam = ref("-id");
	const searchTerm = ref("");
	const filters = ref({});
	const dialogStore = useDialogStore();
	const editId = ref(0);
	const isLoading = ref(false);
	const lastPage = ref(1);
	const newCommandModalKey = ref(1);
	const newBulkUpdateCommandsKey = ref(1); // Key for re-rendering the bulk update dialog
	const commands = ref([]);
	const showConfirmDelete = ref(false);
	const { openDialog, closeDialog } = dialogStore;
	const { toastSuccess, toastError } = useToaster(); // Using toaster for notifications
	const { reload } = useReload(fetchCommands);
	const { toggleSort } = useToggleSort(sortParam, fetchCommands, "useCommands");
	const router = useRouter();

	// Fetch Commands
	async function fetchCommands(params = {}) {
		isLoading.value = true;
		try {
			const response = await axios.get("/api/commands", {
				params: {
					page: currentPage.value,
					perPage: perPage.value,
					sort: sortParam.value,
					...filters.value,
				},
			});
			commands.value = response.data;
			lastPage.value = response.data.last_page;
		} catch (error) {
			console.error("Error fetching commands:", error);
			toastError("Error", "Failed to fetch commands.");
		} finally {
			isLoading.value = false;
		}
	}

	// Create Command
	const createCommand = (async) => {
		editId.value = 0;
		newCommandModalKey.value = Math.random(); // Force re-render of the dialog component
		openDialog("DialogNewCommand");
	};

	function updateCommand(id) {
		editId.value = id;
		newCommandModalKey.value = Math.random(); // Force re-render of the dialog component
		openDialog("DialogNewCommand");
	}

	// Delete Command
	const deleteCommand = async (id) => {
		try {
			await axios.delete(`/api/commands/${id}`);
			fetchCommands(); // Refresh commands list after deletion
			toastSuccess("Command Deleted", "The command has been deleted successfully.");
			showConfirmDelete.value = false;
		} catch (error) {
			if (error.status === 422) {
				toastError("Error", error.response.data.message);
			} else {
				console.error("Error deleting Command Group:", error);
				toastError("Error", "Failed to delete Command Group.");
			}
		}
	};

	// Delete Many Commands
	const deleteManyCommands = async (ids) => {
		try {
			await axios.post("/api/commands/delete-many", { ids });
			fetchCommands(); // Refresh commands list after deletion
			toastSuccess("Commands Deleted", "The commands have been deleted successfully.");
			showConfirmDelete.value = false;
			eventBus.emit("deleteManyCommandsSuccess");
		} catch (error) {
			if (error.status === 422) {
				toastError("Error", error.response.data.message);
			} else {
				console.error("Error deleting Command Group:", error);
				toastError("Error", "Failed to delete Command Group.");
			}
		}
	};

	const viewEditDialog = (id) => {
		editId.value = id;
		newCommandModalKey.value = Math.random(); // Force re-render of the dialog component
		openDialog("DialogNewCommand");
	};

	const openBulkUpdateDialog = () => {
		newBulkUpdateCommandsKey.value = Math.random(); // Force re-render of the bulk update dialog component
		openDialog("DialogBulkUpdateCommands");
	};

	const handleCloseBulkUpdate = () => {
		newBulkUpdateCommandsKey.value = Math.random(); // Force re-render of the bulk update
		closeDialog("DialogBulkUpdateCommands");
	};

	const handleUpdateBulkCommands = () => {
		fetchCommands(); // Reload the commands after bulk update
		newBulkUpdateCommandsKey.value = Math.random(); // Force re-render of the bulk update dialog component
		closeDialog("DialogBulkUpdateCommands");
		toastSuccess("Bulk Update", "Commands updated successfully.");
	};

	// Re-render Dialog
	const handleSave = () => {
		fetchCommands(); // Fetch the updated commands after saving
		newCommandModalKey.value = Math.random(); // Force re-render of the dialog component
	};

	function handleKeyDown(event) {
		if (event.altKey && event.key === "n") {
			event.preventDefault(); // Prevent default behavior (e.g., opening a new window in some browsers)
			openDialog("DialogNewCommand");
		}
	}

	const debouncedFilter = useDebounceFn(() => {
		filters.value[`filter[q]`] = searchTerm.value;
		currentPage.value = 1;
		fetchCommands();
	}, 500);

	// Watchers for state changes
	watch(currentPage, () => {
		fetchCommands();
	});

	useCommands;

	watch(searchTerm, () => {
		debouncedFilter();
	});

	watch(perPage, (newVal) => {
		pageSettings.set("useCommands", "perPage", perPage.value);
		fetchCommands();
	});

	function navToConfigProps() {
		router.push({ name: "device-configprops" });
	}

	function navToCompareOptions() {
		router.push({ name: "command-compare-options" });
	}

	function updateCICEnabled(row, id, currentValue) {
		const newValue = !currentValue;
		row.eoc_enabled = newValue; // ✅ directly mutates row object

		axios
			.post(`/api/commands/set-eoc-state/${id}`, { state: newValue })
			.then(() => toastSuccess("Success", "CIC check updated"))
			.catch((error) => {
				row.eoc_enabled = currentValue; // ✅ revert if error
				console.log(error);
				toastError("Error", error.response?.data?.message || "Failed to update CIC");
			});
	}

	function updateChangeNotification(row, id, currentValue) {
		const newValue = !currentValue;
		row.change_notification = newValue; // ✅ optimistic update

		axios
			.post(`/api/commands/toggle-change-notification/${id}`, { change_notification: newValue })
			.then(() => {
				toastSuccess("Success", "Change notification setting updated");
			})
			.catch((error) => {
				row.change_notification = currentValue; // ✅ revert if error
				console.log(error);
				toastError("Error", error.response?.data?.message || "Failed to update change notification");
			});
	}

	function updateSaveConfig(row, id, currentValue) {
		const newValue = !currentValue;
		row.save_config = newValue; // ✅ optimistic update

		axios
			.post(`/api/commands/set-save_config-state/${id}`, { state: newValue })
			.then(() => {
				toastSuccess("Success", "Save config setting updated");
			})
			.catch((error) => {
				row.save_config = currentValue; // ✅ revert if error
				console.log(error);
				toastError("Error", error.response?.data?.message || "Failed to update save config");
			});
	}

	return {
		// state
		commands,
		currentPage,
		editId,
		isLoading,
		lastPage,
		newCommandModalKey,
		newBulkUpdateCommandsKey,
		perPage,
		searchTerm,
		showConfirmDelete,
		sortParam,

		// methods
		createCommand,
		deleteCommand,
		deleteManyCommands,
		fetchCommands,
		handleKeyDown,
		handleSave,
		navToConfigProps,
		navToCompareOptions,
		openDialog,
		reload,
		toggleSort,
		updateCommand,
		viewEditDialog,
		openBulkUpdateDialog,
		handleCloseBulkUpdate,
		handleUpdateBulkCommands,
		updateCICEnabled,
		updateChangeNotification,
		updateSaveConfig,
	};
}
