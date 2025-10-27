<script setup>
import AppStatusBar from "@/pages/Dashboard/AppStatusBar.vue";
import ConfigInfoCards from "@/pages/Dashboard/ConfigInfoCards.vue";
import FeedbackForm from "@/pages/Dashboard/FeedbackForm.vue";
import HealthLatestCards from "@/pages/Dashboard/HealthLatestCards.vue";
import QuickActions from "@/pages/Dashboard/QuickActions.vue";
import SysinfoCards from "@/pages/Dashboard/SysinfoCards.vue";
import V8DashboardTeaser from "@/pages/Dashboard/V8DashboardTeaser.vue";
import { onMounted, ref } from "vue";
import { useDashboard } from "@/pages/Dashboard/useDashboard";

const { fetchSysinfo, fetchConfiginfo, fetchHealth, sysinfo, configinfo, healthLatest, isLoadingSysinfo, isLoadingConfiginfo, isLoadingHealth } = useDashboard();

defineProps({});

onMounted(() => {
	fetchSysinfo();
	fetchConfiginfo();
	fetchHealth();
});
</script>

<template>
	<main class="flex flex-col flex-1 gap-4 p-6 dark:bg-rcgray-900">
		<!-- Header Actions -->
		<AppStatusBar :deviceCnt="configinfo?.data?.deviceCount" />

		<!-- Main Dashboard Grid -->
		<div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
			<!-- Primary Stats - Full Width on Mobile, 8 cols on XL -->
			<QuickActions />
			<div class="xl:col-span-12">
				<ConfigInfoCards :configinfo="configinfo" :isLoadingConfiginfo="isLoadingConfiginfo" />
			</div>

			<SysinfoCards :sysinfo="sysinfo" :isLoadingSysinfo="isLoadingSysinfo" @refresh="fetchSysinfo(true)" />
			<HealthLatestCards :healthLatest="healthLatest" :isLoadingHealth="isLoadingHealth" @refresh="fetchHealth" :SystemUptime="sysinfo.systemUptime" />

			<!-- Sidebar Cards - Hidden on mobile/tablet, shown on XL+ -->
			<FeedbackForm />
			<V8DashboardTeaser />
		</div>
	</main>
</template>
