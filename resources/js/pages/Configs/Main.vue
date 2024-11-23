<script setup>
import ConfigsTable from '@/pages/Configs/ConfigsTable.vue';
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router'; // Import useRoute for accessing route parameters

defineProps({});

const route = useRoute();
const configsId = ref(parseInt(route.params.id) || 0);
const statusIdParam = ref(route.query || null);

onMounted(() => {});
</script>

<template>
  <main class="flex flex-col flex-1 gap-2 dark:bg-rcgray-900">
    <ConfigsTable
      :configsId="configsId"
      :statusId="statusIdParam.statusId" />
  </main>
</template>
