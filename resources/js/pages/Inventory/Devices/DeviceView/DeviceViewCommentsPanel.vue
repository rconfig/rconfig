<script setup>
import CommentsMainPanel from '@/pages/Inventory/Devices/Comments/CommentsMainPanel.vue';

const props = defineProps({
  deviceId: {
    type: Number,
    required: true
  },
  deviceName: {
    type: String,
    required: true
  }
});
</script>

<template>
  <CommentsMainPanel
    :deviceId="deviceId"
    :deviceName="deviceName"
    :isDeviceCommentsPanelView="true" />
</template>
