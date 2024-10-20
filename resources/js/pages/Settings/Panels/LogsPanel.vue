<script setup>
import { ref } from 'vue';
import { Separator } from '@/components/ui/separator';
import SystemLogsTable from '@/pages/Settings/Panels/Components/SystemLogsTable.vue';
</script>

<template>
  <div class="flex justify-center w-full">
    <div class="flex flex-col items-center w-full gap-4 md:w-full">
      <div class="grid w-full max-w-full items-center gap-1.5">
        <h3 class="mb-2 text-2xl font-semibold leading-7 tracking-tight font-inter">System Logs</h3>
        <p class="text-sm text-muted-foreground">View and manage all system logs</p>

        <SystemLogsTable />
      </div>
    </div>
  </div>
</template>
