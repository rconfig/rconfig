<script setup>
import DeviceNotificationHoverCard from "@/pages/Inventory/Devices/DeviceView/DeviceNotificationHoverCard.vue";
import axios from "axios";
import { Eye, RefreshCw } from "lucide-vue-next";
import { ref, onMounted, inject } from "vue";
import NoResults from "@/pages/Shared/Table/NoResults.vue";
import { Table, TableBody } from "@/components/ui/table";

const props = defineProps({
	deviceId: Number,
});

const notificationResults = ref([]);
const isLoading = ref(false);
const formatters = inject("formatters");

onMounted(() => {
	getDeviceNotifications();
});

function getDeviceNotifications() {
	isLoading.value = true;
	axios
		.get("/api/activitylogs/last5/" + parseInt(props.deviceId))
		.then((response) => {
			// handle success
			notificationResults.value = response.data;
			isLoading.value = false;
		})
		.catch((error) => {
			// handle error
			console.log(error);
		});
}

function refreshData() {
	isLoading.value = true;
	getDeviceNotifications();
	setTimeout(() => {
		isLoading.value = false;
	}, 1000);
}
</script>

<template>
	<div class="p-2">
		<div class="p-2 overflow-none">
			<div class="flex flex-row items-center justify-start gap-2 pt-1">
				<span class="inline-flex flex-row items-center h-[24px] rounded-md px-1.5 gap-1 shadow-[inset_0_0_0_1px_rgb(69,71,74)] bg-[#313337]">
					<span class="flex-[0_1_auto] min-w-0 overflow-hidden text-ellipsis whitespace-nowrap inline-flex line-clamp-1">Last Events</span>
				</span>
				<span class="flex-1 bg-[rgb(49,51,55)] bg-[rgb(49,51,55)] flex-shrink-0 h-px w-full"></span>
				<Button type="button" :title="'Refresh'" variant="ghost" @click="refreshData" class="p-2 h-7">
					<RefreshCw size="16" :class="isLoading ? 'text-blue-500 animate-spin' : 'text-gray-600 hover:text-blue-500'" />
				</Button>
			</div>

			<div class="mt-4 space-y-2" v-if="isLoading">
				<Skeleton class="w-1/2 h-4" />
				<Skeleton class="w-full h-4" />
				<Skeleton class="w-full h-4" />
				<Skeleton class="w-full h-4" />
				<Skeleton class="w-full h-4" />
				<Skeleton class="w-full h-4" />
				<Skeleton class="w-full h-4" />
				<Skeleton class="w-full h-4" />
				<Skeleton class="w-full h-4" />
			</div>

			<div class="mt-6 space-y-4" v-if="!isLoading">
				<div v-if="Object.keys(notificationResults).length > 0">
					<Button class="flex w-full h-full border bg-rcgray-800 cursor-default hover:bg-rcgray-600 items-left" v-for="(notification, index) in notificationResults" :key="index">
						<div class="w-full">
							<div class="pl-6 border-l border-l-4 rounded-md border-l-rcgray-600">
								<div class="flex justify-between">
									<span class="py-0.5 flex w-fit bg-green-100 text-green-800 text-xs font-medium me-2 px-1.5 py-0.5 rounded-lg dark:bg-blue-900 dark:text-blue-100" v-if="notification.log_name === 'info'">
										{{ notification.log_name }}
									</span>
									<span class="text-muted-foreground">Type: {{ notification.event_type }}</span>
								</div>
								<div class="py-2 space-y-1 text-left">
									<p class="text-sm text-white">
										{{ notification.description }}
									</p>
									<div class="flex items-center justify-between">
										<p class="text-muted-foreground">{{ formatters.formatTime(notification.created_at) }}</p>
										<DeviceNotificationHoverCard :notification="notification">
											<Button :title="'View Raw Data'" :alt="'View Raw Data'" variant="outline" class="h-4 px-1 text-sm text-muted-foreground">
												<Eye size="16" />
											</Button>
										</DeviceNotificationHoverCard>
									</div>
								</div>
							</div>
						</div>
					</Button>
				</div>
				<div v-else>
					<Table>
						<TableBody>
							<NoResults />
						</TableBody>
					</Table>
				</div>
			</div>
			<div class="flex items-center justify-end mt-4 space-x-2">
				<Button variant="outline" class="text-center">
					View All
				</Button>
			</div>
		</div>
	</div>
</template>
