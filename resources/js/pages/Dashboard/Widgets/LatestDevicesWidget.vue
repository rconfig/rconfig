<script setup>
import { GripVertical } from "lucide-vue-next";
import { useRouter } from "vue-router";
import { computed } from "vue";

const props = defineProps({
	latestDevices: {
		type: Object,
		required: true,
	},
	isLoadingLatestDevices: {
		type: Boolean,
		required: true,
	},
	editMode: {
		type: Boolean,
		default: false,
	},
});

const router = useRouter();

const devices = computed(() => {
	return props.latestDevices?.data || [];
});

const navigateToDevice = (deviceId) => {
	if (!props.editMode) {
		router.push({ name: 'device-view', params: { id: deviceId } });
	}
};

const formatDate = (dateString) => {
	if (!dateString) return 'N/A';
	const date = new Date(dateString);
	return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
};
</script>

<template>
	<div class="border-0 shadow-md rounded-2xl bg-card text-card-foreground p-4 transition-all duration-200 hover:shadow-lg relative">
		<!-- Drag Handle -->
		<div v-if="editMode" class="absolute top-2 right-2 cursor-move opacity-50 z-10">
			<GripVertical class="w-4 h-4 text-muted-foreground" />
		</div>

		<div class="flex items-center justify-between mb-3">
			<h3 class="text-base font-semibold flex items-center gap-2">
				<RcIcon name="device" class="w-4 h-4" />
				Latest Devices
			</h3>
		</div>

		<!-- Loading State -->
		<div v-if="isLoadingLatestDevices" class="space-y-1 text-sm min-h-[175px]">
			<div v-for="i in 5" :key="i" class="flex items-center justify-between py-1">
				<div class="flex items-center gap-2 flex-1">
					<div class="w-2 h-2 bg-muted rounded-full animate-pulse"></div>
					<div class="flex-1 space-y-0.5">
						<div class="h-3.5 bg-muted rounded w-3/4 animate-pulse"></div>
						<div class="h-3 bg-muted rounded w-1/2 animate-pulse"></div>
					</div>
				</div>
				<div class="h-3.5 bg-muted rounded w-12 animate-pulse"></div>
			</div>
		</div>

		<!-- Actual Content -->
		<div v-else class="space-y-0.5 text-sm min-h-[175px]">
			<div
				v-for="device in devices"
				:key="device.id"
				@click="navigateToDevice(device.id)"
				:class="[
					'flex items-center justify-between py-1',
					editMode ? 'cursor-default' : 'cursor-pointer hover:bg-muted/30 rounded px-2 -mx-2 transition-colors'
				]"
			>
				<div class="flex items-center gap-2 min-w-0 flex-1">
					<div :class="[
						'w-2 h-2 rounded-full flex-shrink-0',
						device.status === 1 ? 'bg-green-500' : 'bg-red-500'
					]"></div>
					<div class="min-w-0 flex-1">
						<p class="font-medium truncate text-sm leading-tight">{{ device.device_name }}</p>
						<p class="text-xs text-muted-foreground truncate leading-tight">{{ device.device_ip }}</p>
					</div>
				</div>
				<div class="flex flex-col items-end flex-shrink-0 gap-0.5">
					<span :class="[
						'text-xs font-medium leading-tight',
						device.status === 1 ? 'text-green-500' : 'text-red-500'
					]">
						{{ device.status === 1 ? 'Up' : 'Down' }}
					</span>
					<span class="text-[10px] text-muted-foreground leading-tight" v-if="device.last_seen">
						{{ formatDate(device.last_seen) }}
					</span>
				</div>
			</div>

			<div v-if="devices.length === 0" class="flex items-center justify-center min-h-[175px]">
				<div class="text-center text-muted-foreground">
					<RcIcon name="device" class="w-8 h-8 mx-auto mb-2 opacity-50" />
					<p class="text-sm">No devices available</p>
				</div>
			</div>
		</div>
	</div>
</template>