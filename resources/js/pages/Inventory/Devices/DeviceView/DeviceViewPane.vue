<script setup>
import DeviceDetailsLeftNav from '@/pages/Inventory/Devices/DeviceView/DeviceDetailsLeftNav.vue';
import DeviceDetailsMainNav from '@/pages/Inventory/Devices/DeviceView/DeviceDetailsMainNav.vue';
import DeviceLatestEventsPanel from '@/pages/Inventory/Devices/DeviceView/DeviceLatestEventsPanel.vue';
import DeviceViewCommentsPanel from '@/pages/Inventory/Devices/DeviceView/DeviceViewCommentsPanel.vue';
import DeviceViewConfigStatusPanel from '@/pages/Inventory/Devices/DeviceView/DeviceViewConfigStatusPanel.vue';
import DeviceViewDetailsPanel from '@/pages/Inventory/Devices/DeviceView/DeviceViewDetailsPanel.vue';
import Loading from '@/pages/Shared/Loading.vue';
import { ScrollArea } from '@/components/ui/scroll-area';
import { ref } from 'vue';
import { useDeviceViewPane } from '@/pages/Inventory/Devices/DeviceView/useDeviceViewPane';

const props = defineProps({
  editId: Number
});

const { addToFavorites, deviceData, favoriteItem, isLoading, mainNavSelected, selectLeftNavView, selectMainNavView } = useDeviceViewPane(props);
</script>

<template>
  <main class="flex flex-col flex-1 gap-2 dark:bg-rcgray-900">
    <div class="flex justify-between border-b topRow">
      <div
        class="flex items-center"
        v-if="deviceData">
        <span class="text-lg font-semibold text-blue-400 font-inter">{{ deviceData.device_name }}</span>
        <Button
          variant="ghost"
          class="h-8 px-2 py-1.5 h-7 ml-2"
          @click="addToFavorites()">
          <StarUnselected
            class="text-muted-foreground"
            v-if="!favoriteItem.isFavorite" />
          <StarSelected
            class="text-muted-foreground"
            v-if="favoriteItem.isFavorite" />
        </Button>
      </div>
      <div class="flex items-center">
        <Button
          class="w-full h-8 ml-2"
          variant="outline">
          <Icon
            icon="lucide:code-xml"
            class="mr-2" />
          Copy Debug
        </Button>
        <Button
          class="w-full h-8 ml-2"
          variant="outline">
          <Icon
            icon="fluent-mdl2:configuration-solid"
            class="mr-2" />
          View Configs
        </Button>

        <Button
          class="w-full h-8 ml-2"
          variant="outline">
          <Icon
            icon="catppuccin:folder-download-open"
            class="mr-2" />
          Download Now
        </Button>
        <Button
          class="w-full h-8 ml-2"
          variant="outline">
          <Icon
            icon="fa6-solid:clone"
            class="mr-2" />
          Clone Device
        </Button>
        <Button
          class="w-full h-8 ml-2"
          variant="outline">
          <Icon
            icon="icon-park-twotone:delete-five"
            class="mr-2" />
          Purge Failed Configs
        </Button>
        <Button
          class="w-full h-8 ml-2"
          variant="outline">
          <Icon
            icon="icon-park-twotone:delete-five"
            class="mr-2" />
          Edit Device
        </Button>
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
          ref="panelElement"
          class="min-h-[100vh]">
          <DeviceDetailsLeftNav
            @selectLeftNavView="selectLeftNavView"
            :selectedNav="leftNavSelected" />
          <DeviceViewConfigStatusPanel
            class="p-2 mb-4"
            v-if="leftNavSelected === 'details'"
            :isLoading="isLoading"
            :deviceData="deviceData" />
          <DeviceViewDetailsPanel
            class="p-2"
            v-if="leftNavSelected === 'details'"
            :isLoading="isLoading"
            :deviceData="deviceData" />
          <DeviceViewCommentsPanel
            class="p-2"
            v-if="leftNavSelected === 'comments'"
            :deviceId="editId" />
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

            <DeviceDetailsMainNav
              class="p-2"
              @selectMainNavView="selectMainNavView"
              :selectedNav="mainNavSelected" />

            <DeviceLatestEventsPanel
              v-if="!isLoading && mainNavSelected === 'notifications'"
              class="p-2"
              style="height: 60vh"
              :deviceId="editId" />

            <div v-if="!isLoading && mainNavSelected === 'configs'">Configs</div>
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
