import axios from "axios";
import { ref, watch } from "vue";
import { useDebounceFn } from "@vueuse/core";
import { useDialogStore } from "@/stores/dialogActions";
import { useToaster } from "@/composables/useToaster";
import { eventBus } from "@/composables/eventBus";
import { useReload } from "@/composables/tables/useReload";
import { useToggleSort } from "@/composables/tables/useToggleSort";
import { usePageSettingsStore } from "@/stores/pageSettings";

// --- Composable: useTags ---
export function useTags() {
	// --- State ---
	const currentPage = ref(1);
	const pageSettings = usePageSettingsStore();
	const perPage = ref(pageSettings.get("useTags", "perPage", 10));
	const sortParam = ref("-id");
	const searchTerm = ref("");
	const filters = ref({});
	const tags = ref([]);
	const isLoading = ref(false);
	const lastPage = ref(1);
	const editId = ref(0);
	const newTagModalKey = ref(1);
	const newRoleModalKey = ref(1);
	const showConfirmDelete = ref(false);

	// --- Stores & Utilities ---
	const dialogStore = useDialogStore();
	const { openDialog } = dialogStore;
	const { toastSuccess, toastError } = useToaster();
	const { reload } = useReload(fetchTags);
	const { toggleSort } = useToggleSort(sortParam, fetchTags, "useTags");

	// --- Fetch Tags ---
	async function fetchTags(params = {}) {
		isLoading.value = true;
		try {
			const response = await axios.get("/api/tags", {
				params: {
					page: currentPage.value,
					perPage: perPage.value,
					sort: sortParam.value,
					...filters.value,
				},
			});
			tags.value = response.data;
			lastPage.value = response.data.last_page;
		} catch (error) {
			console.error("Error fetching tags:", error);
			toastError("Error", "Failed to fetch tags.");
		} finally {
			isLoading.value = false;
		}
	}

	// --- Create Tag Dialog ---
	const createTag = async () => {
		editId.value = 0;
		newTagModalKey.value = Math.random(); // Force re-render of the dialog component
		openDialog("DialogNewTag");
	};

	// --- Update Tag Dialog ---
	function updateTag(id) {
		editId.value = id;
		newTagModalKey.value = Math.random(); // Force re-render of the dialog component
		openDialog("DialogNewTag");
	}

	const handleRoleAssignment = (id) => {
		editId.value = id;
		newRoleModalKey.value = Math.random();
		openDialog("DialogRoleAssignment");
	};

	// --- Delete Tag ---
	const deleteTag = async (id) => {
		try {
			await axios.delete(`/api/tags/${id}`);
			fetchTags();
			toastSuccess("Tag Deleted", "The tag has been deleted successfully.");
			showConfirmDelete.value = false;
		} catch (error) {
			if (error.status === 422) {
				toastError("Error", error.response.data.message);
			} else {
				console.error("Error deleting Tag:", error);
				toastError("Error", "Failed to delete Tag.");
			}
		}
	};

	// --- Delete Many Tags ---
	const deleteManyTags = async (ids) => {
		try {
			await axios.post("/api/tags/delete-many", { ids });
			fetchTags();
			toastSuccess("Tags Deleted", "The tags have been deleted successfully.");
			showConfirmDelete.value = false;
			eventBus.emit("deleteManyTagsSuccess");
		} catch (error) {
			if (error.status === 422) {
				toastError("Error", error.response.data.message);
			} else {
				console.error("Error deleting Tags:", error);
				toastError("Error", "Failed to delete Tags.");
			}
			showConfirmDelete.value = false;
		}
	};

	// --- View/Edit Dialog ---
	const viewEditDialog = (id) => {
		editId.value = id;
		newTagModalKey.value = Math.random();
		openDialog("DialogNewTag");
	};

	// --- Handle Save (refresh and re-render) ---
	const handleSave = () => {
		fetchTags();
		newTagModalKey.value = Math.random();
	};

	// --- Keyboard Shortcut ---
	function handleKeyDown(event) {
		if (event.altKey && event.key === "n") {
			event.preventDefault();
			openDialog("DialogNewTag");
		}
	}

	// --- Debounced Filter ---
	const debouncedFilter = useDebounceFn(() => {
		filters.value[`filter[q]`] = searchTerm.value;
		currentPage.value = 1;
		fetchTags();
	}, 500);

	// --- Watchers ---
	watch(currentPage, () => {
		fetchTags();
	});

	watch(searchTerm, () => {
		debouncedFilter();
	});

	watch(perPage, (newVal) => {
		pageSettings.set("useTags", "perPage", newVal);
		fetchTags();
	});

	// --- Return ---
	return {
		// State
		reload,
		tags,
		isLoading,
		currentPage,
		perPage,
		lastPage,
		editId,
		newTagModalKey,
		newRoleModalKey,
		searchTerm,
		showConfirmDelete,

		// Dialogs & Actions
		openDialog,
		fetchTags,
		createTag,
		updateTag,
		deleteTag,
		deleteManyTags,
		handleSave,
		handleRoleAssignment,
		handleKeyDown,
		viewEditDialog,
		toggleSort,
		sortParam,
	};
}
