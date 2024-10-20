<script setup>
import { computed } from 'vue';
import QuickActionsDialog from '@/layouts/Components/Dialogs/QuickActionsDialog.vue';
import QuickSearchDialog from '@/layouts/Components/Dialogs/QuickSearchDialog.vue';

const props = defineProps({
  href: String,
  active: Boolean
});

const classes = computed(() => {
  return props.active
    ? 'inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 dark:border-indigo-600 text-sm font-medium leading-5 text-gray-900 dark:text-gray-100 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
    : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition duration-150 ease-in-out';
});
</script>

<template>
  <div class="quick-action-div">
    <QuickActionsDialog />
    <QuickSearchDialog />
  </div>
</template>

<style scoped>
.quick-action-div {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
  padding: 10px 12px 11px;
}
</style>
