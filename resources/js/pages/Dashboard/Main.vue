<script setup>
import ConfigInfoCards from "@/pages/Dashboard/ConfigInfoCards.vue";
import HealthLatestCards from "@/pages/Dashboard/HealthLatestCards.vue";
import QuickActions from "@/pages/Dashboard/QuickActions.vue";
import SysinfoCards from "@/pages/Dashboard/SysinfoCards.vue";
import DashboardWidgets from "@/pages/Dashboard/DashboardWidgets.vue";
import NewsletterModal from "@/pages/Dashboard/NewsletterModal.vue";
import { onMounted, ref } from "vue";
import { useDashboard } from "@/pages/Dashboard/useDashboard";

const { fetchSysinfo, fetchConfiginfo, fetchHealth, fetchLatestDevices, sysinfo, configinfo, healthLatest, latestDevices, isLoadingSysinfo, isLoadingConfiginfo, isLoadingHealth, isLoadingLatestDevices } = useDashboard();

defineProps({});

onMounted(() => {
	fetchSysinfo();
	fetchConfiginfo();
	fetchHealth();
	fetchLatestDevices();
});
</script>

<template>
	<main class="flex flex-col flex-1 gap-4 p-6 dark:bg-rcgray-900">
		<NewsletterModal />
		<!-- Main Dashboard Grid -->
		<div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
			<!-- Primary Stats - Full Width on Mobile, 8 cols on XL -->
			<QuickActions />
			<div class="xl:col-span-12">
				<ConfigInfoCards :configinfo="configinfo" :isLoadingConfiginfo="isLoadingConfiginfo" />
			</div>

			<SysinfoCards :sysinfo="sysinfo" :isLoadingSysinfo="isLoadingSysinfo" @refresh="fetchSysinfo(true)" />
			<HealthLatestCards :healthLatest="healthLatest" :isLoadingHealth="isLoadingHealth" @refresh="fetchHealth" :SystemUptime="sysinfo.systemUptime" />

			<DashboardWidgets :configinfo="configinfo" :healthLatest="healthLatest" :latestDevices="latestDevices" :isLoadingLatestDevices="isLoadingLatestDevices" />
		</div>
	</main>
</template>
