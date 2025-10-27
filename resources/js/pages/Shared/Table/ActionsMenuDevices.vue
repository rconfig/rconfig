<script setup>
import RcConfirmAlertDialog from "@/pages/Shared/ConfirmAlertDialog/RcConfirmAlertDialog.vue";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuShortcut, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { ref, inject } from "vue";
import { Pencil, Copy, TvMinimalPlay, MonitorOff, Eye, Trash } from "lucide-vue-next";
import { useToaster } from "@/composables/useToaster";
import { useCopy } from "@/composables/useCopy";

const showConfirmDelete = ref(false);
const showConfirmDisable = ref(false);

const emits = defineEmits(["onEdit", "onDelete", "onDisable", "onClone", "onViewDetails", "onEnable", "onRoleAssignment"]);
const { toastSuccess, toastError } = useToaster();
const { copyItem, activeCopyIcon } = useCopy();
const appDirPath = inject("appDirPath", null);

const props = defineProps({
	rowData: {
		type: Object,
		required: true,
	},
	showEditBtn: {
		type: Boolean,
		default: true,
	},
	showViewDetailsBtn: {
		type: Boolean,
		default: false,
	},
	canDelete: {
		type: Boolean,
		default: true,
	},
	isLocked: {
		type: Boolean,
		default: false,
	},
	showRolesBtn: {
		type: Boolean,
		default: false,
	},
	showCopyDownloadDebug: {
		type: Boolean,
		default: false,
	},
});

function handleEdit() {
	emits("onEdit");
}

function handleClone() {
	emits("onClone");
}

function showAlert() {
	showConfirmDelete.value = true;
}

function handleDelete() {
	emits("onDelete");
	showConfirmDelete.value = false;
}

function showDisableConfirm() {
	showConfirmDisable.value = true;
}

function handleDisable() {
	emits("onDisable");
	showConfirmDisable.value = false;
}
function handleEnable() {
	emits("onEnable");
}

function handleViewDetails() {
	emits("onViewDetails");
}

function handleRoleAssignment() {
	emits("onRoleAssignment");
}

function handleShowCopyDownloadDebug() {
	if (!appDirPath) {
		console.error("appDirPath not available");
		return;
	}

	const cmdText = `cd ${appDirPath} && php artisan rconfig:download-device ${props.rowData.id} -d`;

	try {
		copyItem(props.rowData.id, cmdText);
		toastSuccess("Device Download {ID:" + props.rowData.id + "} debug command copied to clipboard");
	} catch (err) {
		console.error("Failed to copy snippet:", err);
		toastError("Failed to copy Device Download debug command");
	}
}
</script>

<template>
	<div>
		<DropdownMenu>
			<DropdownMenuTrigger as-child>
				<Button variant="ghost" class="hover:animate-pulse">
					...
				</Button>
			</DropdownMenuTrigger>
			<DropdownMenuContent class="w-56" align="end" side="bottom">
				<DropdownMenuItem v-if="showCopyDownloadDebug" class="cursor-pointer hover:bg-rcgray-800" @click="handleShowCopyDownloadDebug">
					<span class="text-gray-300 group-hover:text-gray-200">Debug Cmd</span>
					<DropdownMenuShortcut>
						<RcIcon name="commands" size="16" class="text-blue-300" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>

				<DropdownMenuItem v-if="showRolesBtn" class="cursor-pointer hover:bg-rcgray-800" @click="handleRoleAssignment">
					<span>Roles</span>
					<DropdownMenuShortcut>
						<RcIcon name="rbac" size="16" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>
				<DropdownMenuItem v-if="showViewDetailsBtn" class="cursor-pointer hover:bg-rcgray-800" @click="handleViewDetails">
					<span>View Details</span>
					<DropdownMenuShortcut>
						<Eye size="16" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>
				<DropdownMenuItem v-if="showEditBtn" class="cursor-pointer hover:bg-rcgray-800" @click="handleEdit">
					<span>Edit</span>
					<DropdownMenuShortcut>
						<Pencil size="16" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>
				<DropdownMenuItem v-if="showEditBtn" class="cursor-pointer hover:bg-rcgray-800 group" @click="handleClone">
					<span class="text-blue-400 group-hover:text-blue-500">Clone</span>
					<DropdownMenuShortcut>
						<Copy size="16" class="text-blue-400 group-hover:text-blue-500" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>
				<DropdownMenuSeparator v-if="showEditBtn" />
				<DropdownMenuItem v-if="rowData.status === 100" class="cursor-pointer hover:bg-rcgray-800" @click="handleEnable">
					<span>Enable</span>
					<DropdownMenuShortcut>
						<TvMinimalPlay size="16" class="text-green-400" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>
				<DropdownMenuItem v-if="rowData.status != 100" class="cursor-pointer hover:bg-rcgray-800" @click="showDisableConfirm">
					<span>Disable</span>
					<DropdownMenuShortcut>
						<MonitorOff size="16" class="text-red-300" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>
				<DropdownMenuItem v-if="canDelete && !isLocked" class="group cursor-pointer hover:bg-rcgray-800" @click="showAlert">
					<span class="text-red-500 group-hover:text-red-400">Delete</span>
					<DropdownMenuShortcut>
						<Trash size="16" class="text-red-500 group-hover:text-red-400" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>
				<DropdownMenuItem v-if="isLocked" disabled class="cursor-default opacity-70">
					<span class="italic text-slate-500">Vector CM Locked</span>
					<DropdownMenuShortcut>
						<RcIcon name="lock" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>
			</DropdownMenuContent>
		</DropdownMenu>

		<RcConfirmAlertDialog :ids="[rowData.id]" :showConfirmDisable="showConfirmDisable" @close="showConfirmDisable = false" @handleDisable="handleDisable" />

		<RcConfirmAlertDialog :ids="[rowData.id]" :showConfirmDelete="showConfirmDelete" @close="showConfirmDelete = false" @handleDelete="handleDelete" />
	</div>
</template>
