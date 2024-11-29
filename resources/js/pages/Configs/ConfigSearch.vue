<script setup>
import { ref } from 'vue';
import Loading from '@/pages/Shared/Loading.vue';
import ConfigSearchFilterCard from '@/pages/Configs/ConfigSearch/ConfigSearchFilterCard.vue';
import { ScrollArea } from '@/components/ui/scroll-area';

const isLoading = ref(false);

const props = defineProps({});
</script>

<template>
  <div class="">
    <ResizablePanelGroup
      direction="horizontal"
      class="">
      <ResizablePanel
        :default-size="25"
        :max-size="30"
        :min-size="10"
        collapsible
        :collapsed-size="0"
        ref="panelElement2"
        class="min-h-[100vh]">
        <h1 class="m-2 text-sm font-semibold">Filter Options</h1>
        <ConfigSearchFilterCard
          class="p-2"
          :isLoading="isLoading" />
      </ResizablePanel>
      <ResizableHandle with-handle />
      <ResizablePanel class="min-h-[100vh]">
        <ScrollArea class="h-full border border-none rounded-md">
          <div
            class="flex items-center justify-center"
            style="height: 60vh"
            v-if="isLoading">
            <Loading class="flex justify-center" />
          </div>

          <h1
            class="m-2 text-sm font-semibold"
            v-if="!isLoading">
            Results
          </h1>

          <!-- <DeviceConfigsViewPanel
              class="p-2"
              v-if="!isLoading && mainNavSelected === 'configs'"
              :deviceId="editId"
              style="height: 60vh" /> -->
        </ScrollArea>
      </ResizablePanel>
    </ResizablePanelGroup>
  </div>
</template>
