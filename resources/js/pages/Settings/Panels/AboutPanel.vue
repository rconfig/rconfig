<script setup>
import AlertTip from "@/pages/Shared/Alerts/AlertTip.vue";
import GenericPopover from "@/pages/Shared/Popover/GeneralPopover.vue";
import Loading from "@/pages/Shared/Loaders/Loading.vue";
import RcBadge from "@/pages/Shared/Badges/RcBadge.vue";
import { onMounted, computed, inject } from "vue";
import { useAppStatusBar } from "@/pages/Dashboard/useAppStatusBar";
import { useDashboard } from "@/pages/Dashboard/useDashboard";
import { useLicenseInfoStore } from "@/stores/useLicenseInfoStore";
import { TrendingUp } from "lucide-vue-next";

// Import Lucide Vue Next icons
import { FileText, Headphones, HelpCircle, Crown, Zap, AlertCircle } from "lucide-vue-next";

const store = useLicenseInfoStore();
const formatters = inject("formatters");

// Computed properties for subscription config
const subscriptionConfig = computed(() => {
	const sub = store.subscriptionStatus;

	const configs = {
		professional: { icon: Crown, color: "text-blue-600 dark:text-blue-400" },
		enterprise: { icon: Zap, color: "text-purple-600 dark:text-purple-400" },
		vector: { icon: Crown, color: "text-blue-600 dark:text-blue-400" },
	};

	const config = configs[sub.type] || configs.professional;

	if (sub.error) {
		return {
			icon: AlertCircle,
			color: "text-red-600 dark:text-red-400",
			text: "License Error",
			active: false,
			error: sub.error,
		};
	}

	return {
		...config,
		text: formatters.capitalizeFirstLetter(sub.text),
		active: sub.active,
	};
});

const image = computed(() => "/images/brand/rconfig-with-strap-white.png");

const handleRefreshClick = () => store.fetchAll();

const { licenseData } = useAppStatusBar();
const { fetchConfiginfo, configinfo } = useDashboard();

onMounted(() => {
	// Ensure the license info is fetched when the component is mounted
	store.fetchAll();
	store.startAutoRefresh();
	fetchConfiginfo();
});

const isProOrHigher = computed(() => {
	// const testLicenseType = "Professional"; // Change this value to test different scenarios
	// return testLicenseType.toLowerCase().includes("pro") || testLicenseType.toLowerCase().includes("professional");
	return licenseData.plan_type.toLowerCase().includes("pro") || licenseData.plan_type.toLowerCase().includes("professional");
});

