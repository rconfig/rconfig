<script setup>
import ConfigStatusPanel from "@/pages/Inventory/Shared/Panels/ConfigStatusPanel.vue";
import DetailsViewLeftNav from "@/pages/Inventory/Shared/Navs/DetailsViewLeftNav.vue";
import DetailsViewRightNav from "@/pages/Inventory/Shared/Navs/DetailsViewRightNav.vue";
import DeviceAddEditDialog from "@/pages/Inventory/Devices/DeviceAddEditDialog.vue";
import DeviceConfigsViewPanel from "@/pages/Inventory/Devices/DeviceView/DeviceConfigsViewPanel.vue";
import DeviceViewDetailsPanel from "@/pages/Inventory/Devices/DeviceView/DeviceViewDetailsPanel.vue";
import LatestEventsPanel from "@/pages/Inventory/Shared/Panels/LatestEventsPanel.vue";
import Loading from "@/pages/Shared/Loaders/Loading.vue";
import RcConfirmAlertDialog from "@/pages/Shared/ConfirmAlertDialog/RcConfirmAlertDialog.vue";
import Spinner from "@/pages/Shared/Icon/Spinner.vue";
import ViewPaneDropdown from "@/pages/Inventory/Shared/Dropdowns/ViewPaneDropdown.vue";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Terminal, X } from "lucide-vue-next";
import { ref } from "vue";
import { useDeviceViewPane } from "@/pages/Inventory/Devices/DeviceView/useDeviceViewPane";
import { useDevices } from "@/pages/Inventory/Devices/useDevices";
import { useRouter } from "vue-router";
import RcToolTip from "@/pages/Shared/Tooltips/RcToolTip.vue";

const emit = defineEmits(["close"]);

const props = defineProps({
  editId: Number,
});

const {
  // State
  deviceData,
  downloadStatus,
  favoriteItem,
  // removed: hasXftpFiles
  integrationIds,
  integrationNames,
  isLoading,
  leftNavSelected,
  mainNavSelected,
  panelElement2,
  appDirPath,

  // Methods
  addToFavorites,
  closeNav,
  copyDebug,
  downloadNow,
  fetchDevice,
  openIntegrationUrl,
  selectLeftNavView,
  selectMainNavView,
  recheckEocValidation,
} = useDeviceViewPane(props, emit);

const {
  handleSave,
  viewEditDialog,
  showConfirmDelete,
  deleteDevice,
  purgeDeviceConfigs,
  newDeviceModalKey,
  disableDevice,
  enableDevice,
  // removed: openSendSnippetModal
} = useDevices();

// removed: vectorInstalled inject, isLocked/cm_lock logic

const router = useRouter();
const isClone = ref(false);

function handleDropdownDelete() {
  deleteDevice(props.editId);
  emit("close");
}
function handleDropDownPurge() {
  purgeDeviceConfigs(props.editId);
}

function handleDisable() {
  disableDevice(props.editId, false);
  setTimeout(() => {
    fetchDevice(props.editId);
  }, 300);
}

function handleEnable() {
  enableDevice(props.editId, false);
  setTimeout(() => {
    fetchDevice(props.editId);
  }, 300);
}

function close() {
  emit("close");
}
</script>

