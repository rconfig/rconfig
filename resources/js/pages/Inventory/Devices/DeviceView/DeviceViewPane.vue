<script setup>
import * as monaco from 'monaco-editor';
import DeviceDetailsLeftNav from '@/pages/Inventory/Devices/DeviceView/DeviceDetailsLeftNav.vue';
import DeviceDetailsMainNav from '@/pages/Inventory/Devices/DeviceView/DeviceDetailsMainNav.vue';
import DeviceViewConfigStatusPanel from '@/pages/Inventory/Devices/DeviceView/DeviceViewConfigStatusPanel.vue';
import DeviceViewDetailsPanel from '@/pages/Inventory/Devices/DeviceView/DeviceViewDetailsPanel.vue';
import DeviceViewCommentsPanel from '@/pages/Inventory/Devices/DeviceView/DeviceViewCommentsPanel.vue';
import DeviceLatestEventsPanel from '@/pages/Inventory/Devices/DeviceView/DeviceLatestEventsPanel.vue';
import Loading from '@/pages/Shared/Loading.vue';
import useCodeEditor from '@/composables/codeEditorFunctions';
import { ScrollArea } from '@/components/ui/scroll-area';
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { useDeviceViewPane } from '@/pages/Inventory/Devices/DeviceView/useDeviceViewPane';
import { useDialogStore } from '@/stores/dialogActions';
import { useFavoritesStore } from '@/stores/favorites';

const props = defineProps({
  editId: Number
});

let meditor = null;
const emit = defineEmits(['save', 'close']);

// const { checkDarkModeIsSet, checkLineNumbersIsSet, checkMiniMapIsSet, checkStickyScrollIsSet, copied, copy, copyPath, darkmode, download, initEditor, lineNumbers, meditorValue, minimap, search, toggleEditorDarkMode, toggleEditorLineNumbers, toggleEditorMinimap, toggleStickyScroll } = useCodeEditor(monaco);
const { isLoading, deviceData } = useDeviceViewPane(props, emit);
const dialogStore = useDialogStore();
const { isDialogOpen, closeDialog } = dialogStore;
const toggleStateMultiple = ref([]); //'dark', 'lineNumbers', 'minimap', 'stickyscroll'

const leftNavSelected = ref('details');
const mainNavSelected = ref('notifications');
const favoritesStore = useFavoritesStore();

const favoriteItem = ref({
  id: props.editId,
  label: '',
  icon: 'NetworkDeviceIcon',
  isFavorite: false,
  route: '/devices/view/' + props.editId
});

// Lifecycle Hooks
onMounted(() => {
  // initializeToggleStates();
  // getTemplateRepoFolders();
  // initCodeEditor();

  window.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      onEsc();
    }
  });

  if (deviceData) {
    [...favoritesStore.favorites].forEach(favorite => {
      if (favorite.id === props.editId) {
        favoriteItem.value.isFavorite = true;
      }
    });
  }
});

function addToFavorites() {
  if (deviceData) {
    favoriteItem.value.id = deviceData.value.id;
    favoriteItem.value.label = deviceData.value.device_name;
    favoriteItem.value.route = `/devices/view/${deviceData.value.id}`;
    favoriteItem.value.isFavorite = !favoriteItem.value.isFavorite;
    favoritesStore.toggleFavorite(favoriteItem.value);
  }
}

onUnmounted(() => {
  window.removeEventListener('keydown', e => {
    if (e.key === 'Escape') {
      onEsc();
    }
  });
});

// Methods
function initializeToggleStates() {
  if (checkDarkModeIsSet()) toggleStateMultiple.value.push('dark');
  if (checkLineNumbersIsSet()) toggleStateMultiple.value.push('lineNumbers');
  if (checkMiniMapIsSet()) toggleStateMultiple.value.push('minimap');
  if (checkStickyScrollIsSet()) toggleStateMultiple.value.push('stickyscroll');
}

function initCodeEditor() {
  meditor = initEditor('code-editor__code-pre', 'yaml');
  props.editId === 0 ? getDefaultTemplate(meditor) : showTemplate(props.editId, meditor, model);
}

function close() {
  emit('close');
}

function onEsc() {
  close();
}

function handleSave() {
  saveDialog(props.editId, model, meditor, emit, close);
}

function setTemplateCode(code) {
  meditor.getModel().setValue(code.value);
  closeDialog('DialogTemplateImport');
}

function selectLeftNavView(viewName) {
  if (viewName === 'details') {
    leftNavSelected.value = 'details';
  } else if (viewName === 'comments') {
    leftNavSelected.value = 'comments';
  } else {
    leftNavSelected.value = 'details';
  }
}
function selectMainNavView(viewName) {
  if (viewName === 'notifications') {
    mainNavSelected.value = 'notifications';
  } else if (viewName === 'configs') {
    mainNavSelected.value = 'configs';
  } else {
    mainNavSelected.value = 'notifications';
  }
}
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