// Computed property for device count status (specific check)
const deviceCountStatus = computed(() => {
	const licenseDeviceCount = store.data.license?.data?.device_count;
	const currentDeviceCount = configinfo.data?.deviceCount || 0;

	// If no license data available, return neutral
	if (!licenseDeviceCount) {
		return {
			color: "text-gray-500",
			variant: "secondary",
			status: "unknown",
		};
	}

	const isWithinDeviceLimit = currentDeviceCount <= licenseDeviceCount;

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
	if (!store.data.license?.data) {
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
	<div class="flex justify-center w-full">
		<div class="flex flex-col items-center w-full gap-4 md:w-3/4">
			<div class="grid w-full max-w-full items-center gap-1.5">
				<div class="flex justify-start w-full mb-8">
					<img :src="image" alt="rConfig Logo" class="w-full max-w-xs" />
				</div>

				<h3 class="mb-4 text-2xl font-semibold leading-7 tracking-tight font-inter">About rConfig</h3>
				<p class="text-sm">rConfig is a powerful and user-friendly network configuration management tool designed to help you efficiently manage and automate your network devices. Stay organized, save time, and ensure consistency across your network infrastructure with rConfig.</p>

				<div class="flex items-center w-full justify-end px-4">
					<GenericPopover v-if="configinfo.data && configinfo.data.deviceCount > 1000 && isProOrHigher" title="High Volume Device Count" :description="`You are managing ${configinfo.data.deviceCount} devices, which is considered enterprise scale. Consider upgrading to Professional or Enterprise license for better performance and additional features.`" :hasLink="true" href="/settings/upgrade" linkText="View Upgrade Options" align="end">
						<template #trigger>
							<Button variant="link" size="sm" class="p-0 w-6 h-6 bg-amber-500 rounded-full flex items-center justify-center shadow-lg hover:scale-110 transition-transform duration-200 animate-pulse">
								<TrendingUp class="w-3 h-3 text-white" />
							</Button>
						</template>
					</GenericPopover>
					<!-- Refresh Button -->
					<Button variant="ghost" size="sm" class="ml-4 mt-4 h-8 w-8 p-0 hover:bg-muted/80 transition-colors" :disabled="store.loading" @click="handleRefreshClick" title="Refresh system information"> Refresh: <RcIcon name="refresh" size="14" class="ml-2 text-muted-foreground/80" :class="{ 'animate-spin': store.loading }" /> </Button>
				</div>
				<div v-if="store.loading" class="flex items-center justify-center w-full">
					<Loading />
				</div>

				<RcBadge class="text-xs px-2 py-1 my-4" variant="danger" v-if="!store.loading && subscriptionConfig.error">
					{{ subscriptionConfig.error }}
				</RcBadge>

				<div class="grid gap-3 mt-2 p-4 border" v-if="!store.loading">
					<dl class="grid gap-3">
						<div class="flex items-center justify-between">
							<dt class="text-muted-foreground">Application Version</dt>
							<dd class="flex items-center gap-2">
								<span class="font-medium" :class="subscriptionConfig.color">
									{{ store.data.update?.current_version || "Unknown" }}
								</span>
							</dd>
						</div>
					</dl>

					<dl class="grid gap-3">
						<div class="flex items-center justify-between">
							<dt class="text-muted-foreground">License:</dt>
							<dd class="flex items-center gap-2">
								<component :is="subscriptionConfig.icon" size="16" :class="subscriptionConfig.color" />
								<span class="font-medium" :class="subscriptionConfig.color">
									{{ subscriptionConfig.text }}
								</span>
								<!-- Updated to use aggregate licenseStatus -->
								<div
									class="w-2.5 h-2.5 rounded-full shadow-sm ml-2"
									:class="{
										'bg-green-500 shadow-green-500/50': licenseStatus.status === 'excellent',
										'bg-amber-500 shadow-amber-500/50': licenseStatus.status === 'warning',
										'bg-red-500 shadow-red-500/50': licenseStatus.status === 'critical',
										'bg-gray-500 shadow-gray-500/50': licenseStatus.status === 'unknown',
									}"
								/>
							</dd>
						</div>
					</dl>

					<dl class="grid gap-3">
						<div class="flex items-center justify-between">
							<dt class="text-muted-foreground">License ID</dt>
							<dd class="flex items-center gap-2">
								<span class="font-mono text-sm bg-muted/50 px-2 py-1 rounded">
									{{ store.data.license?.data?.sub_id || "N/A" }}
								</span>
							</dd>
						</div>
					</dl>

					<dl class="grid gap-3">
						<div class="flex items-center justify-between">
							<dt class="text-muted-foreground">Licensee Name</dt>
							<dd class="flex items-center gap-2">
								{{ store.data.license?.data?.sub_name || "N/A" }}
							</dd>
						</div>
					</dl>

					<dl class="grid gap-3">
						<div class="flex items-center justify-between">
							<dt class="text-muted-foreground">License Status</dt>
							<dd class="flex items-center gap-2">
								<span
									class="font-medium"
									:class="{
										'text-green-600 dark:text-green-400': subscriptionConfig.active,
										'text-red-600 dark:text-red-400': !subscriptionConfig.active,
									}"
								>
									{{ subscriptionConfig.active ? "Active" : "Inactive" }}
								</span>
							</dd>
						</div>
					</dl>

					<dl class="grid gap-3">
						<div class="flex items-center justify-between">
							<dt class="text-muted-foreground">Device Count</dt>
							<dd class="flex items-center gap-2">
								<span class="font-medium" :class="deviceCountStatus.color"> {{ configinfo.data?.deviceCount || 0 }}/{{ store.data.license?.data?.device_count || "N/A" }} </span>
							</dd>
						</div>
					</dl>

					<dl class="grid gap-3">
						<div class="flex items-center justify-between">
							<dt class="text-muted-foreground">License Expiry</dt>
							<dd class="flex items-center gap-2">
								{{ formatters.formatTime(store.data.license?.data?.expiry, true) || "N/A" }}
							</dd>
						</div>
					</dl>

					<!-- Additional license details -->
					<dl class="grid gap-3" v-if="store.data.license?.data?.plan_type">
						<div class="flex items-center justify-between">
							<dt class="text-muted-foreground">License Plan</dt>
							<dd class="flex items-center gap-2">
								{{ store.data.license?.data?.plan_type }}
							</dd>
						</div>
					</dl>

					<dl class="grid gap-3" v-if="store.data.license?.data?.devices_limit">
						<div class="flex items-center justify-between">
							<dt class="text-muted-foreground">Devices Limit</dt>
							<dd class="flex items-center gap-2">
								{{ store.data.license?.data?.devices_limit }}
								<span v-if="store.data.license?.data?.devices_count" class="text-sm text-muted-foreground"> ({{ store.data.license?.data?.devices_count }} used) </span>
							</dd>
						</div>
					</dl>

					<!-- Update information -->
					<dl class="grid gap-3" v-if="store.hasUpdate">
						<div class="flex items-center justify-between">
							<dt class="text-muted-foreground">Latest Version</dt>
							<dd class="flex items-center gap-2">
								<RcBadge variant="info" size="sm">
									{{ store.data.update?.latest_version }}
								</RcBadge>
								<span class="text-sm text-muted-foreground">Update available</span>
							</dd>
						</div>
					</dl>
				</div>
			</div>

			<!-- License compatibility message -->

			<AlertTip class="mt-2" variant="dark" small title="License & Support Information" message="Your license grants you access to updates, support, and documentation. For assistance with your license or technical issues, please contact our support team through the rConfig Portal. Visit our documentation for detailed guides and best practices." />

			<Separator class="mt-6 xl:mb-2" />
			<div class="flex flex-col items-start justify-between w-full lg:items-start xl:flex-row gap-3">
				<a href="https://www.rconfig.com/licenses" class="flex items-center gap-2 px-4 py-2 text-white rounded-md bg-rcgray-900 hover:bg-rcgray-800 transition-colors" target="_blank">
					<FileText size="16" />
					License Information
				</a>
				<Separator class="hidden mx-4 xl:block" orientation="vertical" />
				<a class="flex items-center gap-2 px-4 py-2 text-white rounded-md bg-rcgray-900 hover:bg-rcgray-800 transition-colors" :href="$rconfigPortalUrl" target="_blank">
					<Headphones size="16" />
					Contact Support
				</a>
				<Separator class="hidden mx-4 xl:block" orientation="vertical" />
				<a class="flex items-center gap-2 px-4 py-2 text-white rounded-md bg-rcgray-900 hover:bg-rcgray-800 transition-colors" :href="$rconfigDocsUrl" target="_blank">
					<HelpCircle size="16" />
					Online Help
				</a>
			</div>
			<p class="mt-4 ml-4">© rConfig {{ new Date().getFullYear() }}. All rights reserved.</p>
		</div>
	</div>
</template>