<script setup>
import { onMounted } from 'vue';
import { useDashboard } from '@/pages/Dashboard/useDashboard';
import ConfigInfoCards from '@/pages/Dashboard/ConfigInfoCards.vue';
import HealthLatestCards from '@/pages/Dashboard/HealthLatestCards.vue';
import SysinfoCards from '@/pages/Dashboard/SysinfoCards.vue';
import DashboardActions from '@/pages/Dashboard/DashboardActions.vue';

const { fetchSysinfo, fetchConfiginfo, fetchHealth, sysinfo, configinfo, healthLatest, isLoadingSysinfo, isLoadingConfiginfo, isLoadingHealth, toastSuccess, toastError } = useDashboard();

defineProps({});

onMounted(() => {
  fetchSysinfo();
  fetchConfiginfo();
  fetchHealth();
  // window.addEventListener('keydown', handleKeyDown);
});
</script>

<template>
  <main class="flex flex-col flex-1 gap-2 dark:bg-rcgray-900">
    <DashboardActions />

    <ConfigInfoCards
      :configinfo="configinfo"
      :isLoadingConfiginfo="isLoadingConfiginfo" />

    <div class="flex flex-row gap-8 px-8">
      <div class="flex-1">
        <HealthLatestCards
          :healthLatest="healthLatest"
          :isLoadingHealth="isLoadingHealth"
          :SystemUptime="sysinfo.SystemUptime" />
      </div>

      <div class="flex-1">
        <SysinfoCards
          @refresh="fetchSysinfo()"
          :sysinfo="sysinfo"
          :isLoadingSysinfo="isLoadingSysinfo" />
      </div>
    </div>
  </main>
</template>
