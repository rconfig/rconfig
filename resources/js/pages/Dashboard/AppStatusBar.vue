<script setup>
import GenericPopover from "@/pages/Shared/Popover/GeneralPopover.vue";
import PortalNotification from "./PortalNotification.vue";
import { BadgeCheck } from "lucide-vue-next";
import { Code, Shield, Info, Lock } from "lucide-vue-next";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { useAppStatusBar } from "./useAppStatusBar";
import { computed } from "vue";

const props = defineProps({
	deviceCnt: {
		type: Number,
		required: false,
		default: 0,
	},
});

// Use the simplified composable
const {
	// Data
	loading,
	currentVersion,
	latestVersion,
	licenseData,
	connectivityData,
	activeNotice,

	// Configurations
	versionConfig,
	internetConfig,
	subscriptionConfig,

	// HTTPS notification
	isHttpsDisabled,
	showHttpsPopover,
	openHttpsPopover,
	closeHttpsPopover,

	// Methods
	handleRefresh,
	handleUpdate,
	dismissNotice,

	// Utils
	formatters,
} = useAppStatusBar();

// Computed property for device count status (specific check)
const deviceCountStatus = computed(() => {
	// If no license data available, return neutral
	if (!licenseData.value || !licenseData.value.device_count) {
		return {
			color: "text-gray-500",
			variant: "secondary",
			status: "unknown",
		};
	}

	const isWithinDeviceLimit = props.deviceCnt <= licenseData.value.device_count;

	if (isWithinDeviceLimit) {
		return {
			color: "text-green-600 dark:text-green-400",
			variant: "success",
			status: "good",
		};
	} else {
		return {
			color: "text-red-600 dark:text-red-400",
			variant: "danger",
			status: "exceeded",
		};
	}
});

// Computed property for aggregate license status (overall health)
const licenseStatus = computed(() => {
	// If no license data available, return neutral
	if (!licenseData.value) {
		return {
			color: "text-gray-500",
			variant: "secondary",
			status: "unknown",
		};
	}

	const isSubscriptionActive = subscriptionConfig.value.active;
	const isDeviceCountGood = deviceCountStatus.value.status === "good";

	// Add more checks here in the future:
	// const isExpiryGood = checkExpiryStatus();
	// const isFeatureAccessGood = checkFeatureAccess();

	// All conditions good = green
	if (isSubscriptionActive && isDeviceCountGood) {
		return {
			color: "text-green-600 dark:text-green-400",
			variant: "success",
			status: "excellent",
		};
	}

	// All conditions bad = red
	if (!isSubscriptionActive && !isDeviceCountGood) {
		return {
			color: "text-red-600 dark:text-red-400",
			variant: "danger",
			status: "critical",
		};
	}

	// Mixed conditions = amber
	return {
		color: "text-amber-600 dark:text-amber-400",
		variant: "warning",
		status: "warning",
	};
});
</script>

