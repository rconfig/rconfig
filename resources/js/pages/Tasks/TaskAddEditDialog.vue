<script setup>
import NavPanel from "@/pages/Tasks/WizardPanels/NavPanel.vue";
import RcConfirmAlertDialog from "@/pages/Shared/ConfirmAlertDialog/RcConfirmAlertDialog.vue";
import TaskTypeSelection from "@/pages/Tasks/TaskTypeSelection.vue";
import Step1 from "@/pages/Tasks/WizardPanels/Step1.vue";
import Step2 from "@/pages/Tasks/WizardPanels/Step2.vue";
import Step3 from "@/pages/Tasks/WizardPanels/Step3.vue";
import Step4 from "@/pages/Tasks/WizardPanels/Step4.vue";
import Step5 from "@/pages/Tasks/WizardPanels/Step5.vue";
import axios from "axios";
import { Dialog, DialogContent, DialogScrollContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Button } from "@/components/ui/button";
import { ref, onMounted, onUnmounted } from "vue";
import { useDialogStore } from "@/stores/dialogActions";
import { useOnboardingCompletion } from "@/composables/useOnboardingCompletion";
import { useToaster } from "@/composables/useToaster";

const { toastSuccess, toastError, toastInfo, toastWarning, toastDefault } = useToaster();
const { markStepComplete } = useOnboardingCompletion();

const dialogStore = useDialogStore();
const { openDialog, closeDialog, isDialogOpen } = dialogStore;
const emit = defineEmits(["save"]);
const showConfirmCloseAlert = ref(false);
const errors = ref([]);
const errorMessage = ref("");
const Step5Validated = ref(true);
const model = ref({
	task_name: "",
	task_desc: "",
	task_command: "",
	integration_id: null,
	task_categories: null,
	task_devices: null,
	task_tags: null,
	task_snippet: null,
	task_policyassignment: null,
	task_archive_logs: null,
	archive_type: null,
	purge_days: "",
	archive_value: "",
	task_cron: ["*", "*", "*", "*", "*"],
	task_email_notify: true,
	download_report_notify: true,
	verbose_download_report_notify: false,
	is_system: 0,
	created_at: null,
	updated_at: null,
	// Arrays for multi-selects
	device: [],
	category: [],
	tag: [],
	snippet: [],
	policyassignment: [],
	api_collection: [],
});

const activeStep = ref(0);
const showTaskTypeSelection = ref(true);

const props = defineProps({
	editId: Number,
});

function handleKeyDown(event) {
	if (event.ctrlKey && event.key === "Enter") {
		saveDialog();
	}
}

onMounted(() => {
	if (props.editId > 0) {
		axios.get(`/api/tasks/${props.editId}`).then((response) => {
			model.value = {
				...model.value,
				...response.data,
			};
			showTaskTypeSelection.value = false;
			activeStep.value = 2;
		});
	} else {
		showTaskTypeSelection.value = true;
		activeStep.value = 0;
	}

	window.addEventListener("keydown", handleKeyDown);
});

onUnmounted(() => {
	window.removeEventListener("keydown", handleKeyDown);
});

function saveDialog() {
	let id = props.editId > 0 ? `/${props.editId}` : "";
	let method = props.editId > 0 ? "patch" : "post";
	axios[method]("/api/tasks" + id, model.value)
		.then((response) => {
			emit("save", response.data);
			const message = props.editId > 0 ? "Task updated successfully" : "Task created successfully";
			toastSuccess("Success", message);
			markStepComplete("add_schedule_task");

			closeDialog("DialogNewTask");
		})
		.catch((error) => {
			if (error.response && error.response.data && error.response.data.errors) {
				errors.value = Object.values(error.response.data.errors).flat();
			} else {
				errorMessage.value = "An error occurred while saving the task";
			}
		});
}

function prevPage() {
	if (activeStep.value === 2) {
		activeStep.value = 0;
		showTaskTypeSelection.value = true;
		return;
	}

	if (activeStep.value > 1) {
		activeStep.value--;
	}
}

function nextPage() {
	errorMessage.value = "";
	errors.value = [];

	if (activeStep.value === 0) {
		activeStep.value = 2;
		showTaskTypeSelection.value = false;
		return;
	}

	if (activeStep.value === 1 && (!model.value.task_command || model.value.task_command === "")) {
		errorMessage.value = "Please select a task type";
		return;
	}

	if (activeStep.value === 2 && model.value.task_name === "") {
		errorMessage.value = "Please enter a task name";
		return;
	}

	if (activeStep.value === 3 && model.value.task_command === "rconfig:download-device" && (!model.value.device || model.value.device.length === 0)) {
		errorMessage.value = "Please choose one or more devices";
		return;
	}

	if (activeStep.value === 3 && model.value.task_command === "rconfig:download-category" && (!model.value.category || model.value.category.length === 0)) {
		errorMessage.value = "Please choose one or more categories";
		return;
	}

	if (activeStep.value === 3 && model.value.task_command === "rconfig:download-tag" && (!model.value.tag || model.value.tag.length === 0)) {
		errorMessage.value = "Please choose one or more tags";
		return;
	}

	if (activeStep.value === 4 && model.value.task_cron === "") {
		errorMessage.value = "Please enter schedule values";
		return;
	}

	activeStep.value++;
}

