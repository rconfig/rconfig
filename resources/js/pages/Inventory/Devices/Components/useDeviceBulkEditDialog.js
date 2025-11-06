import axios from "axios";
import { useToaster } from "@/composables/useToaster";
import { ref, inject, computed } from "vue";
import { useDialogStore } from "@/stores/dialogActions";

export function useDeviceBulkEditDialog(props, emit) {
	const dialogStore = useDialogStore();
	const formatters = inject("formatters");
	const { closeDialog, isDialogOpen } = dialogStore;
	const { toastSuccess, toastError } = useToaster();

	const isUpdating = ref(false);

	const selectedTemplate = ref([]);
	const selectedCategory = ref([]);
	const selectedTags = ref([]);
	const selectedVendor = ref([]);
	const deviceEnablePrompt = ref("");
	const deviceMainPrompt = ref("");
	const selectedModel = ref([]);
	const append = ref(false);
	const errorMessage = ref("");
	const successMsg = ref("");
	const propertyOptions = [
		{ id: 2, name: "command_group" },
		{ id: 4, name: "device_enable_prompt" },
		{ id: 5, name: "device_main_prompt" },
		{ id: 6, name: "model" },
		{ id: 9, name: "tag" },
		{ id: 10, name: "template" },
		{ id: 11, name: "vendor" },
	];

	const finalPropertyOptions = propertyOptions;

	const selectedPropertyName = ref("");
	const selectedProperty = computed(() => {
		return propertyOptions.find((prop) => prop.name === selectedPropertyName.value) || {};
	});

	function clearMsgs() {
		setTimeout(() => {
			errorMessage.value = "";
			successMsg.value = "";
		}, 4000);
	}

	function close() {
		closeDialog("DeviceBulkEditDialog");
		resetData();
		emit("close");
	}

	function resetData() {
		isUpdating.value = false;
		selectedProperty.value = {};
		selectedTemplate.value = 0;
		selectedCategory.value = 0;
		selectedVendor.value = 0;
		deviceEnablePrompt.value = "";
		deviceMainPrompt.value = "";
		selectedModel.value = "";
		append.value = false;
		errorMessage.value = "";
		successMsg.value = "";
	}

	function confirm() {
		errorMessage.value = "";
		if (props.checkedRows.length === 0) {
			toastError("Error", "Please select at least one device to update.");
			errorMessage.value = "Please select at least one device to update.";
			return;
		}

		const typeActions = {
			command_group: {
				value: selectedCategory.value,
				errorMessage: "Please select a Command Group",
				updateFunction: updateCategory,
			},
			vendor: {
				value: selectedVendor.value,
				errorMessage: "Please select a Vendor",
				updateFunction: updateVendor,
			},
			device_enable_prompt: {
				value: deviceEnablePrompt.value,
				errorMessage: "Please enter a Device Enable Prompt",
				updateFunction: updateDeviceEnablePrompt,
			},
			device_main_prompt: {
				value: deviceMainPrompt.value,
				errorMessage: "Please enter a Device Main Prompt",
				updateFunction: updateDeviceMainPrompt,
			},
			model: {
				value: selectedModel.value,
				errorMessage: "Please enter a Model",
				updateFunction: updateModel,
			},
			template: {
				value: selectedTemplate.value,
				errorMessage: "Please select a template",
				updateFunction: updateTemplate,
			},
			tag: {
				value: selectedTags.value,
				errorMessage: "Please select one or more tags",
				updateFunction: updateTags,
			},
			default: {
				value: 0,
				errorMessage: "Please select a Property",
				updateFunction: () => {},
			},
		};

		const selectedAction = typeActions[selectedProperty.value.name] || typeActions.default;

		if (selectedAction.value.length === 0) {
			toastError("Error", selectedAction.errorMessage);
			errorMessage.value = selectedAction.errorMessage;
			return;
		}

		selectedAction.updateFunction();
	}

	function updateCategory() {
		isUpdating.value = true;
		axios
			.post("/api/device/bulk-update/category", {
				device_ids: props.checkedRows,
				category_id: selectedCategory.value.id,
			})
			.then((response) => {
				toastSuccess("Success", response.data.message);
				isUpdating.value = false;
				successMsg.value = "Categories updated successfully, you can now close this dialog or continue updating other properties.";
				clearMsgs();

				emit("bulkUpdateSuccess");
			})
			.catch((error) => {
				toastError("Error", error.response.data.message || "An error occurred while updating categories");

				isUpdating.value = false;
				emit("bulkUpdateSuccess");
			});
	}

	function updateVendor() {
		isUpdating.value = true;
		axios
			.post("/api/device/bulk-update/vendor", {
				device_ids: props.checkedRows,
				vendor_id: selectedVendor.value.id,
			})
			.then((response) => {
				toastSuccess("Success", response.data.message);
				isUpdating.value = false;
				successMsg.value = "Vendor updated successfully, you can now close this dialog or continue updating other properties.";
				clearMsgs();

				emit("bulkUpdateSuccess");
			})
			.catch((error) => {
				toastError("Error", error.response.data.message || "An error occurred while updating vendors");

				isUpdating.value = false;
				emit("bulkUpdateSuccess");
			});
	}

	function updateDeviceMainPrompt() {
		isUpdating.value = true;
		axios
			.post("/api/device/bulk-update/device_main_prompt", {
				device_ids: props.checkedRows,
				device_main_prompt: deviceMainPrompt.value,
			})
			.then((response) => {
				toastSuccess("Success", response.data.message);
				isUpdating.value = false;
				successMsg.value = "Device main prompt updated successfully, you can now close this dialog or continue updating other properties.";
				clearMsgs();

				emit("bulkUpdateSuccess");
			})
			.catch((error) => {
				toastError("Error", error.response.data.message || "An error occurred while updating device main prompt");

				isUpdating.value = false;
				emit("bulkUpdateSuccess");
			});
	}

	function updateDeviceEnablePrompt() {
		isUpdating.value = true;
		axios
			.post("/api/device/bulk-update/device_enable_prompt", {
				device_ids: props.checkedRows,
				device_enable_prompt: deviceEnablePrompt.value,
			})
			.then((response) => {
				toastSuccess("Success", response.data.message);
				isUpdating.value = false;
				successMsg.value = "Device enable prompt updated successfully, you can now close this dialog or continue updating other properties.";
				clearMsgs();

				emit("bulkUpdateSuccess");
			})
			.catch((error) => {
				toastError("Error", error.response.data.message || "An error occurred while updating device enable prompt");

				isUpdating.value = false;
				emit("bulkUpdateSuccess");
			});
	}

	function updateModel() {
		isUpdating.value = true;
		axios
			.post("/api/device/bulk-update/model", {
				device_ids: props.checkedRows,
				device_model: selectedModel.value.name,
			})
			.then((response) => {
				toastSuccess("Success", response.data.message);
				isUpdating.value = false;
				successMsg.value = "Model updated successfully, you can now close this dialog or continue updating other properties.";
				clearMsgs();

				emit("bulkUpdateSuccess");
			})
			.catch((error) => {
				toastError("Error", error.response.data.message || "An error occurred while updating model");

				isUpdating.value = false;
				emit("bulkUpdateSuccess");
			});
	}

	function updateTemplate() {
		isUpdating.value = true;
		axios
			.post("/api/device/bulk-update/template", {
				device_ids: props.checkedRows,
				template_id: selectedTemplate.value.id,
			})
			.then((response) => {
				toastSuccess("Success", response.data.message);
				isUpdating.value = false;
				successMsg.value = "Template updated successfully, you can now close this dialog or continue updating other properties.";
				clearMsgs();

				emit("bulkUpdateSuccess");
			})
			.catch((error) => {
				toastError("Error", error.response.data.message || "An error occurred while updating template");

				isUpdating.value = false;
				emit("bulkUpdateSuccess");
			});
	}

	function updateTags() {
		// get ids from tags
		const tags = selectedTags.value.map((tag) => tag.id);
		isUpdating.value = true;
		axios
			.post("/api/device/bulk-update/tags", {
				device_ids: props.checkedRows,
				tag_ids: tags,
				append: append.value ? 1 : 0,
			})
			.then((response) => {
				toastSuccess("Success", response.data.message);
				isUpdating.value = false;
				successMsg.value = "Tags updated successfully, you can now close this dialog or continue updating other properties.";
				clearMsgs();

				emit("bulkUpdateSuccess");
			})
			.catch((error) => {
				toastError("Error", error.response.data.message || "An error occurred while updating tags");

				isUpdating.value = false;
				emit("bulkUpdateSuccess");
			});
	}

	return {
		// state
		append,
		deviceEnablePrompt,
		deviceMainPrompt,
		errorMessage,
		formatters,
		isUpdating,
		selectedModel,
		selectedCategory,
		selectedProperty,
		selectedPropertyName,
		selectedTemplate,
		selectedVendor,
		successMsg,
		selectedTags,
		finalPropertyOptions,

		// Methods
		clearMsgs,
		close,
		closeDialog,
		confirm,
		isDialogOpen,
		resetData,
	};
}