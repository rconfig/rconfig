<script setup>
import RcConfirmAlertDialog from "@/pages/Shared/ConfirmAlertDialog/RcConfirmAlertDialog.vue";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuShortcut, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { TrashIcon, Pencil, FileCode2, Power, PowerOff, Eye, Play, Pause, RefreshCcwDot, List } from "lucide-vue-next";
import { ref, inject } from "vue";
import { useToaster } from "@/composables/useToaster";
import { useCopy } from "@/composables/useCopy";

const showConfirmDelete = ref(false);
const emits = defineEmits(["onLatestResults", "onViewDetails", "onViewHistory", "onViewResults", "onEdit", "onDelete", "onTaskPause", "onRunManualTask", "onRunManualPolicy", "onRbacUpdate", "onDownloadEnv", "onEnableDisable", "onRoleAssignment"]);
const { toastSuccess, toastError } = useToaster();
const { copyItem, activeCopyIcon } = useCopy();
const appDirPath = inject("appDirPath", null);

const props = defineProps({
	canDelete: {
		type: Boolean,
		default: true,
	},
	isLocked: {
		type: Boolean,
		default: false,
	},
	rowData: {
		type: Object,
		required: true,
	},
	showLatestResultsBtn: {
		type: Boolean,
		default: false,
	},
	showEditBtn: {
		type: Boolean,
		default: true,
	},
	showViewHistoryBtn: {
		type: Boolean,
		default: false,
	},
	showViewResultsBtn: {
		type: Boolean,
		default: false,
	},
	showViewDetailsBtn: {
		type: Boolean,
		default: false,
	},
	showTaskPauseBtn: {
		type: Boolean,
		default: false,
	},
	taskPaused: {
		type: Boolean,
		default: false,
	},
	showTaskRunNowBtn: {
		type: Boolean,
		default: false,
	},
	showPolicyRunNowBtn: {
		type: Boolean,
		default: false,
	},
	showEditRbacBtn: {
		type: Boolean,
		default: false,
	},
	showAgentEnvBtn: {
		type: Boolean,
		default: false,
	},
	showAgentEnableDisableBtn: {
		type: Boolean,
		default: false,
	},
	showEnableDisableBtn: {
		type: Boolean,
		default: false,
	},
	agentEnableStatus: {
		type: Number,
		default: 0,
	},
	enableStatus: {
		type: [Number, Boolean],
		default: 0,
	},
	showDownloadBtn: {
		type: Boolean,
		default: false,
	},
	showRolesBtn: {
		type: Boolean,
		default: false,
	},
	showCopySnippetDebug: {
		type: Boolean,
		default: false,
	},
	showCopyComplianceDefDebug: {
		type: Boolean,
		default: false,
	},
});

function handleEdit() {
	emits("onEdit");
}

function handleLatestResults() {
	emits("onLatestResults");
}

function handleViewDetails() {
	emits("onViewDetails");
}

function handleViewHistory() {
	emits("onViewHistory");
}

function handleViewResults() {
	emits("onViewResults");
}

function showAlert() {
	showConfirmDelete.value = true;
}

function handleDownload() {
	emits("onDownload");
}

function handleDelete() {
	emits("onDelete");
	showConfirmDelete.value = false;
}

