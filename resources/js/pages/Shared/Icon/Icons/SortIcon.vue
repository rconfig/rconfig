<script setup>
import { SortAsc, SortDesc, ArrowUpDown } from 'lucide-vue-next';

// Props definition
const props = defineProps({
  // The current sort parameter (e.g. 'id', '-id', 'task_name', '-task_name')
  sortParam: {
    type: String,
    required: true
  },
  // The field name to check against (e.g. 'id', 'task_name')
  field: {
    type: String,
    required: true
  },
  // Optional custom size for the icons
  size: {
    type: String,
    default: 'w-4 h-4'
  }
});

// Computed property to determine which icon to show
const isAscending = () => props.sortParam === props.field;
const isDescending = () => props.sortParam === `-${props.field}`;
</script>

<template>
  <SortAsc v-if="isAscending()" :class="size" />
  <SortDesc v-else-if="isDescending()" :class="size" />
  <ArrowUpDown v-else :class="size" />
</template>