<script setup>
import { ref } from 'vue';
import { Separator } from '@/components/ui/separator';
</script>

<template>
  <div class="flex justify-center w-full">
    <div class="flex flex-col items-center w-full gap-4 md:w-1/2">
      <div class="grid w-full max-w-full items-center gap-1.5">
        <h3 class="mb-2 text-2xl font-semibold leading-7 tracking-tight font-inter">Security</h3>
        <p class="text-sm text-muted-foreground">Information on rConfig Security parameters</p>

        <div class="flex items-start gap-4 mt-6">
          <Icon
            icon="fxemoji:lock"
            class="w-12 h-12 mr-8" />
          <div class="w-3/4">
            <div><strong>Encryption of Stored Credentials:</strong></div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
              Rest easy knowing that all credentials stored in your rConfig database are fully encrypted. The encryption key is securely stored in your
              <code class="px-1">.env</code>
              file.
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
              For convenience, we strongly recommend backing up your
              <code class="px-1">.env</code>
              laravel file regularly. Losing it could result in data loss or access issues.
            </div>
            <div class="flex items-center gap-2 mt-2 text-xs text-muted-foreground">
              <Icon icon="logos:laravel" />
              <a
                href="https://laravel.com/docs/11.x/configuration"
                target="_blank"
                class="text-blue-500 underline">
                Learn more about the .env file
              </a>
            </div>
          </div>
        </div>
        <Separator class="my-6" />

        <div class="flex items-start gap-4 mt-6">
          <Icon
            icon="fluent-color:shield-checkmark-48"
            class="w-12 h-12 mr-8" />
          <div class="w-3/4">
            <div><strong>Your Data, Your Responsibility:</strong></div>
            <div class="text-sm text-gray-500 dark:text-gray-400">All data managed by rConfig is stored on *your* systems. rConfig does not and will never store, host, or transmit any of your data.</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Please ensure your own security controls are properly configured to reflect this.</div>
          </div>
        </div>

        <Separator class="my-6" />
      </div>
    </div>
  </div>
</template>