<template>
  <main class="flex flex-col flex-1 dark:bg-rcgray-900">
    <div class="flex flex-row items-center justify-between h-12 gap-3 pl-4 pr-2" v-if="deviceData">
      <div class="flex items-center">
        <Button @click="close()" size="sm" variant="outline" class="gap-1 border-none hover:bg-rcgray-600">
          <X size="16" class="hover:animate-pulse" />
        </Button>
        <span class="text-lg font-semibold rc-text-heading-gradient font-inter ml-2">
          {{ deviceData.device_name }}
        </span>

        <!-- Removed CM lock icon -->
        <!-- Imported device badges with plain sources -->
        <RcIcon
          name="imported-device"
          class="ml-2 mr-0"
          :addedBy="deviceData?.device_added_by"
          v-if="deviceData?.device_added_by && deviceData?.device_added_by == 9000"
          :source="'Zabbix'"
        />
        <RcIcon
          name="imported-device"
          class="ml-2 mr-0"
          :addedBy="deviceData?.device_added_by"
          v-if="deviceData?.device_added_by && deviceData?.device_added_by == 9001"
          :source="'Netbox'"
        />

        <Button variant="ghost" class="h-8 px-2 py-1.5 h-7 ml-2 animated-star" @click="addToFavorites()">
          <RcIcon name="star-unselected" class="text-muted-foreground animated-star" v-if="!favoriteItem.isFavorite" />
          <RcIcon name="star-selected" class="text-muted-foreground animated-star" v-if="favoriteItem.isFavorite" />
        </Button>
      </div>

      <div class="flex items-center">
        <div class="button-container flex flex-row items-center">
          <RcToolTip :delayDuration="50" :content="'Copy debug command'" :side="'bottom'">
            <template #trigger>
              <Button
                class="h-8 w-4 ml-2"
                @click="copyDebug('cd ' + appDirPath + ' && php artisan rconfig:download-device ' + props.editId + ' -d')"
                variant="outline"
                :title="'Copy debug command'"
              >
                <Terminal size="16" class="button-icon" />
                <span class="button-text">Copy debug command</span>
              </Button>
            </template>
          </RcToolTip>

          <RcToolTip :delayDuration="50" :content="'Download now'" :side="'bottom'">
            <template #trigger>
              <Button
                :class="downloadStatus ? 'h-8 ml-2 min-w-fit transition-all duration-300 ease-in-out' : 'h-8 ml-2 w-4'"
                @click="downloadNow()"
                variant="outline"
                :title="'Download now'"
              >
                <Spinner :state="downloadStatus" v-if="downloadStatus" />
                <RcIcon name="folder-download-open" v-if="!downloadStatus" class="button-icon" />
                <span v-if="downloadStatus" class="ml-2 text-muted-foreground transition-all duration-300 ease-in-out" style="word-wrap: normal;">
                  {{ downloadStatus }}
                </span>
                <span v-if="!downloadStatus" class="button-text">Download now</span>
              </Button>
            </template>
          </RcToolTip>

          <!-- Removed Send Snippet button -->
          <!-- Removed Vector Queue / agentqueue button -->

          <RcToolTip :delayDuration="50" :content="'Re-check CIC validation'" :side="'bottom'">
            <template #trigger>
              <Button class="h-8 ml-2 w-4" @click="recheckEocValidation()" variant="outline" :title="'Re-check CIC validation'">
                <RcIcon name="cic" size="16" class="button-icon" />
                <span class="button-text">Re-check CIC validation</span>
              </Button>
            </template>
          </RcToolTip>
        </div>

        <ViewPaneDropdown
          v-if="deviceData"
          :rowData="deviceData"
          @onDisable="handleDisable()"
          @onEnable="handleEnable()"
          @onPurge="handleDropDownPurge()"
          @openDeviceEdit="viewEditDialog(editId), (isClone = false)"
          @openDeviceClone="viewEditDialog(editId), (isClone = true)"
          @onDelete="showConfirmDelete = true"
        />
      </div>
    </div>

    <div v-if="isLoading">
      <div class="flex items-center justify-center" style="height: 60vh;"></div>
      <Loading class="flex justify-center" />
    </div>

    <div v-else>
      <ResizablePanelGroup direction="horizontal">
        <ResizablePanel
          :default-size="25"
          :max-size="30"
          :min-size="10"
          collapsible
          :collapsed-size="0"
          ref="panelElement2"
          class="min-h-[100vh] h-full flex flex-col overflow-y-auto"
        >
          <ScrollArea class="max-h-[83vh] w-full rounded-md border smooth-scroll overflow-y-auto">
            <DetailsViewLeftNav
              @closeNav="closeNav"
              @selectLeftNavView="selectLeftNavView"
              :selectedNav="leftNavSelected"
              :deviceId="editId"
            />
            <ConfigStatusPanel class="p-2 mt-2" v-if="leftNavSelected === 'details'" :isLoading="isLoading" :data="deviceData" />
            <DeviceViewDetailsPanel class="p-2" v-if="leftNavSelected === 'details'" :isLoading="isLoading" :deviceData="deviceData" />
          </ScrollArea>
        </ResizablePanel>

        <ResizableHandle with-handle />

        <ResizablePanel class="min-h-[100vh]">
          <ScrollArea class="h-[100vh] w-full rounded-md border">
            <div class="flex items-center justify-center" style="height: 60vh;" v-if="isLoading">
              <Loading class="flex justify-center" />
            </div>

            <div v-if="!isLoading">
              <DetailsViewRightNav class="p-2" @selectMainNavView="selectMainNavView" :selectedNav="mainNavSelected" />
              <LatestEventsPanel v-if="!isLoading && mainNavSelected === 'notifications'" class="p-2" style="height: 60vh;" :deviceId="editId" />
              <DeviceConfigsViewPanel class="p-2" v-if="!isLoading && mainNavSelected === 'configs'" :deviceId="editId" style="height: 60vh;" />
            </div>
          </ScrollArea>
        </ResizablePanel>
      </ResizablePanelGroup>

      <DeviceAddEditDialog
        :key="newDeviceModalKey"
        @close="isClone = false"
        @save="
          handleSave();
          fetchDevice(editId);
        "
        :isClone="isClone"
        :editId="editId"
      />
      <!-- Confirm delete -->
      <RcConfirmAlertDialog :ids="[editId]" :showConfirmDelete="showConfirmDelete" @close="showConfirmDelete = false" @handleDelete="handleDropdownDelete()" />
    </div>
  </main>
</template>

<style scoped>
.expandable-button {
  transition: transform 0.4s ease-in-out, box-shadow 0.4s ease-in-out, width 0.4s ease-in-out;
  overflow: hidden;
  width: auto;
  min-width: 2rem;
}

.button-icon {
  transition: margin 0.5s ease-in-out;
  margin-right: 0;
  flex-shrink: 0;
}

.button-text {
  opacity: 0;
  width: 0;
  margin-left: 0;
  transition: opacity 0.6s ease-in-out, width 0.5s ease-in-out, margin 0.5s ease-in-out;
  white-space: nowrap;
  overflow: hidden;
}

.expandable-button:hover {
  width: auto;
}

.expandable-button:hover .button-icon {
  margin-right: 0.5rem;
}

.expandable-button:hover .button-text {
  opacity: 1;
  width: auto;
  margin-left: 0;
}

/* Smooth exit */
.expandable-button:not(:hover) {
  transition: transform 0.4s ease-in-out, box-shadow 0.4s ease-in-out, width 0.4s ease-in-out;
}

.expandable-button:not(:hover) .button-icon {
  transition: margin 0.4s ease-in-out;
}

.expandable-button:not(:hover) .button-text {
  transition: opacity 0.6s ease-in-out, width 0.4s ease-in-out, margin 0.4s ease-in-out;
}

.expandable-button:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
}
</style>
