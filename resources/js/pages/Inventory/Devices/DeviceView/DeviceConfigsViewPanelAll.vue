<script setup>
import { ref, onMounted, inject } from 'vue';
import ConfigsTable from '@/pages/Configs/ConfigsTable.vue';
import { useDialogStore } from '@/stores/dialogActions';

const emit = defineEmits(['toggle-view']);

const props = defineProps({
  deviceId: Number
});
const dialogStore = useDialogStore();
const { openDialog, closeDialog, isDialogOpen } = dialogStore;

const latestConfigs = ref({});
const isLoading = ref(false);

onMounted(() => {
  refreshConfigs();
});

function getlastConfigsForGivenDevice() {
  axios
    .get('/api/configs/latest-by-deviceid/' + props.deviceId)
    .then(response => {
      latestConfigs.value = response.data.data;
    })
    .catch(error => {
      console.log(error);
    });
}

function refreshConfigs() {
  isLoading.value = true;
  getlastConfigsForGivenDevice();
  setTimeout(() => {
    isLoading.value = false;
  }, 1000);
}

function emitToggleView() {
  emit('toggle-view', 'latest');
}
</script>

<template>
  <div>
    <div class="p-2 overflow-none">
      <div class="flex flex-row items-center justify-start gap-2 pt-1">
        <span class="inline-flex flex-row items-center h-[24px] rounded-md px-1.5 py-0.5 gap-1 shadow-[inset_0_0_0_1px_rgb(69,71,74)] bg-[#313337]">
          <span class="flex-[0_1_auto] min-w-0 overflow-hidden text-ellipsis whitespace-nowrap inline-flex line-clamp-1">All Config Downloads for Device ID: {{ props.deviceId }}</span>
        </span>
        <span class="flex-1 bg-[rgb(49,51,55)] bg-[rgb(49,51,55)] flex-shrink-0 h-px w-full"></span>
        <Button
          type="button"
          title="Refresh"
          variant="ghost"
          @click="refreshConfigs"
          class="p-2 h-7">
          <Icon
            icon="charm:refresh"
            :class="isLoading ? 'w-4 h-4 text-blue-500 animate-spin' : 'w-4 h-4 text-gray-600 hover:text-blue-500'"
            class="w-4 h-4" />
        </Button>
      </div>

      <div
        class="mt-4 space-y-2"
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

      <div
        class="mt-6 space-y-4"
        v-if="!isLoading">
        <ConfigsTable :configsId="deviceId" />
      </div>
      <div class="flex items-center justify-end mt-4 space-x-2">
        <Button
          variant="outline"
          @click="emitToggleView"
          class="text-center">
          View Latest >>
        </Button>
      </div>
    </div>
  </div>
</template>
