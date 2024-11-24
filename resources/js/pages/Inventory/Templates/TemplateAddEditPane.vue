<script setup>
import * as monaco from 'monaco-editor';
import TemplateImportDialog from '@/pages/Inventory/Templates/TemplateImportDialog.vue';
import useCodeEditor from '@/composables/codeEditorFunctions';
import useTemplateAddEdit from '@/pages/Inventory/Templates/useTemplateAddEdit';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { ref, onMounted, onUnmounted } from 'vue';
import { useDialogStore } from '@/stores/dialogActions';
import { useTemplatesGithub } from '@/pages/Inventory/Templates/useTemplatesGithub';
const props = defineProps({
  editId: Number
});
let meditor = null;
const emit = defineEmits(['save', 'close']);

const { checkDarkModeIsSet, checkLineNumbersIsSet, checkMiniMapIsSet, checkStickyScrollIsSet, copied, copyItem, copyPath, darkmode, download, initEditor, lineNumbers, meditorValue, minimap, search, toggleEditorDarkMode, toggleEditorLineNumbers, toggleEditorMinimap, toggleStickyScroll } = useCodeEditor(monaco);
const { openImportDialog, getTemplateRepoFolders, hasVendorTemplateOptions } = useTemplatesGithub();
const { errors, code, model, getDefaultTemplate, showTemplate, saveDialog, handleKeyDown, fetchTemplateData } = useTemplateAddEdit(props, emit);
const dialogStore = useDialogStore();
const { isDialogOpen, closeDialog } = dialogStore;

const toggleStateMultiple = ref([]); //'dark', 'lineNumbers', 'minimap', 'stickyscroll'

// Lifecycle Hooks
onMounted(() => {
  initializeToggleStates();
  getTemplateRepoFolders();
  initCodeEditor();

  window.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      onEsc();
    }
  });
});

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
</script>

<template>
  <div class="p-4 px-8">
    <div class="grid gap-2 py-4">
      <div class="grid items-center grid-cols-4 gap-4">
        <Label
          for="templateName"
          class="text-right">
          Template Name

          <span class="text-red-600">*</span>
        </Label>
        <Input
          v-model="model.templateName"
          id="templateName"
          autocomplete="off"
          class="col-span-3" />
      </div>

      <div class="grid items-center grid-cols-4 gap-4">
        <Label
          for="description"
          class="text-right">
          Description
        </Label>
        <Input
          v-model="model.description"
          id="description"
          autocomplete="off"
          class="col-span-3" />
      </div>
      <div
        class="grid items-center grid-cols-4 gap-4"
        v-if="hasVendorTemplateOptions">
        <Label
          for="description"
          class="text-right">
          Imported Templates
        </Label>
        <Button
          type="close"
          @click="openImportDialog()"
          class="px-2 py-1 text-sm hover:bg-gray-700"
          variant="outline">
          <Icon
            icon="mdi:github"
            class="mr-2" />
          Choose an Imported Templates
        </Button>
      </div>
    </div>
    <div
      class="grid items-center grid-cols-4 col-start-4 gap-1"
      v-if="errors">
      <span
        class="col-span-1 col-start-2 text-sm text-red-400"
        v-if="errors.templateName">
        {{ errors.templateName[0] }}
      </span>

      <span
        class="col-span-1 col-start-2 text-sm text-red-400"
        v-if="errors.description">
        {{ errors.description[0] }}
      </span>

      <span
        class="col-span-1 col-start-2 text-sm text-red-400"
        v-if="errors.code">
        {{ errors.code[0] }}
      </span>
    </div>

    <div class="flex flex-col">
      <div class="flex items-center p-2">
        <div class="flex items-center gap-2"></div>

        <!-- LEFT BUTTONS -->
        <div>
          <Button
            variant="ghost"
            @click="copyItem"
            title="copyItem">
            <Icon icon="mdi:content-copy"></Icon>
          </Button>

          <Button
            variant="ghost"
            class="ml-1"
            @click="download(model.fileName)"
            title="download">
            <Icon icon="mingcute:download-fill"></Icon>
          </Button>

          <!-- <Button
            variant="ghost"
            class="ml-1"
            @click="showConfigFullScreen()"
            title="Full Screen">
            <Icon icon="ant-design:fullscreen-outlined"></Icon>
          </Button> -->
        </div>
        <!-- LEFT BUTTONS -->

        <Separator
          orientation="vertical"
          class="relative w-px h-6 mx-4 shrink-0 bg-border" />

        <!-- RIGHT BUTTONS -->
        <div class="flex items-center gap-2 ml-auto">
          <ToggleGroup
            v-model="toggleStateMultiple"
            type="multiple">
            <ToggleGroupItem
              value="dark"
              title="Toggle bold"
              @click="toggleEditorDarkMode()">
              <Icon
                icon="mdi:theme-light-dark"
                class="w-4 h-4" />
            </ToggleGroupItem>
            <ToggleGroupItem
              value="lineNumbers"
              title="Line Numbers"
              @click="toggleEditorLineNumbers()">
              <Icon
                icon="mingcute:numbers-09-sort-ascending-line"
                class="w-4 h-4" />
            </ToggleGroupItem>
            <ToggleGroupItem
              value="minimap"
              title="Minimap"
              @click="toggleEditorMinimap()">
              <Icon
                icon="tabler:map-2"
                class="w-4 h-4" />
            </ToggleGroupItem>
            <ToggleGroupItem
              value="stickyscroll"
              title="Sticky Scroll"
              @click="toggleStickyScroll()">
              <Icon
                icon="clarity:scroll-outline-alerted"
                class="w-4 h-4" />
            </ToggleGroupItem>
          </ToggleGroup>
        </div>
        <!-- RIGHT BUTTONS -->

        <Separator
          orientation="vertical"
          class="relative w-px h-6 mx-4 shrink-0 bg-border" />
        <span>YAML</span>
      </div>
      <Separator class="relative w-full h-px shrink-0 bg-border"></Separator>
    </div>

    <!-- EDITOR -->
    <div
      class="code-editor__code-pre"
      id="code-editor__code-pre"
      style="height: calc(100vh - 450px)"></div>
    <!-- EDITOR -->

    <div class="flex justify-end pt-4">
      <Button
        type="close"
        variant="outline"
        class="px-2 py-1 ml-2 text-sm hover:bg-gray-700 hover:animate-pulse"
        @click="close()"
        size="sm">
        Cancel
        <div class="pl-2 ml-auto">
          <kbd class="rc-kdb-class">ESC</kbd>
        </div>
      </Button>

      <Button
        v-if="props.editId === 0"
        type="submit"
        class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
        size="sm"
        @click="handleSave()"
        variant="primary">
        Save
        <div class="pl-2 ml-auto">
          <kbd class="rc-kdb-class2">
            Ctrl&nbsp;
            <Icon
              icon="uil:enter"
              class="" />
          </kbd>
        </div>
      </Button>

      <Button
        v-if="props.editId > 0"
        type="submit"
        class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
        size="sm"
        @click="handleSave()"
        variant="primary">
        Update
        <div class="pl-2 ml-auto">
          <kbd class="rc-kdb-class2">
            Ctrl&nbsp;
            <Icon
              icon="uil:enter"
              class="" />
          </kbd>
        </div>
      </Button>
    </div>
    <TemplateImportDialog
      v-if="isDialogOpen('DialogTemplateImport')"
      @setTemplateCode="setTemplateCode($event)" />
  </div>
</template>
