<script setup>
import { ref } from 'vue';

defineProps({});
</script>

<template>
  <div class="flex flex-col items-center justify-center h-screen gap-1 text-center">
    <div class="flex items-center gap-2 dark:text-gray-400">
      Loading
      <Icon icon="eos-icons:three-dots-loading" />
    </div>

    <h3 class="text-xl font-semibold tracking-tight">You have no command groups</h3>
    <p class="text-sm text-muted-foreground">You can add a command groups.</p>
    <button class="inline-flex items-center justify-center px-4 py-2 mt-4 text-sm font-medium transition-colors rounded-md shadow whitespace-nowrap focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-9">Add Device</button>
  </div>
</template>
