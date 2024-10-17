<script setup>
import { ref, onMounted } from 'vue';
import { Separator } from '@/components/ui/separator';
import SystemSettingsFormBanner from '@/pages/Settings/Forms/SystemSettingsFormBanner.vue';
import SystemSettingsFormTimezone from '@/pages/Settings/Forms/SystemSettingsFormTimezone.vue';
</script>

<template>
  <div class="flex flex-col items-center justify-start max-w-full gap-4">
    <div class="">
      <h3 class="mb-4 text-2xl font-semibold leading-7 tracking-tight font-inter">System Settings</h3>

      <SystemSettingsFormBanner />
      <SystemSettingsFormTimezone />

      <div class="grid w-full max-w-full items-center gap-1.5">
        <p class="text-sm text-muted-foreground">Update your account settings. Set your preferred language and timezone.</p>
        <Separator class="my-6" />
      </div>
    </div>
  </div>
</template>
