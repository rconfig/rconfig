<script setup>
import SystemSettingsFormBanner from '@/pages/Settings/Forms/SystemSettingsFormBanner.vue';
import SystemSettingsFormTimezone from '@/pages/Settings/Forms/SystemSettingsFormTimezone.vue';
import SystemSettingsFormEmail from '@/pages/Settings/Forms/SystemSettingsFormEmail.vue';
</script>

<template>
  <div class="flex justify-center w-full">
    <div class="flex flex-col items-center w-full gap-4 md:w-1/2">
      <SystemSettingsFormTimezone />
      <SystemSettingsFormBanner />
      <SystemSettingsFormEmail />
    </div>
  </div>
</template>
