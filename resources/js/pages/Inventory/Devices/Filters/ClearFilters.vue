<script setup lang="ts">
import axios from 'axios';
import { onMounted } from 'vue';
import { Popover, PopoverTrigger } from '@/components/ui/popover';

const emit = defineEmits(['update:modelValue']);

onMounted(() => {});

function clearFilters() {
  emit('update:modelValue', []);
}
</script>

<template>
  <Popover>
    <PopoverTrigger>
      <Button
        variant="ghost"
        @click="clearFilters"
        class="flex items-center justify-center w-full px-2 py-1 border rounded-xl whitespace-nowrap h-fit bg-rcgray-700 text-rcgray-400">
        <Icon
          icon="mdi:clear-circle-outline"
          class="mr-2" />
        Clear filters
      </Button>
    </PopoverTrigger>
  </Popover>
</template>
