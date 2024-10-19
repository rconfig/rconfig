<script setup>
import { ref } from 'vue';
import { Separator } from '@/components/ui/separator';
import { useAbout } from '@/pages/Settings/Forms/useAbout';

const { licenseInfo } = useAbout();
</script>

<template>
  <div class="flex justify-center w-full">
    <div class="flex flex-col items-center w-full gap-4 md:w-1/2">
      <div class="grid w-full max-w-full items-center gap-1.5">
        <h3 class="mb-4 text-2xl font-semibold leading-7 tracking-tight font-inter">About rConfig</h3>
        <p class="text-sm">rConfig is a powerful and user-friendly network configuration management tool designed to help you efficiently manage and automate your network devices. Stay organized, save time, and ensure consistency across your network infrastructure with rConfig.</p>

        <div class="grid gap-3 p-4 mt-6 border">
          <dl class="grid gap-3">
            <div class="flex items-center justify-between">
              <dt class="text-muted-foreground">App Version</dt>
              <dd class="flex items-center gap-2">{{ licenseInfo.version }}-Core</dd>
            </div>
          </dl>
          <dl class="grid gap-3">
            <div class="flex items-center justify-between">
              <dt class="text-muted-foreground">License ID</dt>
              <dd class="flex items-center gap-2">
                {{ licenseInfo.sub_id }}
              </dd>
            </div>
          </dl>
          <dl class="grid gap-3">
            <div class="flex items-center justify-between">
              <dt class="text-muted-foreground">Licensee Name</dt>
              <dd class="flex items-center gap-2">
                {{ licenseInfo.sub_name }}
              </dd>
            </div>
          </dl>
          <dl class="grid gap-3">
            <div class="flex items-center justify-between">
              <dt class="text-muted-foreground">License Status</dt>
              <dd class="flex items-center gap-2">
                {{ licenseInfo.status }}
              </dd>
            </div>
          </dl>
          <dl class="grid gap-3">
            <div class="flex items-center justify-between">
              <dt class="text-muted-foreground">License Expiry</dt>
              <dd class="flex items-center gap-2">
                {{ licenseInfo.expiry }}
              </dd>
            </div>
          </dl>
        </div>
      </div>
      <Separator class="mt-6 xl:mb-2" />
      <div class="flex flex-col items-start justify-between w-full lg:items-start xl:flex-row">
        <a
          href="https://www.rconfig.com/eula-pro"
          class="flex items-center gap-2 px-4 py-2 text-white rounded-md bg-rcgray-900 hover:bg-rcgray-800"
          target="_blank">
          <Icon icon="clarity:license-line" />
          License
        </a>
        <Separator
          class="hidden mx-4 xl:block"
          orientation="vertical" />
        <a
          :href="'mailto:support@rconfig.com?subject=rConfig Support from V8Core'"
          class="flex items-center gap-2 px-4 py-2 text-white rounded-md bg-rcgray-900 hover:bg-rcgray-800">
          <Icon icon="streamline:customer-support-1-solid" />
          Contact Support
        </a>
        <Separator
          class="hidden mx-4 xl:block"
          orientation="vertical" />
        <a
          class="flex items-center gap-2 px-4 py-2 text-white rounded-md bg-rcgray-900 hover:bg-rcgray-800"
          href="https://www.rconfig.com/docs/5.1/getstarted/overview"
          target="_blank">
          <Icon icon="humbleicons:support" />
          Online Help
        </a>
      </div>
      <p class="mt-4 ml-4">© rConfig {{ new Date().getFullYear() }} all rights reserved. rConfig&trade; is a registered Trademark of rConfig.</p>
    </div>
  </div>
</template>
