<script setup>
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuShortcut, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { MoreVertical, Copy, Trash } from "lucide-vue-next";
import { useCopy } from "@/composables/useCopy";
import { useToaster } from "@/composables/useToaster";

const emits = defineEmits(["onDelete", "onPurge"]);
const { copyItem, activeCopyIcon } = useCopy();
const { toastSuccess } = useToaster();

const props = defineProps({
	editId: {
		type: Number,
		required: true,
	},
	showDelete: {
		type: Boolean,
		default: true,
	},
});

function handleDelete() {
	emits("onDelete");
}

function onCopy() {
	copyItem("", props.editId);
	toastSuccess("Success", "Copied to clipboard");
}
</script>

<template>
	<div>
		<DropdownMenu>
			<DropdownMenuTrigger as-child>
				<Button variant="ghost" class="h-8 ml-2">
					<MoreVertical size="16" />
				</Button>
			</DropdownMenuTrigger>
			<DropdownMenuContent class="w-56" align="end" side="bottom">
				<DropdownMenuItem class="cursor-pointer hover:bg-rcgray-600" @click="onCopy()">
					<span>Copy ID</span>
					<DropdownMenuShortcut>
						<Copy size="16" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>

				<DropdownMenuSeparator v-if="showDelete" />
				<DropdownMenuItem v-if="showDelete" class="cursor-pointer hover:bg-rcgray-600" @click="handleDelete()">
					<span class="text-red-400">Delete</span>
					<DropdownMenuShortcut>
						<Trash size="16" class="text-red-400" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>
			</DropdownMenuContent>
		</DropdownMenu>
	</div>
</template>
