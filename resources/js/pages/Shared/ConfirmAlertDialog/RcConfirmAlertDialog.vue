<script setup>
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from "@/components/ui/alert-dialog";
import { onMounted, onUnmounted, computed } from "vue";

const emit = defineEmits(["handleConfirm", "handleClose", "handleDelete", "handleDisable", "handlePurge", "handleReset", "handleConfirmProceedAlert", "close"]);

const props = defineProps({
	showConfirmCloseAlert: Boolean,
	showConfirmDelete: Boolean,
	showConfirmDisable: Boolean,
	showConfirmPurge: Boolean,
	showConfirmReset: Boolean,
	showConfirmConfirmProceedAlertAlert: Boolean,

	ids: [String, Number, Array],
	editId: [Number, String],
	message: String,

	title: String,
	description: String,

	confirmKey: { type: String, default: " " },
	cancelKey: { type: String, default: "Escape" },
	showKeyHints: { type: Boolean, default: false },
	purgedDataMsg: { type: String, default: "" },
});

const open = computed(() => props.showConfirmCloseAlert || props.showConfirmDelete || props.showConfirmDisable || props.showConfirmPurge || props.showConfirmConfirmProceedAlertAlert || props.showConfirmReset);

const defaultTitle = computed(() => {
	if (props.title) return props.title;
	if (props.showConfirmDelete) return "Delete selected items?";
	if (props.showConfirmDisable) return `Disable device${Array.isArray(props.ids) ? "s" : ""}?`;
	if (props.showConfirmPurge) return `Purge selected items?`;
	if (props.showConfirmReset) return "Are you sure you want to reset to defaults?";
	if (props.showConfirmConfirmProceedAlertAlert) return "Are you sure you want to proceed?";
	return "Are you absolutely sure?";
});

const defaultDescription = computed(() => {
	if (props.description) return props.description;
	if (props.message) return props.message;
	if (props.showConfirmDelete) {
		const idsArray = Array.isArray(props.ids) ? props.ids : [props.ids];
		const count = idsArray.length;
		if (count > 5) {
			return `This will permanently delete ${count} selected items. This action cannot be undone.`;
		}
		return `This will permanently delete the item${Array.isArray(props.ids) ? "s" : ""} with ID: ${props.ids}. All related data (configs, changes and policies results) will also be removed. This action cannot be undone.`;
	}
	if (props.showConfirmDisable) return `Disabling will stop device${Array.isArray(props.ids) ? "s" : ""} from performing scheduled tasks.`;
	if (props.showConfirmPurge) return `This will permanently purge the selected data ${props.purgedDataMsg}. This action cannot be undone.`;
	if (props.showConfirmConfirmProceedAlertAlert) return `Please confirm you want to proceed with the current operation.`;
	if (props.showConfirmReset) return `This will reset the device${Array.isArray(props.ids) ? "s" : ""} to defaults.`;
	return `Confirming will close the dialog and discard any unsaved changes.`;
});

// Get appropriate button style based on operation type
const actionButtonClass = computed(() => {
	if (props.showConfirmDelete || props.showConfirmPurge) {
		return "text-white bg-red-600 hover:bg-red-700 hover:animate-pulse";
	}
	return "text-white bg-blue-600 hover:bg-blue-700 hover:animate-pulse";
});

// Get appropriate button text based on operation type
const actionButtonText = computed(() => {
	if (props.showConfirmDelete) return "Delete";
	if (props.showConfirmDisable) return "Disable";
	if (props.showConfirmPurge) return "Purge";
	if (props.showConfirmReset) return "Reset";
	return "Continue";
});

// Confirm handler
function handleConfirm() {
	if (props.showConfirmDelete) {
		emit("handleDelete", props.ids);
	} else if (props.showConfirmDisable) {
		emit("handleDisable", props.ids);
	} else if (props.showConfirmPurge) {
		emit("handlePurge");
	} else if (props.showConfirmReset) {
		emit("handleReset", props.editId);
	} else if (props.showConfirmConfirmProceedAlertAlert) {
		emit("handleConfirm", props.editId);
		emit("handleConfirmProceedAlert", props.editId);
	} else {
		emit("handleConfirm", props.ids);
	}
}

function handleClose() {
	if (props.showConfirmDelete || props.showConfirmDisable || props.showConfirmPurge) {
		emit("close");
	} else {
		emit("handleClose");
	}
}
</script>

<template>
	<AlertDialog :open="open">
		<AlertDialogContent>
			<AlertDialogHeader>
				<AlertDialogTitle>{{ defaultTitle }}</AlertDialogTitle>
				<AlertDialogDescription>
					<slot name="description">{{ defaultDescription }}</slot>
				</AlertDialogDescription>
			</AlertDialogHeader>

			<AlertDialogFooter>
				<AlertDialogCancel type="button" @click="handleClose">Cancel</AlertDialogCancel>
				<AlertDialogAction type="button" @click="handleConfirm" :class="actionButtonClass">
					{{ actionButtonText }}
					<div v-if="showKeyHints" class="pl-2 ml-auto">
						<kbd class="rc-kdb-class">{{ confirmKey === " " ? "SPC" : confirmKey }}</kbd>
					</div>
				</AlertDialogAction>
			</AlertDialogFooter>
		</AlertDialogContent>
	</AlertDialog>
</template>
