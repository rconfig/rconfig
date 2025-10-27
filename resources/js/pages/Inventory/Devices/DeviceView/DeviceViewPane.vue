<script setup>
import DeviceAddEditDialog from '@/pages/Inventory/Devices/DeviceAddEditDialog.vue';
import ConfirmDeleteAlert from '@/pages/Shared/AlertDialog/ConfirmDeleteAlert.vue';
import DeviceConfigsViewPanel from '@/pages/Inventory/Devices/DeviceView/DeviceConfigsViewPanel.vue';
import DeviceDetailsLeftNav from '@/pages/Inventory/Devices/DeviceView/DeviceDetailsLeftNav.vue';
import DeviceDetailsMainNav from '@/pages/Inventory/Devices/DeviceView/DeviceDetailsMainNav.vue';
import DeviceLatestEventsPanel from '@/pages/Inventory/Devices/DeviceView/DeviceLatestEventsPanel.vue';
import DeviceViewCommentsPanel from '@/pages/Inventory/Devices/DeviceView/DeviceViewCommentsPanel.vue';
import DeviceViewConfigStatusPanel from '@/pages/Inventory/Devices/DeviceView/DeviceViewConfigStatusPanel.vue';
import DeviceViewDetailsPanel from '@/pages/Inventory/Devices/DeviceView/DeviceViewDetailsPanel.vue';
import DeviceViewPaneDropdown from '@/pages/Inventory/Devices/DeviceView/DeviceViewPaneDropdown.vue';
import Loading from '@/pages/Shared/Loaders/Loading.vue';
import Spinner from '@/pages/Shared/Icon/Spinner.vue';
import { ScrollArea } from '@/components/ui/scroll-area';
import { useDeviceViewPane } from '@/pages/Inventory/Devices/DeviceView/useDeviceViewPane';
import { useDevices } from '@/pages/Inventory/Devices/useDevices';
import { ref } from 'vue';

const emit = defineEmits(['close']);

const props = defineProps({
  editId: Number
});

const { addToFavorites, appDirPath, closeNav, copyDebug, deviceData, downloadNow, downloadStatus, favoriteItem, fetchDevice, isLoading, leftNavSelected, mainNavSelected, panelElement2, selectLeftNavView, selectMainNavView } = useDeviceViewPane(props, emit);
const { handleSave, viewEditDialog, showConfirmDelete, deleteDevice, purgeDeviceConfigs, newDeviceModalKey } = useDevices();
const isClone = ref(false);

function handleDropdownDelete() {
  deleteDevice(props.editId);
  emit('close');
}
function handleDropDownPurge() {
  purgeDeviceConfigs(props.editId);
}
</script>

<template>
  <main class="flex flex-col flex-1 gap-2 dark:bg-rcgray-900">
    <div class="flex flex-row items-center justify-between h-12 gap-3 pl-4 pr-2 border-b">
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
          class="h-8 ml-2"
          @click="copyDebug('cd ' + appDirPath + ' && php artisan rconfig:download-device ' + props.editId + ' -d')"
          variant="outline">
          <Icon
            icon="lucide:code-xml"
            class="mr-2" />
          Copy Debug
        </Button>
        <Button
          class="h-8 ml-2"
          @click="downloadNow()"
          variant="outline">
          <Spinner :state="downloadStatus" />
          <span
            v-if="downloadStatus"
            class="text-muted-foreground"
            style="word-wrap: normal">
            {{ downloadStatus }}
          </span>
          <Icon
            v-if="!downloadStatus"
            icon="catppuccin:folder-download-open"
            class="mr-2" />
          <span v-if="!downloadStatus">Download Now</span>
        </Button>
        <DeviceViewPaneDropdown
          @onPurge="handleDropDownPurge()"
          @openDeviceEdit="viewEditDialog(editId)"
          @openDeviceClone="viewEditDialog(editId), (isClone = true)"
          @onDelete="showConfirmDelete = true" />
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
          <DeviceDetailsLeftNav
            @closeNav="closeNav"
            @selectLeftNavView="selectLeftNavView"
            :selectedNav="leftNavSelected"
            :deviceId="editId" />
          <DeviceViewConfigStatusPanel
            class="p-2 mt-2"
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
            :deviceId="editId"
            :deviceName="deviceData.device_name" />
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

            <DeviceConfigsViewPanel
              class="p-2"
              v-if="!isLoading && mainNavSelected === 'configs'"
              :deviceId="editId"
              style="height: 60vh" />
          </ScrollArea>
        </ResizablePanel>
      </ResizablePanelGroup>

      <!-- EDITOR -->
      <!-- <div
      class="code-editor__code-pre"
      id="code-editor__code-pre"
      style="height: calc(100vh - 450px)"></div> -->
      <!-- EDITOR -->
      <DeviceAddEditDialog
        :key="newDeviceModalKey"
        @close="isClone = false"
        @save="
          handleSave();
          fetchDevice(editId);
        "
        :isClone="isClone"
        :editId="editId" />

      <!-- FOR MULTIPLE DELETE -->
      <ConfirmDeleteAlert
        :ids="[editId]"
        :showConfirmDelete="showConfirmDelete"
        @close="showConfirmDelete = false"
        @handleDelete="handleDropdownDelete()" />
    </div>
  </main>
</template>
