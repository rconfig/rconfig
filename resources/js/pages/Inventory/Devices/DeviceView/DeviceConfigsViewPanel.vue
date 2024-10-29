<script setup>
import { ref } from 'vue';
import DeviceConfigsViewPanelAll from '@/pages/Inventory/Devices/DeviceView/DeviceConfigsViewPanelAll.vue';
import DeviceConfigsViewPanelLatest from '@/pages/Inventory/Devices/DeviceView/DeviceConfigsViewPanelLatest.vue';

const props = defineProps({
  deviceId: Number
});

// State to track which view is active
const activeView = ref('latest');

// Function to toggle views
const toggleView = view => {
  activeView.value = view;
};
</script>

<template>
  <div>
    <transition
      name="fade"
      mode="out-in">
      <component
        :is="activeView === 'latest' ? DeviceConfigsViewPanelLatest : DeviceConfigsViewPanelAll"
        :deviceId="deviceId"
        @toggle-view="toggleView" />
    </transition>
  </div>
</template>
