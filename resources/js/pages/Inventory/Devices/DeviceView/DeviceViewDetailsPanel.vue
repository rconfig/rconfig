<script setup>
import { ref, inject } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';

const hoverIcons = ref({});
const activeIcons = ref({});
const emit = defineEmits(['refresh']);
const formatters = inject('formatters');

defineProps({
  isLoading: Boolean,
  deviceData: Object
});

function refresh() {
  emit('refresh');
}
</script>

<template>
  <div>
    <Card class="overflow-hidden">
      <CardHeader class="flex flex-row items-start p-4 bg-muted/50">
        <div class="grid gap-0.5">
          <CardTitle class="flex items-center gap-2 text-lg group">Device Details</CardTitle>
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
            v-if="!isLoading && deviceData">
            <dl class="grid gap-3">
              <div class="flex items-center justify-between">
                <dt class="text-muted-foreground">Device ID</dt>
                <dd class="flex items-center gap-2">{{ deviceData.id }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-muted-foreground">Name</dt>
                <dd class="flex items-center gap-2">{{ deviceData.device_name }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-muted-foreground">IP Address</dt>
                <dd class="flex items-center gap-2">{{ deviceData.device_ip }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-muted-foreground">Status</dt>
                <dd class="flex items-center gap-2">{{ deviceData.status }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-muted-foreground">Category</dt>
                <dd class="flex items-center gap-2">{{ deviceData.category[0].categoryName }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-muted-foreground">Vendor</dt>
                <dd class="flex items-center gap-2">{{ deviceData.vendor[0].vendorName }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-muted-foreground">Model</dt>
                <dd class="flex items-center gap-2">{{ deviceData.device_model }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-muted-foreground">Template</dt>
                <dd class="flex items-center gap-2">{{ deviceData.template[0].templateName }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-muted-foreground">Tags</dt>
                <dd class="flex items-center gap-2">{{ deviceData.tag[0].tagname }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-muted-foreground">Created</dt>
                <dd class="flex items-center gap-2">{{ formatters.formatTime(deviceData.created_at) }}</dd>
              </div>
            </dl>
          </div>
        </transition>
      </CardContent>
      <CardFooter class="flex flex-row items-center px-4 py-3 border-t bg-muted/50">
        <div
          class="flex items-center gap-2 text-xs text-muted-foreground"
          v-if="!isLoading && deviceData">
          <Icon
            icon="formkit:datetime"
            class="text-indigo-400" />
          Last Poll: {{ deviceData.last_seen }}
        </div>
      </CardFooter>
    </Card>
  </div>
</template>
