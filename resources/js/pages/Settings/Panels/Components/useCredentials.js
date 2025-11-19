import axios from "axios";
import { eventBus } from "@/composables/eventBus";
import { ref, onMounted, watch, inject } from "vue";
import { useDebounceFn } from "@vueuse/core";
import { useToaster } from "@/composables/useToaster";
import { useDialogStore } from "@/stores/dialogActions";
import { useReload } from "@/composables/tables/useReload";
import { useToggleSort } from "@/composables/tables/useToggleSort";
import { usePageSettingsStore } from "@/stores/pageSettings";
import { useRouter } from "vue-router";

export function useCredentials(options = {}) {
	const { autoMount = true, skipInitialization = false } = options;

	const { toastSuccess, toastError } = useToaster();

	const editId = ref(0);
	const newCredModalKey = ref(1);
	const currentPage = ref(1);
	const filters = ref({});
	const isLoading = ref(false);
	const lastPage = ref(1);
	const creds = ref({});
	const pageSettings = usePageSettingsStore();
	const perPage = ref(pageSettings.get("useCredentials", "perPage", 10));
	const searchTerm = ref("");
	const showConfirmDelete = ref(false);
	const sortParam = ref("-id");
	const formatters = inject("formatters");
	const dialogStore = useDialogStore();
	const { toggleSort } = useToggleSort(sortParam, fetchCreds, "useCredentials");
	const { reload } = useReload(fetchCreds);
	const router = useRouter();

	if (autoMount && !skipInitialization) {
		onMounted(() => {
			fetchCreds();
		});
	}

	async function fetchCreds(params = {}) {
		isLoading.value = true;
		try {
			const response = await axios.get("/api/settings/credentials", {
				params: {
					page: currentPage.value,
					perPage: perPage.value,
					sort: sortParam.value,
					...filters.value,
				},
			});
			creds.value = response.data;

			if (router.currentRoute.value.params?.id) {
				editCred(creds.value.data.find((cred) => cred.id === parseInt(router.currentRoute.value.params.id)));
			}

			lastPage.value = response.data.last_page;
		} catch (error) {
			console.error("Error fetching creds:", error);
			toastError("Error", "Failed to fetch creds.");
		} finally {
			isLoading.value = false;
		}
	}

	// Create Cred
	const createCred = () => {
		editId.value = 0;
		newCredModalKey.value = Math.floor(Math.random() * 1000);
		dialogStore.openDialog("DialogNewCred");
	};

	// Edit Cred
	const editCred = (row) => {
		editId.value = row.id;
		newCredModalKey.value = Math.floor(Math.random() * 1000);
		dialogStore.openDialog("DialogNewCred");
	};

	const handleSave = () => {
		fetchCreds(); // Fetch the updated tags after saving
		newCredModalKey.value = Math.floor(Math.random() * 1000);
	};

	// Delete Credential
	const deleteCredential = async (id) => {
		try {
			await axios.delete(`/api/settings/credentials/${id}`);
			fetchCreds(); // Refresh creds list after deletion
			toastSuccess("Credential Deleted", "The credential has been deleted successfully.");
			showConfirmDelete.value = false;
		} catch (error) {
			if (error.status === 422) {
				toastError("Error", error.response.data.message);
			} else {
				console.error("Error deleting Credential:", error);
				toastError("Error", "Failed to delete Credential.");
			}
		}
	};

	// Delete Many Creds
	const deleteManyCredentials = async (ids) => {
		try {
			await axios.post("/api/settings/credentials/delete-many", { ids });
			fetchCreds(); // Refresh creds list after deletion
			toastSuccess("Credentials Deleted", "The creds have been deleted successfully.");
			showConfirmDelete.value = false;
			eventBus.emit("deleteManyCredentialsSuccess");
		} catch (error) {
			if (error.status === 422) {
				toastError("Error", error.response.data.message);
			} else {
				console.error("Error deleting Credentials:", error);
				toastError("Error", "Failed to delete Credentials.");
			}
			showConfirmDelete.value = false;
		}
	};

	const debouncedFilter = useDebounceFn(() => {
		filters.value[`filter[cred_name]`] = searchTerm.value;
		currentPage.value = 1;
		fetchCreds();
	}, 500);

	// Watchers for state changes
	watch(currentPage, () => {
		fetchCreds();
	});

	watch(searchTerm, () => {
		debouncedFilter();
	});

	watch(perPage, (newVal) => {
		pageSettings.set("useCredentials", "perPage", newVal);
		fetchCreds();
	});

	return {
		// --- Methods / Actions ---
		reload,
		fetchCreds,
		createCred,
		editCred,
		deleteCredential,
		deleteManyCredentials,
		handleSave,
		toggleSort,

		// --- State / Data ---
		creds,
		currentPage,
		lastPage,
		perPage,
		searchTerm,
		sortParam,
		isLoading,
		editId,
		newCredModalKey,
		showConfirmDelete,
		formatters,
		dialogStore,

		// --- External Utilities ---
		pageSettings,
		router,
	};
}
