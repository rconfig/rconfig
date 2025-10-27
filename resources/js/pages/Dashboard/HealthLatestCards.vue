<script setup>
import { ref } from "vue";
import { HoverCard, HoverCardContent, HoverCardTrigger } from "@/components/ui/hover-card";
import HorizonLogo from "@/pages/Shared/Icon/Icons/HorizonLogo.vue";
import { Archive } from "lucide-vue-next";

const hoverIcons = ref({});
const activeIcons = ref({});
const emit = defineEmits(["refresh"]);

defineProps({
	healthLatest: {
		type: Object,
		required: true,
	},
	isLoadingHealth: {
		type: Boolean,
		required: true,
	},
	SystemUptime: {
		type: String,
	},
});

function refresh() {
	emit("refresh");
}
</script>

<template>
	<div class="xl:col-span-6">
		<div class="border-0 shadow-md rounded-2xl bg-card text-card-foreground p-4 transition-all duration-200 hover:shadow-lg h-fit">
			<div class="flex items-center justify-between mb-3">
				<h3 class="text-lg font-semibold flex items-center gap-2">
					<svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
					</svg>
					Health Status
				</h3>
				<button class="p-2 rounded-lg hover:bg-muted/50 transition-colors" @click="refresh" :disabled="isLoadingHealth">
					<RcIcon name="refresh" size="16" :class="isLoadingHealth ? 'animate-spin' : ''" />
				</button>
			</div>

			<!-- Loading Skeleton for Health Status -->
			<div v-if="isLoadingHealth" class="space-y-3 text-sm min-h-[175px]">
				<div v-for="i in 5" :key="i" class="flex justify-between items-center py-1">
					<div class="flex items-center gap-2">
						<div class="w-4 h-4 bg-muted rounded animate-pulse"></div>
						<div class="h-4 bg-muted rounded w-20 animate-pulse"></div>
					</div>
					<div class="flex items-center gap-2">
						<div class="h-4 bg-muted rounded w-16 animate-pulse"></div>
						<div class="w-2 h-2 bg-muted rounded-full animate-pulse"></div>
					</div>
				</div>
			</div>

			<!-- Actual Health Status Content -->
			<div v-else-if="healthLatest.data" class="space-y-3 text-sm min-h-[175px]">
				<!-- CPU Load -->
				<div class="flex justify-between items-center py-1" v-for="item in healthLatest.data.filter((h) => h.name === 'CpuLoad')" :key="item.name">
					<div class="flex items-center gap-2">
						<svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
						</svg>
						<span class="text-muted-foreground">{{ item.label }}</span>
					</div>
					<div class="flex items-center gap-2">
						<span class="font-medium">{{ item.status }}</span>
						<div class="w-2 h-2 rounded-full bg-green-500"></div>
						<!-- <Button class="h-6 p-1 ml-auto text-muted-foreground" variant="ghost" title="copy raw data" @click="copyItem(item.name, item.status)">
							<RcIcon name="copy-transition" :isActive="activeCopyIcon[item.name]" :size="16" />
						</Button> -->
					</div>
				</div>

				<!-- Horizon Queue -->
				<div class="flex justify-between items-center py-1.5" v-for="item in healthLatest.data.filter((h) => h.name === 'Horizon')" :key="item.name">
					<div class="flex items-center gap-2">
						<HorizonLogo size="18" />
						<span class="text-muted-foreground">{{ item.label }} Queue</span>
					</div>
					<div class="flex items-center gap-2">
						<span class="font-medium">{{ item.status }}</span>
						<div class="w-2 h-2 rounded-full" :class="item.status === 'Running' ? 'bg-green-500' : 'bg-red-500'"></div>
					</div>
				</div>

				<!-- Disk Space -->
				<div class="flex justify-between items-center py-1.5" v-for="item in healthLatest.data.filter((h) => h.name.includes('Disk Space'))" :key="item.name">
					<div class="flex items-center gap-2">
						<svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z" />
						</svg>
						<span class="text-muted-foreground">Diskspace</span>
					</div>
					<div class="flex items-center gap-2">
						<span class="font-medium">{{ item.status }}</span>
						<div class="w-2 h-2 rounded-full" :class="parseInt(item.status) < 80 ? 'bg-green-500' : 'bg-red-500'"></div>
					</div>
				</div>

				<!-- Ping -->
				<div class="flex justify-between items-center py-1.5" v-for="item in healthLatest.data.filter((h) => h.name === 'Ping')" :key="item.name">
					<div class="flex items-center gap-2">
						<svg class="w-4 h-4 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
						</svg>
						<span class="text-muted-foreground">{{ item.label }}</span>
					</div>
					<div class="flex items-center gap-2">
						<span class="font-medium">{{ item.status }}</span>
						<div class="w-2 h-2 rounded-full" :class="item.status === 'Reachable' ? 'bg-green-500' : 'bg-red-500'"></div>
					</div>
				</div>

				<!-- Database -->
				<div class="flex justify-between items-center py-1.5" v-for="item in healthLatest.data.filter((h) => h.name === 'Database')" :key="item.name">
					<div class="flex items-center gap-2">
						<RcIcon name="database" size="18" class="text-blue-500" />
						<span class="text-muted-foreground">{{ item.label }}</span>
					</div>
					<div class="flex items-center gap-2">
						<span class="font-medium">{{ item.status }}</span>
						<div class="w-2 h-2 rounded-full" :class="item.status === 'Ok' ? 'bg-green-500' : 'bg-red-500'"></div>
					</div>
				</div>
			</div>

			<!-- Empty state when no health data -->
			<div v-else class="space-y-3 min-h-[175px] flex items-center justify-center">
				<div class="text-center text-muted-foreground">
					<svg class="w-8 h-8 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
					</svg>
					<p class="text-sm">No health data available</p>
				</div>
			</div>

			<div class="mt-4 pt-3 border-t border-muted/50 text-xs text-muted-foreground flex items-center justify-between">
				<div class="flex items-center gap-2">
					<svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
					</svg>
					<span v-if="!isLoadingHealth">System Uptime: {{ SystemUptime || "Loading..." }}</span>
					<div v-else class="h-3 bg-muted rounded w-24 animate-pulse"></div>
				</div>

				<HoverCard>
					<HoverCardTrigger as-child>
						<button class="flex items-center gap-1 px-2 py-1 rounded-md hover:bg-muted/50 transition-colors text-xs text-muted-foreground hover:text-foreground">
							<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
							</svg>
							Details
						</button>
					</HoverCardTrigger>
					<HoverCardContent class="w-96 bg-gradient-to-r from-card/80 via-card/60 to-card/80 backdrop-blur-md shadow-sm" side="top" align="end">
						<div class="space-y-3">
							<div class="flex items-center gap-2 border-b pb-2">
								<svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
								</svg>
								<h4 class="font-semibold text-sm">Complete Health Status</h4>
							</div>

							<div class="grid grid-cols-1 gap-2 text-xs" v-if="healthLatest.data && !isLoadingHealth">
								<div v-for="item in healthLatest.data" :key="item.name" class="flex justify-between items-center py-1">
									<span class="text-muted-foreground font-medium">{{ item.label }}:</span>
									<div class="flex items-center gap-2">
										<span class="text-right">{{ item.status }}</span>
										<div class="w-2 h-2 rounded-full" :class="['Ok', 'Running', 'Reachable'].includes(item.status) || (item.name.includes('Disk') && parseInt(item.status) < 80) ? 'bg-green-500' : 'bg-red-500'"></div>
									</div>
								</div>
							</div>

							<div v-else class="text-center text-muted-foreground">
								<div v-if="isLoadingHealth" class="space-y-2">
									<div v-for="i in 8" :key="i" class="flex justify-between items-center py-1">
										<div class="h-3 bg-muted rounded w-20 animate-pulse"></div>
										<div class="h-3 bg-muted rounded w-16 animate-pulse"></div>
									</div>
								</div>
								<div v-else>Loading health data...</div>
							</div>
						</div>
					</HoverCardContent>
				</HoverCard>
			</div>
		</div>
	</div>
</template>

<style scoped>
/* Add subtle elevation instead of borders */
.shadow-md {
	box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.dark .shadow-md {
	box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.18);
}

@keyframes pulse {
	0%,
	100% {
		opacity: 1;
	}
	50% {
		opacity: 0.5;
	}
}

.animate-pulse {
	animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
