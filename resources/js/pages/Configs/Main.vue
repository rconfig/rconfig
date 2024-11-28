<script setup>
import ConfigsTable from '@/pages/Configs/ConfigsTable.vue';
import useConfigs from '@/composables/useConfigs';

const { configsId, statusIdParam } = useConfigs();
defineProps({});
</script>

<template>
  <main class="flex flex-col flex-1 gap-2 dark:bg-rcgray-900">
    <ConfigsTable
      :configsId="configsId"
      :statusId="statusIdParam.statusId" />
  </main>
</template>
