<script setup>
import DeviceDetailsLeftNav from '@/pages/Inventory/Devices/DeviceView/DeviceDetailsLeftNav.vue';
import DeviceDetailsMainNav from '@/pages/Inventory/Devices/DeviceView/DeviceDetailsMainNav.vue';
import DeviceLatestEventsPanel from '@/pages/Inventory/Devices/DeviceView/DeviceLatestEventsPanel.vue';
import DeviceConfigsViewPanel from '@/pages/Inventory/Devices/DeviceView/DeviceConfigsViewPanel.vue';
import DeviceViewCommentsPanel from '@/pages/Inventory/Devices/DeviceView/DeviceViewCommentsPanel.vue';
import ConfigSummaryPanel from '@/pages/Configs/ConfigView/ConfigSummaryPanel.vue';
import DeviceViewDetailsPanel from '@/pages/Inventory/Devices/DeviceView/DeviceViewDetailsPanel.vue';
import DeviceViewPaneDropdown from '@/pages/Inventory/Devices/DeviceView/DeviceViewPaneDropdown.vue';
import Loading from '@/pages/Shared/Loading.vue';
import Spinner from '@/pages/Shared/Icon/Spinner.vue';
import { ScrollArea } from '@/components/ui/scroll-area';
import { useConfigViewPane } from '@/pages/Configs/ConfigView/useConfigViewPane';

const props = defineProps({
  editId: Number
});

const { closeNav, configData, isLoading, panelElement2 } = useConfigViewPane(props);
</script>

<template>
  <main class="flex flex-col flex-1 gap-2 dark:bg-rcgray-900">
    <div class="flex flex-row items-center justify-between h-12 gap-3 pl-4 pr-2 border-b">
      <div
        class="flex items-center"
        v-if="configData">
        <span class="text-lg font-semibold text-blue-400 font-inter">{{ configData.filename }}</span>
      </div>
      <div class="flex items-center">
        <DeviceViewPaneDropdown />
      </div>
    </div>

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
          <ConfigSummaryPanel
            class="p-2 mt-2"
            :isLoading="isLoading"
            :configData="configData" />
          <!-- <DeviceViewDetailsPanel
            class="p-2"
            v-if="leftNavSelected === 'details'"
            :isLoading="isLoading"
            :configData="configData" /> -->
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

            <!-- <DeviceDetailsMainNav
              class="p-2"
              @selectMainNavView="selectMainNavView"
              :selectedNav="mainNavSelected.value" />

            <DeviceLatestEventsPanel
              v-if="!isLoading && mainNavSelected === 'notifications'"
              class="p-2"
              style="height: 60vh"
              :deviceId="editId" />

            <DeviceConfigsViewPanel
              class="p-2"
              v-if="!isLoading && mainNavSelected === 'configs'"
              :deviceId="editId"
              style="height: 60vh" /> -->
          </ScrollArea>
        </ResizablePanel>
      </ResizablePanelGroup>

      <!-- EDITOR -->
      <!-- <div
      class="code-editor__code-pre"
      id="code-editor__code-pre"
      style="height: calc(100vh - 450px)"></div> -->
      <!-- EDITOR -->
    </div>
  </main>
</template>
