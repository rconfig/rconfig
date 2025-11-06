<script setup>
import axios from "axios";
import CategoryMultiSelect from "@/pages/Shared/FormFields/CategoryMultiSelect.vue";
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { ref, onMounted, onUnmounted } from "vue";
import { useDialogStore } from "@/stores/dialogActions";
import { useToaster } from "@/composables/useToaster";
import Spinner from "@/pages/Shared/Icon/Spinner.vue";

const { toastSuccess, toastError } = useToaster();
const dialogStore = useDialogStore();
const { closeDialog, isDialogOpen } = dialogStore;

// Props and emits
const props = defineProps({
	data: {
		type: Array,
		required: true,
	},
	isOpen: {
		type: Boolean,
		default: false,
	},
});

const emit = defineEmits(["close", "updated"]);

// Reactive state
const errors = ref("");
const selectedCategories = ref([]);
const isLoading = ref(false);

// Get categories data
const categories = ref([]);

// Fetch Command Groups
async function fetchCommandGroups(params = {}) {
	isLoading.value = true;
	try {
		const response = await axios.get("/api/categories?per_page=1000", {});
		categories.value = response.data;
	} catch (error) {
		console.error("Error fetching categories:", error);
		toastError("Error", "Failed to fetch categories.");
	} finally {
		isLoading.value = false;
	}
}

// Keyboard shortcut handler
function handleKeyDown(event) {
	if (event.ctrlKey && event.key === "Enter") {
		confirmUpdate();
	}
	if (event.key === "Escape") {
		closeDialog("DialogBulkUpdateCommands");
	}
}

// Mount/unmount keyboard listeners
onMounted(() => {
	fetchCommandGroups();
	window.addEventListener("keydown", handleKeyDown);
});

onUnmounted(() => {
	window.removeEventListener("keydown", handleKeyDown);
});

function updateCategorySelection(categories) {
	selectedCategories.value = categories;
}

function close(type) {
	emit(type === "updated" ? "updated" : "close");
}

async function confirmUpdate() {
	if (selectedCategories.value.length === 0) {
		errors.value = "You must select at least one command group.";
		return;
	}

	isLoading.value = true;
	errors.value = "";

	try {
		await axios.post("/api/commands/bulk-update-categories", {
			categories: selectedCategories.value,
			commands: props.data,
		});

		toastSuccess("Command groups updated", `Successfully updated ${props.data.length} command(s) with new command groups.`);
		close("updated");
	} catch (error) {
		const errorMessage = error.response?.data?.message || "Failed to update command groups";
		errors.value = errorMessage;
		toastError("Update failed", errorMessage);
	} finally {
		isLoading.value = false;
	}
}
</script>

<template>
	<Dialog :open="isDialogOpen('DialogBulkUpdateCommands')">
		<DialogContent class="p-0 sm:max-w-2xl" @escapeKeyDown="close" @pointerDownOutside="close" @closeClicked="close">
			<DialogHeader class="rc-dialog-header">
				<DialogTitle class="text-sm text-rcgray-200">
					<div class="flex items-center">
						<RcIcon name="commands" />
						<span class="ml-2">Update Command Groups</span>
					</div>
				</DialogTitle>
			</DialogHeader>

			<div class="grid gap-4 p-4">
				<p class="text-sm text-rcgray-300">The commands below will be updated with the selected command groups.</p>

				<!-- Commands List -->
				<div class="max-h-40 overflow-y-auto rounded-lg border border-border bg-background/80 backdrop-blur-sm p-2">
					<ul class="space-y-1.5">
						<li
							v-for="command in data"
							:key="command.id || command.command"
							class="flex items-center text-xs font-mono text-muted-foreground bg-muted/60 hover:bg-muted px-3 py-1.5 rounded transition-colors"
						>
							<span class="text-green-400 mr-2">$</span>
							<span class="text-foreground">{{ command.command }}</span>
						</li>
					</ul>
				</div>

				<!-- Category Selection -->
				<div class="grid gap-2">
					<Label class="text-sm font-medium text-rcgray-200">
						Command Groups <span class="text-red-400">*</span>
					</Label>
					<CategoryMultiSelect v-model="selectedCategories" id="selectedCategoryObj" />

					<p class="text-xs text-rcgray-400">You must associate one or multiple command groups to the chosen commands.</p>
				</div>

				<!-- Error Display -->
				<div v-if="errors" class="text-sm text-red-400 bg-red-900/20 border border-red-700 rounded p-2">
					{{ errors }}
				</div>
			</div>

			<DialogFooter class="rc-dialog-footer bg-rcgray-800">
				<Button type="button" variant="outline" class="px-2 py-1 ml-2 text-sm hover:bg-gray-700 hover:animate-pulse" @click="close" size="sm">
					Cancel
					<div class="pl-2 ml-auto">
						<kbd class="rc-kdb-class">ESC</kbd>
					</div>
				</Button>

				<Button
					type="submit"
					class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
					size="sm"
					@click="confirmUpdate"
					variant="primary"
					:disabled="isLoading || selectedCategories.length === 0"
				>
					<Spinner v-if="isLoading" class="w-4 h-4 mr-2" />
					Confirm
					<div class="pl-2 ml-auto" v-if="!isLoading">
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
