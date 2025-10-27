import { ref, onMounted, computed } from "vue";
import { useToaster } from "@/composables/useToaster";
import { useDialogStore } from "@/stores/dialogActions";

export function useRoles(props, emit) {
	const isLoading = ref(false);
	const roles = ref([]);
	const selectedRoles = ref([]);
	const errors = ref([]);
	const { toastSuccess, toastError } = useToaster();
	const dialogStore = useDialogStore();
	const { closeDialog, isDialogOpen } = dialogStore;
	const typeMapping = {
		1: "device",
		2: "tag",
		3: "snippet",
		4: "agent",
	};

	onMounted(() => {
		fetchInitialData();
	});

	const displayName = computed(() => {
		console.log(typeMapping[props.type]);
		return typeMapping[props.type] || "Item";
	});

	const fetchInitialData = async () => {
		isLoading.value = true;
		await getRoles();

		switch (props.type) {
			case 1: // device
				await getDevice();
				break;
			case 2: // tag
				await getTag();
				break;
			case 3: // snippet
				await getSnippet();
				break;
			case 4: // agent
				await getAgent();
				break;
			default:
				selectedRoles.value = [];
		}

		isLoading.value = false;
	};

	const getRoles = async () => {
		try {
			const response = await axios.get("/api/roles");
			roles.value = response.data.data;
		} catch (error) {
			errors.value = error.response.data.errors;
			toastError("Error", "Failed to fetch roles", error.response.data.message);
			console.error("Error fetching roles:", error);
		}
	};

	const getDevice = async () => {
		try {
			const response = await axios.get(`/api/devices/${props.itemId}`);
			if (response.data.roles.length > 0) {
				selectedRoles.value = response.data.roles.map((role) => role.id);
			}
		} catch (error) {
			errors.value = error.response.data.errors;
			toastError("Error", "Failed to fetch device roles", error.response.data.message);
		}
	};

	const getTag = async () => {
		try {
			const response = await axios.get(`/api/tags/${props.itemId}`);
			if (response.data.roles.length > 0) {
				selectedRoles.value = response.data.roles.map((role) => role.id);
			}
		} catch (error) {
			errors.value = error.response.data.errors;
			toastError("Error", "Failed to fetch tag roles", error.response.data.message);
		}
	};

	const getSnippet = async () => {
		try {
			const response = await axios.get(`/api/snippets/${props.itemId}`);
			if (response.data.roles.length > 0) {
				selectedRoles.value = response.data.roles.map((role) => role.id);
			}
		} catch (error) {
			errors.value = error.response.data.errors;
			toastError("Error", "Failed to fetch snippet roles", error.response.data.message);
		}
	};

	const getAgent = async () => {
		try {
			const response = await axios.get(`/api/agents/${props.itemId}`);
			if (response.data.roles.length > 0) {
				selectedRoles.value = response.data.roles.map((role) => role.id);
			}
		} catch (error) {
			errors.value = error.response.data.errors;
			toastError("Error", "Failed to fetch agent roles", error.response.data.message);
		}
	};

	function updateRoles() {
		switch (props.type) {
			case 1: // device
				updateDeviceRoles();
				break;
			case 2: // tag
				updateTagRoles();
				break;
			case 3: // snippet
				updateSnippetRoles();
				break;
			case 4: // agent
				updateAgentRoles();
				break;
			default:
				selectedRoles.value = [];
		}
		setTimeout(() => {
			// wait for the DB to update
			emit("updateRoles", selectedRoles.value);
		}, 300);
	}

	const updateDeviceRoles = async () => {
		if (selectedRoles.value.length === 0) {
			toastError("Error", "Please select at least one role");
			return false;
		}

		try {
			await axios.post(`/api/device/update-roles/${props.itemId}`, {
				roles: selectedRoles.value,
			});
			toastSuccess("Success", "Roles assigned successfully");
			close();
			return true;
		} catch (error) {
			errors.value = error.response.data.errors;
			toastError("Error", "Failed to update roles", error.response.data.message);
			return false;
		}
	};

	const updateTagRoles = async () => {
		if (selectedRoles.value.length === 0) {
			toastError("Error", "Please select at least one role");
			return false;
		}
		try {
			await axios.post(`/api/tags/update-roles/${props.itemId}`, {
				roles: selectedRoles.value,
			});
			toastSuccess("Success", "Roles assigned successfully");
			close();
			return true;
		} catch (error) {
			errors.value = error.response.data.errors;
			toastError("Error", "Failed to update roles", error.response.data.message);
			return false;
		}
	};

	const updateSnippetRoles = async () => {
		if (selectedRoles.value.length === 0) {
			toastError("Error", "Please select at least one role");
			return false;
		}
		try {
			await axios.post(`/api/snippets/update-roles/${props.itemId}`, {
				roles: selectedRoles.value,
			});
			toastSuccess("Success", "Roles assigned successfully");
			close();
			return true;
		} catch (error) {
			errors.value = error.response.data.errors;
			toastError("Error", "Failed to update roles", error.response.data.message);
			return false;
		}
	};

	const updateAgentRoles = async () => {
		if (selectedRoles.value.length === 0) {
			toastError("Error", "Please select at least one role");
			return false;
		}
		try {
			await axios.post(`/api/agents/update-roles/${props.itemId}`, {
				roles: selectedRoles.value,
			});
			toastSuccess("Success", "Roles assigned successfully");
			close();
			return true;
		} catch (error) {
			errors.value = error.response.data.errors;
			toastError("Error", "Failed to update roles", error.response.data.message);
			return false;
		}
	};

	const isRoleSelected = (roleId) => {
		return selectedRoles.value.includes(roleId);
	};

	const toggleRole = (roleId) => {
		if (roleId === 1) return; // aadmin role cannot be changed
		const index = selectedRoles.value.indexOf(roleId);
		if (index > -1) {
			selectedRoles.value.splice(index, 1);
		} else {
			selectedRoles.value.push(roleId);
		}
	};

	function close() {
		closeDialog("DialogRoleAssignment");
	}

	return {
		displayName,
		isLoading,
		roles,
		updateRoles,
		isDialogOpen,
		isRoleSelected,
		toggleRole,
		close,
	};
}
