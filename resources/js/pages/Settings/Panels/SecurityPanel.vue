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
          <!-- <Icon
            icon="catppuccin:codeowners"
            class="w-12 h-12 mr-8"
            color="red" /> 
            ICON Below is catppuccin:codeowner with red color-->

          <svg
            class="w-12 h-12 mr-8"
            xmlns="http://www.w3.org/2000/svg"
            width="1em"
            height="1em"
            viewBox="0 0 16 16">
            <g
              fill="none"
              stroke="#fff47a"
              stroke-linecap="round"
              stroke-linejoin="round">
              <path d="m8.01 1.5l5.728 1.774C15.25 11.154 9.813 14.5 7.984 14.5s-7.281-3.357-5.7-11.226zM8 8.5v2z" />
              <circle
                cx="8"
                cy="7"
                r="1.5" />
            </g>
          </svg>

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
            icon="catppuccin:cargo-lock"
            class="w-12 h-12 mr-8" />
          <div class="w-3/4">
            <div><strong>Your Data, Your Responsibility:</strong></div>
            <div class="text-sm text-gray-500 dark:text-gray-400">All data managed by rConfig is stored on *your* systems. rConfig does not and will never store, host, or transmit any of your data.</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Please ensure your own network and server security controls are properly configured to reflect this.</div>
          </div>
        </div>

        <Separator class="my-6" />
      </div>
    </div>
  </div>
</template>
