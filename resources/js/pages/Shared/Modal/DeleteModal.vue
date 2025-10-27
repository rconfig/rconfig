<script setup>
import { computed } from "vue";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { AlertTriangleIcon } from "lucide-vue-next";

const props = defineProps({
	editId: {
		type: [Number, String],
		required: true,
	},
	name: {
		type: String,
		default: "item",
	},
	customMessage: {
		type: String,
		default: "",
	},
	dangerButtonText: {
		type: String,
		default: "Delete",
	},
	cancelButtonText: {
		type: String,
		default: "Cancel",
	},
});

const emit = defineEmits(["close-modal", "confirm-delete"]);

const confirmationMessage = computed(() => {
	if (props.customMessage) {
		return props.customMessage;
	}
	return `Are you sure you want to delete this ${props.name.toLowerCase()}? This action cannot be undone.`;
});

function closeModal() {
	emit("close-modal");
}

function confirmAction() {
	emit("confirm-delete", props.editId);
}
</script>

<template>
	<Dialog :open="true" @update:open="closeModal">
		<DialogContent class="sm:max-w-md">
			<DialogHeader>
				<DialogTitle class="flex items-center gap-2">
					<RcIcon name="alert-triangle" class="h-5 w-5 text-destructive" />
					<span>Delete {{ name }}</span>
				</DialogTitle>
				<DialogDescription>
					{{ confirmationMessage }}
				</DialogDescription>
			</DialogHeader>

			<DialogFooter class="sm:justify-between gap-2 mt-4">
				<Button variant="outline" @click="closeModal">
					{{ cancelButtonText }}
				</Button>
				<Button variant="destructive" @click="confirmAction">
					{{ dangerButtonText }}
				</Button>
			</DialogFooter>
		</DialogContent>
	</Dialog>
</template>
