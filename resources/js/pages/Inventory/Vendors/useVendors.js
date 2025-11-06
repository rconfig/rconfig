import axios from "axios";
import { ref, watch } from "vue";
import { useDebounceFn } from "@vueuse/core";
import { useDialogStore } from "@/stores/dialogActions";
import { useToaster } from "@/composables/useToaster"; // Import the composable
import { eventBus } from "@/composables/eventBus";
import { useReload } from "@/composables/tables/useReload";
import { useToggleSort } from "@/composables/tables/useToggleSort";
import { usePageSettingsStore } from "@/stores/pageSettings";

export function useVendors() {
	const currentPage = ref(1);
	const pageSettings = usePageSettingsStore();
	const perPage = ref(pageSettings.get("useVendors", "perPage", 10));
	const sortParam = ref("-id");
	const searchTerm = ref("");
	const filters = ref({});
	const dialogStore = useDialogStore();
	const editId = ref(0);
	const isLoading = ref(false);
	const lastPage = ref(1);
	const newVendorModalKey = ref(1);
	const vendors = ref([]);
	const showConfirmDelete = ref(false);
	const { openDialog } = dialogStore;
	const { toastSuccess, toastError } = useToaster(); // Using toaster for notifications
	const { reload } = useReload(fetchVendors);
	const { toggleSort } = useToggleSort(sortParam, fetchVendors, "useVendors");

	// Fetch Vendors
	async function fetchVendors(params = {}) {
		isLoading.value = true;
		try {
			const response = await axios.get("/api/vendors", {
				params: {
					page: currentPage.value,
					perPage: perPage.value,
					sort: sortParam.value,
					...filters.value,
				},
			});
			vendors.value = response.data;
			lastPage.value = response.data.last_page;
		} catch (error) {
			console.error("Error fetching vendors:", error);
			toastError("Error", "Failed to fetch vendors.");
		} finally {
			isLoading.value = false;
		}
	}

	// Create Vendor
	const createVendor = (async) => {
		editId.value = 0;
		newVendorModalKey.value = Math.random(); // Force re-render of the dialog component
		openDialog("DialogNewVendor");
	};

	function updateVendor(id) {
		editId.value = id;
		newVendorModalKey.value = Math.random(); // Force re-render of the dialog component
		openDialog("DialogNewVendor");
	}

	// Delete Vendor
	const deleteVendor = async (id) => {
		try {
			await axios.delete(`/api/vendors/${id}`);
			fetchVendors(); // Refresh vendors list after deletion
			toastSuccess("Vendor Deleted", "The vendor has been deleted successfully.");
		} catch (error) {
			if (error.status === 422) {
				toastError("Error", error.response.data.message);
			} else {
				console.error("Error deleting Command:", error);
				toastError("Error", "Failed to delete Command.");
			}
		}
	};

	// Delete Many Vendors
	const deleteManyVendors = async (ids) => {
		try {
			await axios.post("/api/vendors/delete-many", { ids });
			fetchVendors(); // Refresh vendors list after deletion
			toastSuccess("Vendors Deleted", "The vendors have been deleted successfully.");
			showConfirmDelete.value = false;

			eventBus.emit("deleteManyVendorsSuccess");
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

	const viewEditDialog = (id) => {
		editId.value = id;
		newVendorModalKey.value = Math.random(); // Force re-render of the dialog component
		openDialog("DialogNewVendor");
	};

	// Re-render Dialog
	const handleSave = () => {
		fetchVendors(); // Fetch the updated vendors after saving
		newVendorModalKey.value = Math.random(); // Force re-render of the dialog component
	};

	function handleKeyDown(event) {
		if (event.altKey && event.key === "n") {
			event.preventDefault(); // Prevent default behavior (e.g., opening a new window in some browsers)
			openDialog("DialogNewVendor");
		}
	}

	const debouncedFilter = useDebounceFn(() => {
		filters.value[`filter[q]`] = searchTerm.value;
		currentPage.value = 1;
		fetchVendors();
	}, 500);

	// Watchers for state changes
	watch(currentPage, () => {
		fetchVendors();
	});

	watch(searchTerm, () => {
		debouncedFilter();
	});

	watch(perPage, (newVal) => {
		pageSettings.set("useVendors", "perPage", newVal);
		fetchVendors();
	});

	return {
		reload,
		vendors,
		isLoading,
		currentPage,
		perPage,
		lastPage,
		editId,
		newVendorModalKey,
		searchTerm,
		openDialog,
		fetchVendors,
		createVendor,
		updateVendor,
		deleteVendor,
		deleteManyVendors,
		handleSave,
		handleKeyDown,
		viewEditDialog,
		toggleSort,
		sortParam,
		showConfirmDelete,
	};
}
