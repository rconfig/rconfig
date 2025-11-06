<script setup>
import CategoryMultiSelect from "@/pages/Shared/FormFields/CategoryMultiSelect.vue";
import Spinner from "@/pages/Shared/Icon/Spinner.vue";
import axios from "axios";
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { ref, onMounted, onUnmounted } from "vue";
import { useDialogStore } from "@/stores/dialogActions";
import { useToaster } from "@/composables/useToaster";

const { toastSuccess } = useToaster();

const dialogStore = useDialogStore();
const { openDialog, closeDialog, isDialogOpen } = dialogStore;

const emit = defineEmits(["save"]);
const errors = ref([]);
const isLoading = ref(false);
const model = ref({
	command: "",
	description: "",
	category: [],
});

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
		isLoading.value = true;
		axios
			.get(`/api/commands/${props.editId}`)
			.then((response) => {
				model.value = response.data;
				model.value.categoryArray = response.data.category.map((cat) => cat.id);
			})
			.catch((error) => {
				console.error("Error loading command:", error);
			})
			.finally(() => {
				isLoading.value = false;
			});
	}

	window.addEventListener("keydown", handleKeyDown);
});

onUnmounted(() => {
	window.removeEventListener("keydown", handleKeyDown);
});

function saveDialog() {
	const id = props.editId > 0 ? `/${props.editId}` : "";
	const method = props.editId > 0 ? "patch" : "post";

	axios[method]("/api/commands" + id, model.value)
		.then((response) => {
			emit("save", response.data);
			toastSuccess("Command saved", "The command has been saved successfully.");
			closeDialog("DialogNewCommand");
		})
		.catch((error) => {
			errors.value = error.response?.data?.errors || {};
		});
}
</script>

<template>
	<Dialog :open="isDialogOpen('DialogNewCommand')">
		<DialogTrigger as-child />
		<DialogContent
			class="p-0 sm:max-w-fit"
			@escapeKeyDown="closeDialog('DialogNewCommand')"
			@pointerDownOutside="closeDialog('DialogNewCommand')"
			@closeClicked="closeDialog('DialogNewCommand')"
		>
			<DialogHeader class="rc-dialog-header">
				<DialogTitle class="text-sm text-rcgray-200">
					<div class="flex items-center">
						<RcIcon name="commands" />
						<span class="ml-2">{{ editId > 0 ? 'Edit' : 'Add' }} Command {{ editId > 0 ? "(ID: " + editId + ")" : "" }}</span>
					</div>
				</DialogTitle>
			</DialogHeader>

			<div class="grid gap-2 p-4">
				<!-- Command Name Field -->
				<div class="grid items-center grid-cols-4 gap-2">
					<Label for="command" class="text-right">
						Command Name <span class="text-red-400">*</span>
					</Label>
					<div v-if="isLoading" class="col-span-3">
						<Skeleton class="h-6 w-full" />
					</div>
					<Input v-else v-model="model.command" id="command" class="col-span-3" />
					<span class="col-span-3 col-start-2 text-sm text-red-400" v-if="errors.command && !isLoading">
						{{ errors.command[0] }}
					</span>
				</div>

				<!-- Description Field -->
				<div class="grid items-center grid-cols-4 gap-2">
					<Label for="description" class="text-right">Description</Label>
					<div v-if="isLoading" class="col-span-3">
						<Skeleton class="h-6 w-full" />
					</div>
					<Input v-else v-model="model.description" id="description" class="col-span-3" />
				</div>

				<!-- Command Group Field -->
				<div class="grid items-center grid-cols-4 gap-2">
					<Label class="text-right">
						Command Group <span class="text-red-400">*</span>
					</Label>
					<div v-if="isLoading" class="col-span-3">
						<Skeleton class="h-6 w-full" />
					</div>
					<CategoryMultiSelect v-else v-model="model.category" />
					<span class="col-span-3 col-start-2 text-sm text-red-400" v-if="errors.categoryArray && !isLoading">
						{{ errors.categoryArray[0] }}
					</span>
				</div>
			</div>

			<DialogFooter class="rc-dialog-footer bg-rcgray-800">
				<Button type="button" variant="outline" class="px-2 py-1 ml-2 text-sm hover:bg-gray-700 hover:animate-pulse" @click="closeDialog('DialogNewCommand')" size="sm">
					Close
					<div class="pl-2 ml-auto">
						<kbd class="rc-kdb-class">ESC</kbd>
					</div>
				</Button>

				<Button
					type="submit"
					class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
					size="sm"
					@click="saveDialog"
					variant="primary"
				>
					{{ props.editId === 0 ? 'Save' : 'Update' }}
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
</template>