function handleTaskPause() {
	emits("onTaskPause");
}
function handleRunManualTask() {
	emits("onRunManualTask");
}
function handleRunManualPolicy() {
	emits("onRunManualPolicy");
}
function handleRbacUpdate() {
	emits("onRbacUpdate");
}
function handleDownloadEnv() {
	emits("onDownloadEnv");
}
function handleEnableDisable() {
	emits("onEnableDisable");
}
function handleRoleAssignment() {
	emits("onRoleAssignment");
}
function handleShowCopySnippetDebug() {
	if (!appDirPath) {
		console.error("appDirPath not available");
		return;
	}

	const cmdText = `cd ${appDirPath} && php artisan rconfig:send-snippet ${props.rowData.id} DeviceId -d`;

	try {
		copyItem(props.rowData.id, cmdText);
		toastSuccess("Snippet  {ID:" + props.rowData.id + "} debug command copied to clipboard");
	} catch (err) {
		console.error("Failed to copy snippet:", err);
		toastError("Failed to copy snippet debug command");
	}
}
function handleShowCopyComplianceDefDebug() {
	if (!appDirPath) {
		console.error("appDirPath not available");
		return;
	}

	const cmdText = `cd ${appDirPath} && php artisan rconfig:policy-definition-validation ${props.rowData.id} ConfigId`;

	try {
		copyItem(props.rowData.id, cmdText);
		toastSuccess("Compliance Definition {ID:" + props.rowData.id + "} debug command copied to clipboard");
	} catch (err) {
		console.error("Failed to copy compliance definition:", err);
		toastError("Failed to copy compliance definition debug command");
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
				<DropdownMenuItem v-if="showCopySnippetDebug" class="cursor-pointer hover:bg-rcgray-800" @click="handleShowCopySnippetDebug">
					<span class="text-gray-300 group-hover:text-gray-200">Debug Cmd</span>
					<DropdownMenuShortcut>
						<RcIcon name="commands" size="16" class="text-blue-300" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>

				<DropdownMenuItem v-if="showCopyComplianceDefDebug" class="cursor-pointer hover:bg-rcgray-800" @click="handleShowCopyComplianceDefDebug">
					<span class="text-gray-300 group-hover:text-gray-200">Debug Cmd</span>
					<DropdownMenuShortcut>
						<RcIcon name="commands" size="16" class="text-blue-300" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>

				<DropdownMenuItem v-if="showViewDetailsBtn" class="group cursor-pointer hover:bg-rcgray-800" @click="handleViewDetails">
					<span class="text-gray-300 group-hover:text-gray-200">View Details</span>
					<DropdownMenuShortcut>
						<Eye size="16" class="text-blue-400 group-hover:text-blue-300" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>
				<DropdownMenuItem v-if="showViewHistoryBtn" class="group cursor-pointer hover:bg-rcgray-800" @click="handleViewHistory">
					<span class="text-gray-300 group-hover:text-gray-200">View History</span>
					<DropdownMenuShortcut>
						<Eye size="16" class="text-blue-400 group-hover:text-blue-300" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>

				<DropdownMenuItem v-if="showLatestResultsBtn" class="cursor-pointer hover:bg-rcgray-800" @click="handleLatestResults">
					<span class="text-gray-300 group-hover:text-gray-200">Latest Results</span>
					<DropdownMenuShortcut>
						<List size="16" class="text-green-500 text-green hover:animate-spin" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>

				<DropdownMenuItem v-if="showViewResultsBtn" class="group cursor-pointer hover:bg-rcgray-800" @click="handleViewResults">
					<span class="text-gray-300 group-hover:text-gray-200">View Results</span>
					<DropdownMenuShortcut>
						<Eye size="16" class="text-blue-400 group-hover:text-blue-300" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>
				<DropdownMenuItem v-if="showTaskRunNowBtn" class="cursor-pointer hover:bg-rcgray-800" @click="handleRunManualTask">
					<span class="text-gray-300 group-hover:text-gray-200">Run now</span>
					<DropdownMenuShortcut>
						<Play size="16" class="animate-pulse text-yellow-300" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>
				<DropdownMenuItem v-if="showPolicyRunNowBtn" class="cursor-pointer hover:bg-rcgray-800" @click="handleRunManualPolicy">
					<span class="text-gray-300 group-hover:text-gray-200">Run now</span>
					<DropdownMenuShortcut>
						<Play size="16" class="animate-pulse text-yellow-300" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>

				<DropdownMenuItem v-if="showEditRbacBtn" class="group cursor-pointer hover:bg-rcgray-800" @click="handleRbacUpdate">
					<!-- CHANGE USER ROLE -->
					<span class="text-gray-300 group-hover:text-gray-200">Change Role</span>
					<DropdownMenuShortcut>
						<RcIcon name="rbac" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>

				<DropdownMenuItem v-if="showRolesBtn" class="cursor-pointer hover:bg-rcgray-800" @click="handleRoleAssignment">
					<!-- UPDATE ROLES FOR DEVICES, TAGS, SNIPPETS, AGENTS -->
					<span class="text-gray-300 group-hover:text-gray-200">Roles</span>
					<DropdownMenuShortcut>
						<RcIcon name="rbac" size="16" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>

				<DropdownMenuItem v-if="showTaskPauseBtn && !taskPaused" class="cursor-pointer hover:bg-rcgray-800" @click="handleTaskPause">
					<span class="text-gray-300 group-hover:text-gray-200">Pause</span>
					<DropdownMenuShortcut>
						<Pause size="16" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>

				<DropdownMenuItem v-if="showTaskPauseBtn && taskPaused" class="cursor-pointer hover:bg-rcgray-800" @click="handleTaskPause">
					<span class="text-gray-300 group-hover:text-gray-200">Resume</span>
					<DropdownMenuShortcut>
						<RefreshCcwDot size="16" class="text-green-500 text-green hover:animate-spin" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>

				<!-- AGENTS MENU ITEMS -->
				<DropdownMenuItem v-if="showAgentEnvBtn" class="cursor-pointer hover:bg-rcgray-800" @click="handleDownloadEnv">
					<span class="text-gray-300 group-hover:text-gray-200">Download .ENV</span>
					<DropdownMenuShortcut>
						<FileCode2 class="text-blue-400" size="18" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>
				<DropdownMenuItem v-if="showAgentEnableDisableBtn" class="cursor-pointer hover:bg-rcgray-800" @click="handleEnableDisable()">
					<span v-if="!agentEnableStatus">Enable</span>
					<span v-if="agentEnableStatus">Disable</span>
					&nbsp;Agent
					<DropdownMenuShortcut>
						<Power v-if="!agentEnableStatus" class="text-green-400" size="18" />
						<PowerOff v-if="agentEnableStatus" class="text-red-400" size="18" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>
				<!-- AGENTS MENU ITEMS -->

				<DropdownMenuItem v-if="showEnableDisableBtn" class="cursor-pointer hover:bg-rcgray-800" @click="handleEnableDisable()">
					<span v-if="!enableStatus">Enable</span>
					<span v-if="enableStatus">Disable</span>
					<DropdownMenuShortcut>
						<Power v-if="!enableStatus" class="text-green-400" size="18" />
						<PowerOff v-if="enableStatus" class="text-red-400" size="18" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>

				<DropdownMenuItem v-if="showEditBtn" class="group cursor-pointer hover:bg-rcgray-800" @click="handleEdit">
					<span class="text-gray-300 group-hover:text-gray-200">Edit</span>
					<DropdownMenuShortcut>
						<Pencil size="16" class="text-gray-300 group-hover:text-gray-100" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>

				<DropdownMenuItem v-if="showDownloadBtn" class="cursor-pointer hover:bg-rcgray-800" @click="handleDownload">
					<span class="text-gray-300 group-hover:text-gray-200">Download</span>
					<DropdownMenuShortcut>
						<RcIcon name="download" size="16" class="text-blue-300" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>

				<DropdownMenuSeparator v-if="showEditBtn" />
				<DropdownMenuItem v-if="canDelete && !isLocked" class="group cursor-pointer hover:bg-rcgray-800" @click="showAlert">
					<span class="text-red-500 group-hover:text-red-400">Delete</span>
					<DropdownMenuShortcut>
						<TrashIcon size="16" class="text-red-500 group-hover:text-red-400" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>
				<DropdownMenuItem v-if="isLocked" disabled class="cursor-default">
					<span class="italic text-slate-500">Vector CM Locked</span>
					<DropdownMenuShortcut>
						<RcIcon name="lock" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>
			</DropdownMenuContent>
		</DropdownMenu>

		<RcConfirmAlertDialog v-if="showConfirmDelete" :showConfirmDelete="showConfirmDelete" @close="showConfirmDelete = false" @handleDelete="handleDelete" :ids="[rowData.id]" />
	</div>
</template>
