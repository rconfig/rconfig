<script setup>
import BadgeColorSelector from "@/pages/Inventory/CommandGroups/BadgeColorSelector.vue";
import axios from "axios";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { ref, onMounted, onUnmounted } from "vue";
import { useDialogStore } from "@/stores/dialogActions";
import { useToaster } from "@/composables/useToaster";

const { toastSuccess, toastError, toastInfo, toastWarning, toastDefault } = useToaster();

const dialogStore = useDialogStore();
const { openDialog, closeDialog, isDialogOpen } = dialogStore;
const emit = defineEmits(["save"]);
const roles = ref([]);
const errors = ref([]);
const model = ref({
	categoryName: "",
	categoryDescription: "",
	badgeColor: "",
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
		axios.get(`/api/categories/${props.editId}`).then((response) => {
			model.value = response.data;
		});
	}

	window.addEventListener("keydown", handleKeyDown);
});

onUnmounted(() => {
	window.removeEventListener("keydown", handleKeyDown);
});

function saveDialog() {
	let id = props.editId > 0 ? `/${props.editId}` : ""; // determine if we are creating or updating
	let method = props.editId > 0 ? "patch" : "post"; // determine if we are creating or updating
	axios[method]("/api/categories" + id, model.value)
		.then((response) => {
			emit("save", response.data);
			toastSuccess("Command Group created", "The tag has been created successfully.");
			closeDialog("DialogNewCommandGroups");
		})
		.catch((error) => {
			errors.value = error.response.data.errors;
		});
}
</script>

<template>
	<Dialog :open="isDialogOpen('DialogNewCommandGroups')">
		<DialogTrigger as-child>
			<!-- <Button variant="outline">Edit Profile</Button> -->
		</DialogTrigger>
		<DialogContent class="p-0 sm:max-w-fit" @escapeKeyDown="closeDialog('DialogNewCommandGroups')" @pointerDownOutside="closeDialog('DialogNewCommandGroups')" @closeClicked="closeDialog('DialogNewCommandGroups')">
			<DialogHeader class="rc-dialog-header">
				<DialogTitle class="text-sm text-rcgray-200">
					<div class="flex items-center">
						<RcIcon name="command-group" />
						<span class="ml-2">{{ editId > 0 ? 'Edit' : 'Add' }} Command Group {{ editId > 0 ? "(ID: " + editId + ")" : "" }}</span>
					</div>
				</DialogTitle>
			</DialogHeader>
			<div class="grid gap-2 p-4">
				<div class="grid items-center grid-cols-4 gap-2">
					<Label for="categoryName" class="text-right">
						Name
						<span class="text-red-400">*</span>
					</Label>
					<Input v-model="model.categoryName" id="categoryName" class="col-span-3" />
					<span class="col-span-3 col-start-2 text-sm text-red-400" v-if="errors?.categoryName">
						{{ errors?.categoryName[0] }}
					</span>
				</div>

				<div class="grid items-center grid-cols-4 gap-2">
					<Label for="categoryDescription" class="text-right">
						Description
					</Label>
					<Input v-model="model.categoryDescription" id="categoryDescription" class="col-span-3" />
				</div>

				<div class="grid items-center grid-cols-4 gap-4">
					<Label for="badgeColor" class="text-right">
						Badge Color
					</Label>
					<BadgeColorSelector v-model="model.badgeColor" />
				</div>
			</div>

			<DialogFooter class="rc-dialog-footer bg-rcgray-800">
				<Button type="close" variant="outline" class="px-2 py-1 ml-2 text-sm hover:bg-gray-700 hover:animate-pulse" @click="closeDialog('DialogNewCommandGroups')" size="sm">
					Close
					<div class="pl-2 ml-auto">
						<kbd class="rc-kdb-class">ESC</kbd>
					</div>
				</Button>
				<Button v-if="props.editId === 0" type="submit" class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse" size="sm" @click="saveDialog" variant="primary">
					Save
					<div class="pl-2 ml-auto">
						<kbd class="rc-kdb-class2">
							Ctrl&nbsp;
							<RcIcon name="enter" class="ml-1" />
						</kbd>
					</div>
				</Button>

				<Button v-else type="submit" class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse" size="sm" @click="saveDialog" variant="primary">
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
</template>