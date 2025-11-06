<script setup>
import { ref, inject, onMounted, watch } from "vue";
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from "@/components/ui/card";
import { Calendar, ChevronDown, ChevronRight } from "lucide-vue-next";

const emit = defineEmits(["refresh"]);
const formatters = inject("formatters");


defineProps({
	isLoading: Boolean,
	deviceData: Object,
});

const STORAGE_KEY = "rconfig-device-details-collapsed";
const isCollapsed = ref(false);

onMounted(() => {
	isCollapsed.value = localStorage.getItem(STORAGE_KEY) === "true";
});

watch(isCollapsed, (val) => {
	localStorage.setItem(STORAGE_KEY, val.toString());
});

const toggleCollapse = () => {
	isCollapsed.value = !isCollapsed.value;
};
</script>

<template>
	<div>
		<Card class="overflow-hidden">
			<CardHeader class="py-2 px-4 bg-muted/50 cursor-pointer" @click="toggleCollapse">
				<div class="flex flex-row items-center justify-between">
					<div class="grid gap-0.5 flex-1">
						<CardTitle class="flex items-center gap-2 text-lg group">
							<component :is="isCollapsed ? ChevronRight : ChevronDown" class="w-4 h-4" />
							<div>Device Details</div>
						</CardTitle>
					</div>

					<!-- Status section below the header -->
					<div class="mt-3 flex justify-start" v-if="!isLoading && deviceData">
						<!-- Red status - Offline/Error -->
						<div v-if="deviceData.status === 0" class="flex items-center px-2 py-1 rounded-md bg-red-500/10 border border-red-500/20">
							<RcIcon name="status-red" class="mr-2" />
							<span class="text-red-700 dark:text-red-400 text-sm ml-2">Offline</span>
						</div>

						<!-- Green status - Online -->
						<div v-else-if="deviceData.status === 1" class="flex items-center px-2 py-1 rounded-md bg-green-500/10 border border-green-500/20">
							<RcIcon name="status-green" class="mr-2" />
							<span class="text-green-700 dark:text-green-400 text-sm ml-2">Online</span>
						</div>

						<!-- Yellow status - Warning -->
						<div v-else-if="deviceData.status === 2" class="flex items-center px-2 py-1 rounded-md bg-yellow-500/10 border border-yellow-500/20">
							<RcIcon name="status-yellow" class="mr-2" />
							<span class="text-yellow-700 dark:text-yellow-400 text-sm ml-2">Warning</span>
						</div>

						<!-- Gray status - Unknown -->
						<div v-else-if="deviceData.status === 100" class="flex items-center px-2 py-1 rounded-md bg-gray-500/10 border border-gray-500/20">
							<RcIcon name="status-gray" class="mr-2" />
							<span class="text-gray-700 dark:text-gray-400 text-sm ml-2">Disabled</span>
						</div>
					</div>
				</div>
			</CardHeader>

			<transition name="fade">
				<CardContent class="p-4 pt-0 text-sm" v-if="!isCollapsed">
					<div class="space-y-2" v-if="isLoading">
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
					<div class="grid gap-3" v-if="!isLoading && deviceData">
						<dl class="grid gap-3">
							<div class="flex items-center justify-between">
								<dt class="text-muted-foreground">Device ID</dt>
								<dd class="flex items-center gap-2">{{ deviceData.id }}</dd>
							</div>
							<div class="flex items-center justify-between">
								<dt class="text-muted-foreground">Device Name</dt>
								<dd class="flex items-center gap-2">{{ deviceData.device_name }}</dd>
							</div>
							<div class="flex items-center justify-between">
								<dt class="text-muted-foreground">Device IP</dt>
								<dd class="flex items-center gap-2">{{ deviceData.device_ip }}</dd>
							</div>
							<div class="flex items-center justify-between">
								<dt class="text-muted-foreground">Command Group</dt>
								<dd class="flex items-center gap-2">
									<router-link :to="`/commandgroups/${deviceData.category[0]?.id}`" class="rc-text-link">
										<span :class="deviceData.category[0]?.badgeColor ? deviceData.category[0]?.badgeColor : 'bg-gray-600 text-gray-200 border-gray-500'" class="w-fit flex items-center text-xs font-medium px-2.5 py-0.5 rounded-xl border">
											{{ deviceData.category[0]?.categoryName }}
										</span>
									</router-link>
								</dd>
							</div>
							<div class="flex items-center justify-between">
								<dt class="text-muted-foreground">Vendor</dt>
								<dd class="flex items-center gap-2">
									<router-link :to="`/vendors/${deviceData.vendor[0]?.id}`" class="rc-text-link">
										{{ deviceData.vendor[0]?.vendorName }}
									</router-link>
								</dd>
							</div>
							<div class="flex items-center justify-between">
								<dt class="text-muted-foreground">Model</dt>
								<dd class="flex items-center gap-2">{{ deviceData.device_model }}</dd>
							</div>
							<div class="flex items-center justify-between">
								<dt class="text-muted-foreground">Template</dt>
								<dd class="flex items-center gap-2">
									<router-link :to="{ name: 'template-view', params: { id: deviceData.template[0]?.id } }" class="rc-text-link"> {{ deviceData.template[0]?.templateName }} </router-link>
								</dd>
							</div>
							<div class="flex max-w-full items-start gap-2">
								<dt class="w-[6rem] text-muted-foreground shrink-0">Tags</dt>
								<dd class="flex flex-wrap justify-end gap-2 w-full">
									<div class="flex items-center" v-for="tag in deviceData.tag" :key="tag.id">
										<RcIcon name="tag" class="text-muted-foreground mr-1" :height="12" /> <router-link :to="`/tags/${tag?.id}`" class="rc-text-link">{{ tag?.tagname }} </router-link>
									</div>
								</dd>
							</div>

							<div class="flex items-center justify-between">
								<dt class="text-muted-foreground">Created</dt>
								<dd class="flex items-center gap-2">{{ formatters.formatTime(deviceData.created_at) }}</dd>
							</div>
						</dl>
					</div>
				</CardContent>
			</transition>
			<CardFooter class="flex flex-row items-center px-4 py-3 border-t bg-muted/50">
				<div class="flex items-center gap-2 text-xs text-muted-foreground" v-if="!isLoading && deviceData">
					<Calendar class="w-4 h-4 mr-2 opacity-70" />
					Last Poll: {{ formatters.formatTime(deviceData.last_seen) }}
				</div>
			</CardFooter>
		</Card>
	</div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
	transition: opacity 0.2s;
}
.fade-enter-from,
.fade-leave-to {
	opacity: 0;
}
</style>
