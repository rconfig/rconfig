<script setup>
import axios from "axios";
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { ref, onMounted, onUnmounted } from "vue";
import { useDialogStore } from "@/stores/dialogActions";
import { useToaster } from "@/composables/useToaster";

const { toastSuccess, toastError } = useToaster();
const dialogStore = useDialogStore();
const { closeDialog, isDialogOpen } = dialogStore;
const emit = defineEmits(["save"]);
const errors = ref([]);
const model = ref({
	tagname: "",
	tagDescription: "",
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
		axios.get(`/api/tags/${props.editId}`).then((response) => {
			model.value = response.data;
		});
	}

	window.addEventListener("keydown", handleKeyDown);
});

onUnmounted(() => {
	window.removeEventListener("keydown", handleKeyDown);
});

function saveDialog() {
	let id = props.editId > 0 ? `/${props.editId}` : "";
	let method = props.editId > 0 ? "patch" : "post";
	axios[method]("/api/tags" + id, model.value)
		.then((response) => {
			emit("save", response.data);
			toastSuccess("Tag saved", "The tag has been saved successfully.");
			closeDialog("DialogNewTag");
		})
		.catch((error) => {
			errors.value = error.response.data.errors;
			toastError("Error", "Failed to save tag.");
		});
}
</script>

<template>
	<Dialog :open="isDialogOpen('DialogNewTag')">
		<DialogTrigger as-child>
			<!-- <Button variant="outline">Edit Profile</Button> -->
		</DialogTrigger>
		<DialogContent class="w-full p-0" @escapeKeyDown="closeDialog('DialogNewTag')" @pointerDownOutside="closeDialog('DialogNewTag')" @closeClicked="closeDialog('DialogNewTag')">
			<DialogHeader class="rc-dialog-header">
				<DialogTitle class="text-sm text-rcgray-200">
					<div class="flex items-center">
						<RcIcon name="commands" />
						<span class="ml-2">{{ editId > 0 ? "Edit" : "New" }} Tag {{ editId > 0 ? "(ID: " + editId + ")" : "" }}</span>
					</div>
				</DialogTitle>
			</DialogHeader>

			<div class="grid gap-2 py-4">
				<div class="grid items-center grid-cols-4 gap-2">
					<Label for="tagname" class="text-right">
						Tag Name
						<span class="text-red-600">*</span>
					</Label>
					<Input v-model="model.tagname" id="tagname" class="col-span-3" />
					<span class="col-span-3 col-start-2 text-sm text-red-400" v-if="errors.tagname">
						{{ errors.tagname[0] }}
					</span>
				</div>

				<div class="grid items-center grid-cols-4 gap-2">
					<Label for="tagDescription" class="text-right">Description</Label>
					<Input v-model="model.tagDescription" id="tagDescription" class="col-span-3" />
					<span class="col-span-3 col-start-2 text-sm text-red-400" v-if="errors.tagDescription">
						{{ errors.tagDescription[0] }}
					</span>
				</div>
			</div>

			<DialogFooter class="rc-dialog-footer bg-rcgray-800">
				<Button type="close" variant="outline" class="px-2 py-1 ml-2 text-sm hover:bg-gray-700 hover:animate-pulse" @click="closeDialog('DialogNewTag')" size="sm">
					Cancel
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