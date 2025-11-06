<script setup>
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuShortcut, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { MoreVertical, Edit, Copy, Trash2, FileX, TvMinimalPlay, MonitorOff, Trash, Eye } from "lucide-vue-next";
import { useRouter } from "vue-router"; // Added missing useRouter import

const router = useRouter();

const emits = defineEmits(["openDeviceEdit", "onDelete", "onPurge", "openDeviceClone", "onDisable", "onEnable"]);

const props = defineProps({
	rowData: {
		type: Object,
		required: true,
	},
	type: {
		type: String,
		default: "device",
	},
	showEditBtn: {
		type: Boolean,
		default: true,
	},
});

function handleDelete() {
	emits("onDelete");
}

function openDeviceEdit() {
	emits("openDeviceEdit");
}

function openDeviceClone() {
	emits("openDeviceClone");
}

function handleDisable() {
	emits("onDisable");
}
function handleEnable() {
	emits("onEnable");
}

function onPurge() {
	router.push({
		name: "settings-data-purge",
	});
	console.log("Purging failed configs for device:", props.rowData.id);
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
				<DropdownMenuItem v-if="showEditBtn" class="cursor-pointer hover:bg-rcgray-600 group" @click="openDeviceEdit()">
					<span>Edit Device</span>
					<DropdownMenuShortcut class="group">
						<Edit size="16" class="text-rcgray-400 group-hover:text-rcgray-300" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>
				<DropdownMenuItem v-if="showEditBtn" class="cursor-pointer hover:bg-rcgray-600 group" @click="openDeviceClone()">
					<span>Clone Device</span>
					<DropdownMenuShortcut>
						<Copy size="16" class="text-rcgray-400 group-hover:text-rcgray-300" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>
				<DropdownMenuItem v-if="rowData.status === 100" class="cursor-pointer hover:bg-rcgray-800" @click="handleEnable">
					<span>Enable</span>
					<DropdownMenuShortcut>
						<TvMinimalPlay size="16" class="text-green-400" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>
				<DropdownMenuItem v-if="rowData.status != 100" class="cursor-pointer hover:bg-rcgray-800" @click="handleDisable">
					<span>Disable</span>
					<DropdownMenuShortcut>
						<MonitorOff size="16" class="text-red-300" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>
				<DropdownMenuItem v-if="showEditBtn" class="cursor-pointer hover:bg-rcgray-600 group" @click="onPurge()">
					<span class="text-amber-500 group-hover:text-amber-400">Purge Failed Configs</span>
					<DropdownMenuShortcut>
						<FileX size="16" class="text-amber-500 group-hover:text-amber-400" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>
				<DropdownMenuSeparator v-if="showEditBtn" />
				<DropdownMenuItem class="cursor-pointer hover:bg-rcgray-600" @click="handleDelete()">
					<span class="text-red-400">Delete</span>
					<DropdownMenuShortcut>
						<Trash2 size="16" class="text-red-500" />
					</DropdownMenuShortcut>
				</DropdownMenuItem>
			</DropdownMenuContent>
		</DropdownMenu>
	</div>
</template>
