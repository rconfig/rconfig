<script setup>
import { ref } from 'vue';
import { useStorage } from '@vueuse/core';

// Store to track the read status of items, persisted to local storage
const items = useStorage('helpTopicItemsHelpSheet', [
  {
    id: 1,
    name: 'Adding a device',
    url: 'https://docs.rconfig.com/devices/devices/',
    icon: 'DeviceIcon',
    iconClass: 'text-4xl',
    text: 'Adding a device to inventory is easy, but it requires a few steps. Follow this guide to get started.',
    read: false
  },
  {
    id: 2,
    name: 'Troubleshooting Device Connectivity',
    url: 'https://docs.rconfig.com/general/troubleshooting/',
    icon: 'TroubleshootIcon',
    iconClass: 'text-4xl',
    text: 'If you are having trouble connecting to a device, follow this guide to troubleshoot the issue.',
    read: false
  },
  {
    id: 3,
    name: 'Queue Manager',
    url: 'https://docs.rconfig.com/settings/queues/',
    icon: 'SysQueueManagerIcon',
    iconClass: 'text-4xl',
    text: 'The Queue Manager is a powerful tool that manages multi threaded tasks. Learn how to use it here.',
    read: false
  }
]);

function visitDocs() {
  window.open('https://docs.rconfig.com', '_blank');
}

function openItem(item) {
  window.open(item.url, '_blank');
  markAsRead(item.id);
}

function markAsRead(itemId) {
  const item = items.value.find(i => i.id === itemId);
  if (item) {
    item.read = true;
  }
}
</script>

<template>
  <div class="grid gap-4 py-4">
    <div class="flex justify-between">
      <span class="font-semibold">Popular Docs</span>

      <Badge
        class="flex justify-end bg-gray-800 border border-gray-700 text-slate-50 min-w-fit hover:cursor-pointer hover:bg-gray-700"
        @click="visitDocs()">
        View Docs
      </Badge>
    </div>
    <Card class="space-y-2">
      <button
        @click="openItem(item)"
        v-for="item of items"
        :key="item.id"
        class="flex items-start justify-between gap-2 p-3 text-sm text-left transition-all border rounded-lg hover:bg-accent">
        <div class="">
          <component
            :is="item.icon"
            :class="item.iconClass" />
        </div>
        <div>
          <div class="flex flex-col w-full gap-1">
            <div class="flex items-center">
              <div class="flex items-center gap-2">
                <div class="font-semibold">
                  {{ item.name }}
                </div>
              </div>
              <div class="ml-auto text-xs text-muted-foreground">
                <span
                  v-if="!item.read"
                  class="flex w-2 h-2 bg-blue-600 rounded-full" />
              </div>
            </div>
          </div>
          <div class="text-xs line-clamp-2 text-muted-foreground">
            {{ item.text.substring(0, 300) }}
          </div>
        </div>
      </button>
    </Card>
  </div>
</template>
