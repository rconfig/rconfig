<script setup>
import { ref, inject } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';

const formatters = inject('formatters');

defineProps({
  isLoading: Boolean,
  configData: Object
});
</script>

<template>
  <div>
    <Card class="overflow-hidden">
      <CardHeader class="flex flex-row items-start p-4 bg-muted/50">
        <div class="grid gap-0.5 w-full">
          <CardTitle class="gap-2 text-lg group">
            <div class="flex justify-between">
              <div>Config Details</div>
              <div
                class="flex items-center gap-2 text-xs text-muted-foreground"
                v-if="!isLoading && configData">
                Download status:
                <StatusGreenIcon
                  class="mr-2"
                  v-if="configData.download_status === 1" />
                <StatusRedIcon
                  class="mr-2"
                  v-else-if="configData.download_status === 0" />
                <StatusYellowIcon
                  class="mr-2"
                  v-else-if="configData.download_status === 2" />
              </div>
            </div>
          </CardTitle>
        </div>
      </CardHeader>
      <CardContent class="p-4 pt-0 text-sm">
        <div
          class="space-y-2"
          v-if="isLoading">
          <Skeleton class="w-1/2 h-4" />
          <Skeleton class="w-full h-4" />
          <Skeleton class="w-full h-4" />
          <Skeleton class="w-full h-4" />
          <Skeleton class="w-full h-4" />
          <Skeleton class="w-full h-4" />
          <Skeleton class="w-full h-4" />
          <Skeleton class="w-full h-4" />
          <Skeleton class="w-full h-4" />
        </div>

        <transition name="fade">
          <div
            class="grid gap-3"
            v-if="!isLoading && configData">
            <dl class="grid gap-3">
              <div class="flex items-center justify-between">
                <dt class="text-muted-foreground">Device ID</dt>
                <dd class="flex items-center gap-2">{{ configData.id }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-muted-foreground">Device Name</dt>
                <dd class="flex items-center gap-2">{{ configData.device_name }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-muted-foreground">Command Group</dt>
                <dd class="flex items-center gap-2">{{ configData.device_category }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-muted-foreground">Command</dt>
                <dd class="flex items-center gap-2">{{ configData.command }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-muted-foreground">Filename</dt>
                <dd class="flex items-center gap-2">{{ configData.config_filename }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-muted-foreground">Filesize</dt>
                <dd class="flex items-center gap-2">{{ formatters.formatFileSize(configData.config_filesize) }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-muted-foreground">Duration</dt>
                <dd class="flex items-center gap-2">{{ formatters.formatDuration(configData.start_time, configData.end_time) }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-muted-foreground">Created</dt>
                <dd class="flex items-center gap-2">{{ formatters.formatTime(configData.created_at) }}</dd>
              </div>
            </dl>
          </div>
        </transition>
      </CardContent>
      <!-- <CardFooter class="flex flex-row items-center px-4 py-3 border-t bg-muted/50">
        <div
          class="flex items-center gap-2 text-xs text-muted-foreground"
          v-if="!isLoading && configData">
          <Icon
            icon="formkit:datetime"
            class="text-indigo-400" />
          Status: {{ configData.download_status }}
        </div>
      </CardFooter> -->
    </Card>
  </div>
</template>
