<script setup>
import * as monaco from 'monaco-editor';
import useCodeEditor from '@/composables/codeEditorFunctions';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { ref, onMounted } from 'vue';

const props = defineProps({
  leftSelectedId: Number,
  rightSelectedId: Number,
  configResultsLeft: Object,
  configResultsRight: Object
});

const toggleStateMultiple = ref([]); //'dark', 'lineNumbers', 'minimap', 'stickyscroll'
const { checkDarkModeIsSet, checkLineNumbersIsSet, checkMiniMapIsSet, checkStickyScrollIsSet, copied, copyItem, copyPath, darkmode, download, initDiffEditor, lineNumbers, meditorValue, minimap, search, toggleEditorDarkMode, toggleEditorLineNumbers, toggleEditorMinimap, toggleStickyScroll } = useCodeEditor(monaco);

onMounted(() => {
  initializeToggleStates();
  initCodeEditor();
});

function initializeToggleStates() {
  if (checkDarkModeIsSet()) toggleStateMultiple.value.push('dark');
  if (checkLineNumbersIsSet()) toggleStateMultiple.value.push('lineNumbers');
  //   if (checkMiniMapIsSet()) toggleStateMultiple.value.push('minimap');
  //   if (checkStickyScrollIsSet()) toggleStateMultiple.value.push('stickyscroll');
}

function initCodeEditor() {
  var originalModel = monaco.editor.createModel(props.configResultsLeft.filecontent, 'text/plain');
  var modifiedModel = monaco.editor.createModel(props.configResultsRight.filecontent, 'text/plain');

  let diffEditor = initDiffEditor('code-editor__code-pre', 'rconfig');

  if (diffEditor) {
    diffEditor.setModel({
      original: originalModel,
      modified: modifiedModel
    });
  }
}
</script>

<template>
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
          <!-- <ToggleGroupItem
            value="minimap"
            title="Minimap"
            @click="toggleEditorMinimap()">
            <Icon
              icon="tabler:map-2"
              class="w-4 h-4" />
          </ToggleGroupItem> -->
        </ToggleGroup>
      </div>
      <!-- RIGHT BUTTONS -->

      <Separator
        orientation="vertical"
        class="relative w-px h-6 mx-4 shrink-0 bg-border" />
      <span>CONFIG</span>
    </div>
    <Separator class="relative w-full h-px shrink-0 bg-border"></Separator>
  </div>
  <div
    class="code-editor__code-pre"
    id="code-editor__code-pre"
    style="height: calc(80vh)"></div>
  <!-- EDITOR -->
  <!-- <pre style="white-space: pre-wrap"> {{ configResultsLeft }}</pre>
  <pre style="white-space: pre-wrap"> {{ configResultsRight }}</pre> -->
  <!-- EDITOR -->
</template>
