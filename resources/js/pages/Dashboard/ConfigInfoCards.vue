<script setup>
import RcDashboardHovercard from "@/pages/Shared/HoverCards/RcDashboardHovercard.vue";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Clock, ChartColumnIncreasing, AlertTriangle, Activity, TrendingUp } from "lucide-vue-next";
import { inject, computed } from "vue";
import { useRouter } from "vue-router";
import GenericPopover from "@/pages/Shared/Popover/GeneralPopover.vue";
import { useAppStatusBar } from "./useAppStatusBar";

defineProps({
	configinfo: {
		type: Object,
		required: true,
	},
	isLoadingConfiginfo: {
		type: Boolean,
		required: true,
	},
});

const router = useRouter();
const formatters = inject("formatters");

const { licenseData } = useAppStatusBar();

function openDevices() {
	router.push({ name: "devices" });
}

function downDevices() {
	router.push({ name: "devices", query: { statusId: 0 } });
}

function openConfigs() {
	router.push("/configs");
}

function openFailedConfigs() {
	router.push({ name: "configs", query: { statusId: 0 } });
}

function openAlerts() {
	router.push({ name: "alerts" });
}

const isOlderThanOneDay = (dateString) => {
	const configDate = new Date(dateString);
	const now = new Date();
	const oneDayInMs = 24 * 60 * 60 * 1000; // 24 hours in milliseconds

	return now - configDate > oneDayInMs;
};

const isProOrHigher = computed(() => {
	// const testLicenseType = "Professional"; // Change this value to test different scenarios
	// return testLicenseType.toLowerCase().includes("pro") || testLicenseType.toLowerCase().includes("professional");
	return licenseData.plan_type.toLowerCase().includes("pro") || licenseData.plan_type.toLowerCase().includes("professional");
});
</script>