function handleTaskSelected(command) {
	console.log("Task selected:", command);
}

function handleContinueFromTaskSelection() {
	activeStep.value = 2;
	showTaskTypeSelection.value = false;
}

function confirmCloseWizardOpen() {
	showConfirmCloseAlert.value = true;
}

function cancelCloseWizard() {
	showConfirmCloseAlert.value = false;
}

function confirmCloseWizard() {
	showConfirmCloseAlert.value = false;
	closeDialog("DialogNewTask");
}

function Step5CheckSuccess() {
	console.log("Step5CheckSuccess");
	Step5Validated.value = false;
}
</script>

<template>
	<Dialog :open="isDialogOpen('DialogNewTask')">
		<DialogTrigger as-child>
			<!-- Trigger button -->
		</DialogTrigger>
		<DialogContent @interactOutside="confirmCloseWizardOpen()" @pointerDownOutside="confirmCloseWizardOpen()" class="max-w-4xl gap-0 p-0 m-4 bg-rcgray-900" @escapeKeyDown="closeDialog('DialogNewTask')" @closeClicked="closeDialog('DialogNewTask')">
			<DialogHeader class="rc-dialog-header">
				<DialogTitle class="text-sm text-rcgray-200">
					<div class="flex items-center">
						<RcIcon name="tasks" />
						<span class="ml-2">
							{{ props.editId > 0 ? "Edit" : "Add" }}
							Task
							{{ props.editId > 0 ? `(ID: ${props.editId})` : "" }}
						</span>
					</div>
				</DialogTitle>
			</DialogHeader>

			<!-- Task Type Selection -->
			<div v-if="showTaskTypeSelection" class="p-6">
				<TaskTypeSelection :model="model" @taskSelected="handleTaskSelected" @continue="handleContinueFromTaskSelection" />
			</div>

			<!-- Wizard Steps -->
			<div v-else>
				<ResizablePanelGroup direction="horizontal" class="p-4">
					<ResizablePanel :default-size="25" :max-size="25" :min-size="25" collapsible :collapsed-size="25" ref="panelElement" class="">
						<NavPanel :currentStep="activeStep" />
					</ResizablePanel>
					<ResizableHandle />
					<ResizablePanel class="max-h-[80vh]">
						<ScrollArea class="h-full px-4 border border-none rounded-md">
							<Step2 v-if="activeStep === 2" :model="model" />
							<Step3 :model="model" v-if="activeStep === 3" />
							<Step4 :model="model" v-if="activeStep === 4" />
							<Step5 @checkSuccess="Step5CheckSuccess()" :model="model" v-if="activeStep === 5" />
						</ScrollArea>
					</ResizablePanel>
				</ResizablePanelGroup>
			</div>

			<!-- Navigation Footer -->
			<div v-if="!showTaskTypeSelection" class="w-full py-2 border-t border-b">
				<div class="flex items-center px-2" :class="errors.length > 0 || errorMessage ? 'justify-between' : 'justify-end'">
					<!-- Display validation errors -->
					<div v-if="errors.length > 0" class="text-sm text-red-500">
						<ul class="list-disc list-inside">
							<li v-for="error in errors" :key="error">{{ error }}</li>
						</ul>
					</div>
					<div v-else-if="errorMessage" class="text-sm text-red-500">
						{{ errorMessage }}
					</div>

					<div class="flex">
						<Button @click.prevent="prevPage()" variant="outline" class="px-2 py-1 text-sm hover:animate-pulse" size="sm">
							Previous
						</Button>
						<Button :disabled="activeStep === 5" @click.prevent="nextPage()" variant="outline" class="px-2 py-1 ml-2 text-sm hover:animate-pulse" size="sm">
							Next
						</Button>
					</div>
				</div>
			</div>

			<DialogFooter class="rc-dialog-footer bg-rcgray-800">
				<Button type="close" variant="outline" class="px-2 py-1 ml-2 text-sm hover:bg-gray-700 hover:animate-pulse" @click="confirmCloseWizardOpen()" size="sm">
					Cancel
					<div class="pl-2 ml-auto">
						<kbd class="rc-kdb-class">ESC</kbd>
					</div>
				</Button>

				<Button :disabled="Step5Validated" v-if="props.editId === 0" type="submit" class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse" size="sm" @click="saveDialog()" variant="primary">
					Save
					<div class="pl-2 ml-auto">
						<kbd class="rc-kdb-class2">
							Ctrl&nbsp;
							<RcIcon name="enter" class="ml-1" />
						</kbd>
					</div>
				</Button>

				<Button v-if="props.editId > 0" type="submit" class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse" size="sm" @click="saveDialog()" variant="primary">
					Update
					<div class="pl-2 ml-auto">
						<kbd class="rc-kdb-class2">
							Ctrl&nbsp;
							<RcIcon name="enter" class="ml-1" />
						</kbd>
					</div>
				</Button>
			</DialogFooter>
		</DialogContent>
	</Dialog>
	<RcConfirmAlertDialog :showConfirmCloseAlert="showConfirmCloseAlert" @handleClose="cancelCloseWizard" @handleConfirm="confirmCloseWizard" />
</template>