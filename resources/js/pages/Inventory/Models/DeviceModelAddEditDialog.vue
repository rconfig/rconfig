<script setup>
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { computed, onMounted, ref, watch } from "vue";
import { useDialogStore } from "@/stores/dialogActions";
import { useToaster } from "@/composables/useToaster";
import axios from "axios";

const props = defineProps({
	editModelName: {
		type: String,
		default: "",
	},
	editId: {
		type: Number,
		default: 0,
	},
});

const emit = defineEmits(["save"]);

// --- State ---
const deviceModelForm = ref({
	name: "",
});

const isLoading = ref(false);
const errors = ref({});
const devices = ref([]);

// --- Stores & Utilities ---
const dialogStore = useDialogStore();
const { isDialogOpen, closeDialog } = dialogStore;
const { toastSuccess, toastError } = useToaster();

// --- Computed ---
const isEditMode = computed(() => !!props.editModelName);
const dialogTitle = computed(() => (props.editId != 0 ? `Edit Device Model` : `New Device Model`));

// --- Methods ---
const fetchDeviceModelData = async () => {
	isLoading.value = true;
	try {
		const response = await axios.get(`/api/device-models/${props.editId}`);
		const modelData = response.data;

		deviceModelForm.value.name = modelData.name;
		devices.value = modelData.devices || [];
	} catch (error) {
		console.error("Error fetching device model:", error);
		toastError("Error", "Failed to fetch device model data.");
		closeDialog("DialogNewDeviceModel");
	} finally {
		isLoading.value = false;
	}
};

const validateForm = () => {
	errors.value = {};

	if (!deviceModelForm.value.name?.trim()) {
		errors.value.name = t("validation.required", { field: t("common.entities.deviceModel") });
		return false;
	}

	if (deviceModelForm.value.name.length > 255) {
		errors.value.name = t("validation.maxLength", { field: t("common.entities.deviceModel"), max: 255 });
		return false;
	}

	return true;
};

const saveDeviceModel = async () => {
	if (!validateForm()) return;

	isLoading.value = true;
	errors.value = {};

	try {
		let response;

		if (props.editId != 0) {
			// Update existing device model
			response = await axios.patch(`/api/device-models/` + props.editId, deviceModelForm.value);
			toastSuccess(
				"Success",
				"The device model has been updated successfully. Devices associated with this model will take a few moments to reflect the changes.", {
					count: response.data.data.devices_updated || 0,
				}
			);
		} else {
			// Create new device model
			response = await axios.post("/api/device-models", deviceModelForm.value);
			toastSuccess("Success", "The device model has been created successfully.");
		}

		emit("save");
		closeDialog("DialogNewDeviceModel");
		resetForm();
	} catch (error) {
		if (error.response?.status === 422) {
			// Validation errors
			errors.value = error.response.data.data || {};
			toastError("Validation Error", error.response.data.message);
		} else if (error.response?.status === 409) {
			// Conflict (duplicate name)
			errors.value.name = error.response.data.message;
			toastError("Error", error.response.data.message);
		} else {
			console.error("Error saving device model:", error);
			toastError("Error", "Failed to save device model.");
		}
	} finally {
		isLoading.value = false;
	}
};

const resetForm = () => {
	deviceModelForm.value = {
		name: "",
	};
	errors.value = {};
	devices.value = [];
};

const handleCancel = () => {
	resetForm();
	closeDialog("DialogNewDeviceModel");
};

// --- Watchers ---
watch(
	() => props.editModelName,
	() => {
		if (isDialogOpen("DialogNewDeviceModel")) {
			if (isEditMode.value) {
				fetchDeviceModelData();
			} else {
				resetForm();
			}
		}
	}
);

// --- Lifecycle ---
onMounted(() => {
	if (props.editId != 0) {
		fetchDeviceModelData();
	}
});
</script>

<template>
	<Dialog :open="isDialogOpen('DialogNewDeviceModel')" @update:open="handleCancel">
		<DialogContent class="sm:max-w-[600px]">
			<DialogHeader>
				<DialogTitle>{{ dialogTitle }}</DialogTitle>
			</DialogHeader>

			<div v-if="isLoading" class="flex justify-center py-8">
				<div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
			</div>

			<form v-else @submit.prevent="saveDeviceModel" class="space-y-6">
				<!-- Device Model Name -->
				<div class="space-y-2">
					<Label for="name" class="text-sm font-medium"> Device Model Name * </Label>
					<Input id="name" v-model="deviceModelForm.name" type="text" placeholder="Enter device model name" :class="{ 'border-red-500': errors.name }" maxlength="255" required />
					<p v-if="errors.name" class="text-sm text-red-600">
						{{ errors.name }}
					</p>
				</div>

				<!-- Current Devices (Edit Mode Only) -->
				<div v-if="isEditMode && devices.length > 0" class="space-y-2">
					<Label class="text-sm font-medium"> Devices Using This Model ({{ devices.length }}) </Label>
					<div class="max-h-32 overflow-y-auto border rounded-md p-3 bg-gray-50">
						<div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
							<div v-for="device in devices" :key="device.id" class="text-sm text-gray-700 truncate" :title="device.name">• {{ device.name }}</div>
						</div>
					</div>
					<p class="text-xs text-gray-600">
						Updating the device model name will affect all devices using this model.
					</p>
				</div>

				<!-- Warning for Edit Mode -->
				<div v-if="isEditMode" class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
					<div class="flex">
						<div class="flex-shrink-0">
							<svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
								<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
							</svg>
						</div>
						<div class="ml-3">
							<p class="text-sm text-yellow-700">
								Editing the device model name will affect all devices using this model.
							</p>
						</div>
					</div>
				</div>

				<!-- Actions -->
				<DialogFooter>
					<Button type="button" variant="outline" @click="handleCancel" :disabled="isLoading">
						Cancel
					</Button>
					<!-- <Button type="submit" :disabled="isLoading" class="bg-blue-600 hover:bg-blue-700">
						<div v-if="isLoading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
						{{ isEditMode ? t("common.update") : t("common.create") }}
					</Button> -->

					<Button v-if="props.editId === 0" type="submit" class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse" size="sm" @click="saveDeviceModel()" variant="primary" :disabled="isLoading">
						Save
						<div class="pl-2 ml-auto">
							<kbd class="rc-kdb-class2">
								Ctrl&nbsp;
								<RcIcon name="enter" class="ml-1" />
							</kbd>
						</div>
					</Button>

					<Button v-else type="submit" class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse" size="sm" @click="saveDeviceModel()" variant="primary" :disabled="isLoading">
						Update
						<div class="pl-2 ml-auto">
							<kbd class="rc-kdb-class2">
								Ctrl&nbsp;
								<RcIcon name="enter" class="ml-1" />
							</kbd>
						</div>
					</Button>
				</DialogFooter>
			</form>
		</DialogContent>
	</Dialog>
</template>