<template>
	<div class="grid gap-3 grid-cols-2 sm:gap-4 md:grid-cols-2 xl:gap-8 lg:grid-cols-3 2xl:grid-cols-6">
		<!-- Updated first card with 1000+ indicator -->
		<Card :class="['border-0 shadow-md rounded-2xl bg-card text-card-foreground hover:bg-muted/20 hover:cursor-pointer hover:shadow-lg transition-all duration-200 relative', configinfo.data && configinfo.data.deviceCount > 0 ? 'border-b border-green-500/50' : '']" @click="openDevices()">
			<!-- Floating indicator for high volume -->
			<div v-if="configinfo.data && configinfo.data.deviceCount > 1000 && isProOrHigher" class="absolute -top-2 -right-2 z-10" @click.stop>
				<GenericPopover :title="'High Volume Device Count'" :description="`You are managing ${configinfo.data.deviceCount} devices, which is considered enterprise scale. Monitor performance and consider resource optimization.  Contact us for License Upgrade options.`" href="/settings/upgrade" linkText="View Upgrade Options" align="start">
					<template #trigger>
						<RcBadge variant="warning" size="sm" class="p-0 w-6 h-6 rounded-full flex items-center justify-center shadow-lg hover:scale-110 transition-transform duration-200 animate-pulse">
							<TrendingUp class="w-3 h-3 text-white" />
						</RcBadge>
					</template>
				</GenericPopover>
			</div>

			<CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
				<CardTitle class="text-sm font-medium">Device Count</CardTitle>
				<RcIcon name="device" class="w-4 h-4" />
			</CardHeader>
			<CardContent>
				<div v-if="configinfo.data">
					<div class="text-2xl font-bold">
						{{ configinfo.data.deviceCount }}
					</div>
					<p class="text-xs text-muted-foreground">Total device count</p>
				</div>

				<div v-if="isLoadingConfiginfo" class="space-y-3">
					<Skeleton class="h-8 w-16" />
					<Skeleton class="h-3 w-full" />
				</div>
			</CardContent>
		</Card>

		<Card :class="['border-0 shadow-md rounded-2xl bg-card text-card-foreground hover:bg-muted/20 hover:cursor-pointer hover:shadow-lg transition-all duration-200', configinfo.data && configinfo.data.deviceDownCount > 0 ? 'border-b border-destructive/50' : '']" @click="downDevices()">
			<CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
				<CardTitle class="text-sm font-medium">Down Devices</CardTitle>
				<RcIcon name="device-red" class="w-4 h-4" />
			</CardHeader>
			<CardContent>
				<div v-if="configinfo.data">
					<div class="text-2xl font-bold">
						{{ configinfo.data.deviceDownCount }}
					</div>
					<p class="text-xs text-muted-foreground">Devices currently down</p>
				</div>
				<div v-if="isLoadingConfiginfo" class="space-y-3">
					<Skeleton class="h-8 w-16" />
					<Skeleton class="h-3 w-full" />
				</div>
			</CardContent>
		</Card>

		<Card class="border-0 shadow-md rounded-2xl bg-card text-card-foreground hover:bg-muted/20 hover:cursor-pointer hover:shadow-lg transition-all duration-200" @click="router.push('/configs')">
			<CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
				<div class="flex items-center gap-2">
					<CardTitle class="text-sm font-medium">Config Files</CardTitle>
					<RcDashboardHovercard>
						<div class="space-y-2">
							<h4 class="text-sm font-semibold">Config Files on Disk</h4>
							<p class="text-xs text-muted-foreground">
								Total number of configuration files stored on disk
							</p>
							<p class="text-xs text-muted-foreground">
								Note: rConfig will keep unchanged config files for historical reference
							</p>
						</div>
					</RcDashboardHovercard>
				</div>
				<ChartColumnIncreasing class="w-4 h-4" />
			</CardHeader>
			<CardContent>
				<div v-if="configinfo.data">
					<div class="text-2xl font-bold">{{ configinfo.data.configFileTotalCount }}</div>
					<p class="text-xs text-muted-foreground">Total config files on disk</p>
				</div>
				<div v-if="isLoadingConfiginfo" class="space-y-3">
					<Skeleton class="h-8 w-24" />
					<Skeleton class="h-3 w-full" />
				</div>
			</CardContent>
		</Card>

		<Card class="border-0 shadow-md rounded-2xl bg-card text-card-foreground hover:bg-muted/20 hover:cursor-pointer hover:shadow-lg transition-all duration-200" @click="router.push('/configs')">
			<CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
				<div class="flex items-center gap-2">
					<CardTitle class="text-sm font-medium">Configurations</CardTitle>
					<RcDashboardHovercard>
						<div class="space-y-2">
							<h4 class="text-sm font-semibold">Config Files on Disk</h4>
							<p class="text-xs text-muted-foreground">
								Number of configuration records in the database
							</p>
							<p class="text-xs text-muted-foreground">
								Note: rConfig will keep unchanged config files for historical reference
							</p>
						</div>
					</RcDashboardHovercard>
				</div>
				<Activity class="w-4 h-4" />
			</CardHeader>
			<CardContent>
				<div v-if="configinfo.data">
					<div class="text-2xl font-bold">{{ configinfo.data.configTotalCount }}</div>
					<p class="text-xs text-muted-foreground">Actual configs in database</p>
				</div>
				<div v-if="isLoadingConfiginfo" class="space-y-3">
					<Skeleton class="h-8 w-20" />
					<Skeleton class="h-3 w-full" />
				</div>
			</CardContent>
		</Card>

		<Card :class="['border-0 shadow-md rounded-2xl bg-card text-card-foreground hover:bg-muted/20 hover:cursor-pointer hover:shadow-lg transition-all duration-200', configinfo.data && configinfo.data.lastConfig && configinfo.data.lastConfig.created_at && isOlderThanOneDay(configinfo.data?.lastConfig?.created_at) ? 'border-b border-warning/50' : '']" @click="openConfigs()">
			<CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
				<div class="flex items-center gap-2">
					<CardTitle class="text-sm font-medium">Last Config Time</CardTitle>
					<RcDashboardHovercard v-if="configinfo.data">
						<div class="space-y-3">
							<div v-if="configinfo.data.lastConfig">
								<div>
									<h4 class="text-sm font-semibold">
										Status:
										<span :class="configinfo.data.lastConfig.created_at && isOlderThanOneDay(configinfo.data?.lastConfig?.created_at) ? 'text-warning' : 'text-green-600'">
											{{ configinfo.data.lastConfig.created_at && isOlderThanOneDay(configinfo.data?.lastConfig?.created_at) ? "Older than one day" : "Recent" }}
										</span>
									</h4>
								</div>
								<div class="space-y-2 text-xs">
									<div class="grid grid-cols-2 gap-1">
										<span class="font-medium">Config ID:</span>
										<span>{{ configinfo.data.lastConfig.id }}</span>
									</div>
									<div class="grid grid-cols-2 gap-1">
										<span class="font-medium">Device:</span>
										<span>{{ configinfo.data.lastConfig.device_name }}</span>
									</div>
									<div class="grid grid-cols-2 gap-1">
										<span class="font-medium">Command:</span>
										<span>{{ configinfo.data.lastConfig.command }}</span>
									</div>
									<div class="grid grid-cols-2 gap-1">
										<span class="font-medium">Filename:</span>
										<span class="truncate">{{ configinfo.data.lastConfig.config_filename }}</span>
									</div>
									<div class="grid grid-cols-2 gap-1">
										<span class="font-medium">File Size:</span>
										<span>{{ configinfo.data.lastConfig.config_filesize }} bytes</span>
									</div>
									<div class="grid grid-cols-2 gap-1">
										<span class="font-medium">Duration:</span>
										<span>{{ configinfo.data.lastConfig.duration }}s</span>
									</div>
									<div class="grid grid-cols-2 gap-1">
										<span class="font-medium">Connection:</span>
										<span>{{ configinfo.data.lastConfig.connection_type }}</span>
									</div>
									<div class="grid grid-cols-2 gap-1">
										<span class="font-medium">Status:</span>
										<span
											:class="{
												'text-red-600 font-medium': configinfo.data.lastConfig.download_status === 0,
												'text-green-600 font-medium': configinfo.data.lastConfig.download_status === 1,
												'text-yellow-600 font-medium': configinfo.data.lastConfig.download_status === 2,
											}"
										>
											{{ configinfo.data.lastConfig.download_status === 0 ? "Error" : configinfo.data.lastConfig.download_status === 1 ? "Good" : "Unknown" }}
										</span>
									</div>
								</div>
							</div>
							<div v-else class="text-center py-4">
								<p class="text-sm text-muted-foreground">No last config available</p>
							</div>
						</div>
					</RcDashboardHovercard>
				</div>
				<Clock class="w-4 h-4" />
			</CardHeader>
			<CardContent>
				<div v-if="configinfo.data && configinfo.data?.lastConfig?.created_at">
					<div class="text-md font-bold min-w-0 mb-2" :class="isOlderThanOneDay(configinfo.data?.lastConfig?.created_at) ? 'text-amber-500' : ''">
						{{ formatters.formatTime(configinfo.data.lastConfig.created_at) }}
					</div>
					<p class="text-xs text-muted-foreground">Last config download</p>
				</div>
				<div v-else-if="configinfo.data && !configinfo.data?.lastConfig?.created_at">
					<div class="text-md font-bold min-w-0 mb-2 text-muted-foreground">--</div>
					<p class="text-xs text-muted-foreground">No config available</p>
				</div>
				<div v-if="isLoadingConfiginfo" class="space-y-3">
					<Skeleton class="h-8 w-20" />
					<Skeleton class="h-3 w-full" />
				</div>
			</CardContent>
		</Card>

		<Card :class="['border-0 shadow-md rounded-2xl bg-card text-card-foreground hover:bg-muted/20 hover:cursor-pointer hover:shadow-lg transition-all duration-200', configinfo.data && configinfo.data.failedConfigCount > 0 ? 'border-b border-destructive/50' : '']" @click="openFailedConfigs()">
			<CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
				<CardTitle class="text-sm font-medium">Failed Configs</CardTitle>
				<AlertTriangle class="w-4 h-4" />
			</CardHeader>
			<CardContent>
				<div v-if="configinfo.data">
					<div class="text-2xl font-bold">
						{{ configinfo.data.failedConfigCount || 0 }}
					</div>
					<p class="text-xs text-muted-foreground">Failed config count</p>
				</div>
				<div v-if="isLoadingConfiginfo" class="space-y-3">
					<Skeleton class="h-8 w-16" />
					<Skeleton class="h-3 w-full" />
				</div>
			</CardContent>
		</Card>
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