import axios from "axios";
import { eventBus } from "@/composables/eventBus";
import { ref, watch } from "vue";
import { useDebounceFn } from "@vueuse/core";
import { useDialogStore } from "@/stores/dialogActions";
import { usePageSettingsStore } from "@/stores/pageSettings";
import { useReload } from "@/composables/tables/useReload";
import { useRouter } from "vue-router";
import { useToaster } from "@/composables/useToaster";
import { useToggleSort } from "@/composables/tables/useToggleSort";

export function useUsers() {
	const currentPage = ref(1);
	const pageSettings = usePageSettingsStore();
	const perPage = ref(pageSettings.get("useUsers", "perPage", 10));
	const sortParam = ref("-id");
	const searchTerm = ref("");
	const filters = ref({});
	const dialogStore = useDialogStore();
	const editId = ref(0);
	const isLoading = ref(false);
	const lastPage = ref(1);
	const newUserModalKey = ref(1);
	const users = ref([]);
	const showConfirmDelete = ref(false);
	const { openDialog } = dialogStore;
	const { toastSuccess, toastError } = useToaster();
	const router = useRouter();
	const { reload } = useReload(fetchUsers);
	const { toggleSort } = useToggleSort(sortParam, fetchUsers, "useUsers");

	const relatedDocs = ref([]);

	// Fetch Users
	async function fetchUsers(params = {}) {
		isLoading.value = true;
		try {
			const response = await axios.get("/api/users", {
				params: {
					page: currentPage.value,
					perPage: perPage.value,
					sort: sortParam.value,
					...filters.value,
				},
			});
			users.value = response.data;
			lastPage.value = response.data.last_page;
		} catch (error) {
			console.error("Error fetching users:", error);
			toastError("Error", "Failed to fetch users.");
		} finally {
			isLoading.value = false;
		}
	}

	// Create User
	const createUser = (async) => {
		editId.value = 0;
		newUserModalKey.value = Math.random(); // Force re-render of the dialog component
		openDialog("DialogNewUser");
	};

	function updateUser(id) {
		editId.value = id;
		newUserModalKey.value = Math.random(); // Force re-render of the dialog component
		openDialog("DialogNewUser");
	}

	// Delete User
	const deleteUser = async (id) => {
		try {
			await axios.delete(`/api/users/${id}`);
			fetchUsers(); // Refresh users list after deletion
			toastSuccess("User Deleted", "The user has been deleted successfully.");
		} catch (error) {
			console.error("Error deleting user:", error);
			toastError("Error", "Failed to delete user.");
		}
	};

	const viewEditDialog = (id) => {
		editId.value = id;
		newUserModalKey.value = Math.random(); // Force re-render of the dialog component
		openDialog("DialogNewUser");
	};

	// Re-render Dialog
	const handleSave = () => {
		fetchUsers(); // Fetch the updated users after saving
		newUserModalKey.value = Math.random(); // Force re-render of the dialog component
	};

	function handleKeyDown(event) {
		if (event.altKey && event.key === "n") {
			event.preventDefault(); // Prevent default behavior (e.g., opening a new window in some browsers)
			openDialog("DialogNewUser");
		}
	}

	const debouncedFilter = useDebounceFn(() => {
		filters.value[`filter[q]`] = searchTerm.value;
		currentPage.value = 1;
		fetchUsers();
	}, 500);

	// Watchers for state changes
	watch(currentPage, () => {
		fetchUsers();
	});

	watch(searchTerm, () => {
		debouncedFilter();
	});

	watch(perPage, (newVal) => {
		pageSettings.set("useUsers", "perPage", newVal);
		fetchUsers();
	});

	// Function for notification toggle
	const toggleNotification = async (id, value) => {
		try {
			await axios.post(`/api/user/set-notification-status/${id}`, { status: value });
			toastSuccess("Notification settings updated", "User notification preferences have been saved.");

			// Refresh data or update the specific user in the table
			await fetchUsers();
		} catch (error) {
			toastError("Error updating notification settings", error.response?.data?.message || "An unexpected error occurred");
		}
	};

	const toggleSocialiteApproved = async (id, value) => {
		// Optimistically update the UI
		const user = users.value.data.find((u) => u.id === id);
		if (user) {
			user.is_socialite_approved = value;
		}

		try {
			await axios.post(`/api/user/set-socialite-approval-status/${id}`, { status: value });
			toastSuccess("SSO approval status updated", "User SSO approval status has been saved.");
		} catch (error) {
			// Revert on error
			if (user) {
				user.is_socialite_approved = value === 1 ? 0 : 1;
			}
			toastError("Error updating SSO approval status", error.response?.data?.message || "An unexpected error occurred");
		}
	};

	const updateRelatedDocs = () => {
		relatedDocs.value = [
			{
				title: "User Management Guide",
				description: "Learn how to create, edit, and manage users in rConfig",
				link: "https://docs.rconfig.com/settings/users/",
				icon: "external-link",
			},
		];
	};

	// Function to navigate to users activity log
	const navigateToActivityLog = () => {
		router.push("/settings/users-activity-log");
	};

	const deleteManyUsers = async (ids) => {
		try {
			await axios.post("/api/users/delete-many", { ids });
			fetchUsers(); // Refresh Users list after deletion
			toastSuccess("Users Deleted", "The users have been deleted successfully.");
			showConfirmDelete.value = false;
			eventBus.emit("deleteManyUsersSuccess");
		} catch (error) {
			if (error.status === 422) {
				toastError("Error", error.response.data.message);
			} else {
				console.error("Error deleting users:", error);
				toastError("Error", "Failed to delete users.");
			}
			showConfirmDelete.value = false;
		}
	};

	return {
		reload,
		users,
		isLoading,
		currentPage,
		perPage,
		lastPage,
		editId,
		newUserModalKey,
		searchTerm,
		openDialog,
		fetchUsers,
		navigateToActivityLog,
		createUser,
		updateUser,
		deleteUser,
		handleSave,
		handleKeyDown,
		viewEditDialog,
		toggleSort,
		sortParam,
		toggleNotification,
		toggleSocialiteApproved,
		relatedDocs,
		showConfirmDelete,
		deleteManyUsers,
		updateRelatedDocs,
	};
}