<template>
	<div class="bg-gradient-to-r from-card/80 via-card/60 to-card/80 backdrop-blur-md shadow-sm">
		<header class="flex items-center justify-between w-full h-8">
			<!-- Version Information -->
			<div class="flex items-center gap-4">
				<Skeleton v-if="loading" class="h-5 w-20 rounded-md" />
				<Skeleton v-if="loading" class="h-5 w-20 rounded-md" />

				<template v-else-if="currentVersion">
					<!-- Version Display -->
					<Popover>
						<PopoverTrigger asChild>
							<div class="gap-1.5 px-2 py-1 flex items-center gap-3 cursor-pointer hover:bg-muted/50 transition-colors rounded-lg">
								<div class="flex items-center gap-2 px-2 py-1 bg-muted/50 rounded-lg border">
									<Code size="18" class="text-primary/80" />
									<span class="text-xs font-medium text-foreground">{{ currentVersion }}</span>
								</div>
							</div>
						</PopoverTrigger>
						<PopoverContent class="w-64 p-4 shadow-lg border bg-card/95 backdrop-blur-xl" align="start">
							<div class="space-y-3">
								<div class="text-xs font-medium text-foreground border-b pb-2">Version Information</div>
								<div class="space-y-2 text-sm">
									<div class="flex justify-between">
										<span class="text-muted-foreground">Current Version</span>
										<span class="font-medium">{{ currentVersion }}</span>
									</div>
									<div class="flex justify-between">
										<span class="text-muted-foreground">Latest Version</span>
										<span class="font-medium">{{ latestVersion || currentVersion }}</span>
									</div>
								</div>
							</div>
						</PopoverContent>
					</Popover>

					<!-- Version Status Badge -->

					<div class="flex items-center gap-3">
						<RcBadge :variant="versionConfig.variant" size="large" class="flex items-center gap-1.5 px-2 py-1" @click="handleUpdate">
							<component :is="versionConfig.icon" size="14" />
							<span class="text-xs font-medium">{{ versionConfig.text }}</span>
						</RcBadge>

						<!-- Portal Notification -->
						<PortalNotification />
					</div>
				</template>
			</div>

			<!-- Right Side: rConfig Portal Status + Subscription Information -->
			<div class="flex items-center gap-3">
				<Skeleton v-if="loading && isHttpsDisabled" class="h-5 w-20 rounded-md" />

				<!-- HTTPS Disabled Notification -->
				<GenericPopover v-if="!loading && isHttpsDisabled" title="HTTPS Disabled" description="HTTPS is currently disabled. For security, we recommend enabling HTTPS." :hasLink="true" :align="'end'" :href="$rconfigDocsUrl + '/other/ssl-setup/'">
					<template #trigger>
						<div class="flex items-center gap-1 px-2 py-1 bg-red-100 dark:bg-red-900/30 rounded-md border border-red-300 dark:border-red-700 cursor-pointer hover:bg-red-200 transition-colors">
							<Lock size="14" class="text-red-600 dark:text-red-400 animate-pulse" />
							<span class="text-xs font-semibold text-red-700 dark:text-red-400">HTTPS Disabled</span>
						</div>
					</template>
				</GenericPopover>

				<GenericPopover v-else title="HTTPS Enabled" description="HTTPS is enabled and your connection is secure." :hasLink="false" :align="'end'">
					<template #trigger>
						<div class="flex items-center gap-1 px-2 py-1 bg-green-100 dark:bg-green-900/30 rounded-md border border-green-300 dark:border-green-700 cursor-pointer hover:bg-green-200 transition-colors">
							<Lock size="14" class="text-green-600 dark:text-green-400 animate-pulse" />
						</div>
					</template>
				</GenericPopover>

				<Skeleton v-if="loading" class="h-5 w-20 rounded-md" />

				<!-- rConfig Portal Status -->
				<div v-else>
					<Popover>
						<PopoverTrigger asChild>
							<div class="flex items-center gap-2 px-2 py-1 bg-muted/50 rounded-lg border cursor-pointer hover:bg-muted/80 transition-colors">
								<component :is="internetConfig.icon" size="16" :class="internetConfig.class" />
								<span class="text-xs font-medium" :class="internetConfig.class">
									{{ internetConfig.text }}
								</span>
							</div>
						</PopoverTrigger>

						<PopoverContent class="w-72 p-0 shadow-lg border bg-card/95 backdrop-blur-xl" align="end">
							<div class="p-4 space-y-3">
								<div class="flex items-center justify-between text-sm font-semibold text-foreground border-b pb-2">
									<span>rConfig Portal Connectivity</span>
									<component :is="internetConfig.icon" size="16" :class="internetConfig.class" />
								</div>

								<div class="space-y-2 text-sm">
									<div class="flex justify-between">
										<span class="text-muted-foreground">Status</span>
										<span class="font-medium" :class="internetConfig.class">{{ internetConfig.text }}</span>
									</div>

									<!-- Response Time (if available) -->
									<div v-if="connectivityData?.rconfig?.response_time_ms" class="flex justify-between">
										<span class="text-muted-foreground">Response Time</span>
										<span class="font-medium">{{ connectivityData.rconfig.response_time_ms }}ms</span>
									</div>

									<!-- HTTP Status Code (if available) -->
									<div v-if="connectivityData?.rconfig?.status_code" class="flex justify-between">
										<span class="text-muted-foreground">HTTP Status</span>
										<span class="font-medium">{{ connectivityData.rconfig.status_code }}</span>
									</div>

									<!-- Last Checked -->
									<div v-if="connectivityData?.rconfig?.last_checked" class="flex justify-between">
										<span class="text-muted-foreground">Last Checked</span>
										<span class="font-medium text-xs">{{ new Date(connectivityData.rconfig.last_checked).toLocaleTimeString() }}</span>
									</div>

									<!-- Feedback Status -->
									<div class="flex justify-between">
										<span class="text-muted-foreground">Feedback Service</span>
										<span class="font-medium" :class="connectivityData?.feedback_enabled ? 'text-green-500' : 'text-red-500'">
											{{ connectivityData?.feedback_enabled ? "Enabled" : "Disabled" }}
										</span>
									</div>

									<!-- Manual Override Info -->
									<div v-if="connectivityData?.manual_override?.active" class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-md border border-amber-200 dark:border-amber-800">
										<div class="text-xs text-amber-700 dark:text-amber-300 font-medium mb-1">Manual Override</div>
										<div class="text-xs text-amber-600 dark:text-amber-400">
											{{ connectivityData.manual_override.details?.reason || "Override Active" }}
										</div>
										<div class="text-xs text-amber-500 dark:text-amber-400 mt-1">Set By: {{ connectivityData.manual_override.details?.set_by || "Unknown" }}</div>
									</div>

									<!-- Environment Overrides Info -->
									<div v-if="connectivityData?.environment_overrides?.checks_disabled || connectivityData?.environment_overrides?.force_offline || connectivityData?.environment_overrides?.force_online" class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-md border border-blue-200 dark:border-blue-800">
										<div class="text-xs text-blue-700 dark:text-blue-300 font-medium mb-1">Environment Override</div>
										<div class="text-xs text-blue-600 dark:text-blue-400 space-y-1">
											<div v-if="connectivityData.environment_overrides.checks_disabled">• Checks Disabled</div>
											<div v-if="connectivityData.environment_overrides.force_offline">• Forced Offline</div>
											<div v-if="connectivityData.environment_overrides.force_online">• Forced Online</div>
										</div>
									</div>
								</div>
							</div>

							<!-- Footer Link -->
							<div class="border-t bg-muted/20 px-4 py-3">
								<div class="flex items-center justify-center">
									<router-link to="/settings/system" class="text-muted-foreground hover:text-foreground transition-colors flex items-center gap-1 text-xs">
										<Shield size="12" />
										Connectivity Settings
									</router-link>
								</div>
							</div>
						</PopoverContent>
					</Popover>
				</div>

				<!-- Subscription Status -->
				<Skeleton v-if="loading" class="h-5 w-20 rounded-md" />

				<div v-else-if="licenseData" class="flex items-center gap-3">
					<Popover>
						<PopoverTrigger asChild>
							<div class="flex items-center gap-2 px-2 py-1 bg-muted/50 rounded-lg border cursor-pointer hover:bg-muted/80 transition-colors">
								<component :is="subscriptionConfig.icon" size="16" :class="subscriptionConfig.color" />
								<span class="text-xs font-medium" :class="subscriptionConfig.color">License: {{ subscriptionConfig.text }}</span>

								<!-- Active Indicator - now uses aggregate licenseStatus -->
								<div class="flex items-center ml-1">
									<div
										class="w-2.5 h-2.5 rounded-full shadow-sm"
										:class="{
											'bg-green-500 shadow-green-500/50': licenseStatus.status === 'excellent',
											'bg-amber-500 shadow-amber-500/50': licenseStatus.status === 'warning',
											'bg-red-500 shadow-red-500/50': licenseStatus.status === 'critical',
											'bg-gray-500 shadow-gray-500/50': licenseStatus.status === 'unknown',
										}"
									/>
								</div>
							</div>
						</PopoverTrigger>

						<PopoverContent class="w-72 p-4 shadow-lg border bg-card/95 backdrop-blur-xl pb-0" align="end">
							<!-- License Information Section -->
							<div class="space-y-3">
								<div class="flex items-center justify-between text-sm font-semibold text-foreground border-b pb-2">
									<span>License Information</span>
									<GenericPopover title="License Compatibility" description="rConfig V8 licenses are fully backward compatible - existing V6 and V7 license holders can use V8 with their current licenses." :hasLink="false" :align="'end'">
										<template #trigger>
											<Button variant="link" size="sm" class="text-blue-400 hover:text-blue-300 p-0">
												<BadgeCheck class="h-4 w-4 ml-1 animate-pulse text-green-500" />
											</Button>
										</template>
									</GenericPopover>
								</div>
								<div class="space-y-2 pb-2 text-sm">
									<div class="flex justify-between">
										<span class="text-muted-foreground">Plan</span>
										<div class="flex items-center gap-2">
											<span class="font-medium" :class="subscriptionConfig.color">{{ subscriptionConfig.text }}</span>
											<div
												class="w-2 h-2 rounded-full"
												:class="{
													'bg-green-500': subscriptionConfig.active,
													'bg-red-500': !subscriptionConfig.active,
												}"
											/>
										</div>
									</div>
									<div class="flex justify-between">
										<span class="text-muted-foreground">Status</span>
										<span
											class="font-medium"
											:class="{
												'text-green-600 dark:text-green-400': subscriptionConfig.active,
												'text-red-600 dark:text-red-400': !subscriptionConfig.active,
											}"
										>
											{{ subscriptionConfig.active ? "Active" : "Inactive" }}
										</span>
									</div>
									<div v-if="licenseData.sub_id" class="flex justify-between">
										<span class="text-muted-foreground">Subscription ID</span>
										<span class="font-medium text-xs font-mono bg-muted/50 px-2 py-1 rounded">
											{{ licenseData.sub_id }}
										</span>
									</div>
									<div v-if="licenseData.sub_name" class="flex justify-between">
										<span class="text-muted-foreground">Subscription Name</span>
										<span class="font-medium">{{ licenseData.sub_name }}</span>
									</div>
									<div v-if="licenseData.device_count" class="flex justify-between">
										<span class="text-muted-foreground">Device Count</span>
										<span class="font-medium" :class="deviceCountStatus.color"> {{ props.deviceCnt || 0 }}/{{ licenseData.device_count }} </span>
									</div>
									<div v-if="licenseData.expiry && licenseData.expiry !== '1970-01-01 00:00:00'" class="flex justify-between">
										<span class="text-muted-foreground">Expiry</span>
										<span class="font-medium text-xs">
											{{ formatters.formatTime(licenseData.expiry, true) }}
										</span>
									</div>

									<!-- Error Display -->
									<RcBadge v-if="subscriptionConfig.error" class="text-xs px-2 py-1 my-2" variant="danger">
										{{ subscriptionConfig.error }}
									</RcBadge>
								</div>
							</div>

							<!-- Footer Links -->
							<div class="border-t bg-muted/20 px-4 py-3 mt-2">
								<div class="flex items-center justify-between text-xs">
									<router-link to="/settings" class="text-muted-foreground hover:text-foreground transition-colors flex items-center gap-1">
										<RcIcon name="settings" size="12" />
										Settings
									</router-link>
									<router-link to="/settings/about" class="text-muted-foreground hover:text-foreground transition-colors flex items-center gap-1">
										<Info size="12" />
										About
									</router-link>
									<a href="https://www.rconfig.com/licenses" target="_blank" class="text-muted-foreground hover:text-foreground transition-colors flex items-center gap-1">
										<Shield size="12" />
										License
									</a>
								</div>
							</div>
						</PopoverContent>
					</Popover>
				</div>

				<!-- Refresh Button -->
				<Button variant="ghost" size="sm" class="h-8 w-8 p-0 hover:bg-muted/80 transition-colors" :disabled="loading" @click="handleRefresh" title="Refresh System Info">
					<RcIcon name="refresh" size="14" class="text-muted-foreground/80" :class="{ 'animate-spin': loading }" />
				</Button>
			</div>
		</header>
	</div>
</template>