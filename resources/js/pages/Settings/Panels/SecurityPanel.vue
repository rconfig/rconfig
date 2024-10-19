<script setup>
import { ref } from 'vue';
import { Separator } from '@/components/ui/separator';
</script>

<template>
  <div class="flex justify-center w-full">
    <div class="flex flex-col items-center w-full gap-4 md:w-1/2">
      <div class="grid w-full max-w-full items-center gap-1.5">
        <h3 class="mb-2 text-2xl font-semibold leading-7 tracking-tight font-inter">Security</h3>
        <p class="text-sm text-muted-foreground">Update your account settings. Set your preferred language and timezone.</p>
        <Separator class="my-6" />
      </div>
    </div>
  </div>
</template>
