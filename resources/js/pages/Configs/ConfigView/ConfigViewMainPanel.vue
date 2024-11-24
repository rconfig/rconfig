<script setup>
import * as monaco from 'monaco-editor';
import { ref, onMounted, onUnmounted } from 'vue';
import useCodeEditor from '@/composables/codeEditorFunctions';
import useConfigViewMainPanel from '@/pages/Configs/ConfigView/useConfigViewMainPanel';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';

const props = defineProps({
  configId: Number
});

let meditor = null;

const { checkDarkModeIsSet, checkLineNumbersIsSet, checkMiniMapIsSet, checkStickyScrollIsSet, copied, copyItem, copyPath, darkmode, download, initEditor, lineNumbers, meditorValue, minimap, search, toggleEditorDarkMode, toggleEditorLineNumbers, toggleEditorMinimap, toggleStickyScroll } = useCodeEditor(monaco);
const { errors, getDefaultEditorCode, showConfiguration, saveDialog, config_location } = useConfigViewMainPanel(props);

const isLoading = ref(false);
const toggleStateMultiple = ref([]); //'dark', 'lineNumbers', 'minimap', 'stickyscroll'

// Lifecycle Hooks
onMounted(() => {
  initializeToggleStates();
  initCodeEditor();
});

// Methods
function initializeToggleStates() {
  if (checkDarkModeIsSet()) toggleStateMultiple.value.push('dark');
  if (checkLineNumbersIsSet()) toggleStateMultiple.value.push('lineNumbers');
  if (checkMiniMapIsSet()) toggleStateMultiple.value.push('minimap');
  if (checkStickyScrollIsSet()) toggleStateMultiple.value.push('stickyscroll');
}

function initCodeEditor() {
  meditor = initEditor('code-editor__code-pre', 'rconfig');
  props.configId === 0 ? getDefaultEditorCode(meditor) : showConfiguration(props.configId, meditor);
}
</script>

<template>
  <div>
    <div class="p-2 overflow-none">
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
        class="space-y-4"
        v-if="!isLoading">
        <div class="p-4 px-8">
          <div class="flex flex-col">
            <div class="flex items-center p-2">
              <div class="flex items-center gap-2"></div>

              <!-- LEFT BUTTONS -->
              <div>
                <Button
                  variant="ghost"
                  @click="copyPath(config_location)"
                  title="copy path">
                  <Icon icon="lucide-lab:copy-file-path"></Icon>
                </Button>

                <Button
                  variant="ghost"
                  @click="copyItem()"
                  title="copy content">
                  <Icon icon="mdi:content-copy"></Icon>
                </Button>

                <Button
                  variant="ghost"
                  class="ml-1"
                  @click="download(config_location)"
                  title="download">
                  <Icon icon="mingcute:download-fill"></Icon>
                </Button>
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
                  <!-- <ToggleGroupItem
                   disabled for configs as it is not needed
                    value="stickyscroll"
                    title="Sticky Scroll"
                    @click="toggleStickyScroll()">
                    <Icon
                      icon="clarity:scroll-outline-alerted"
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

          <!-- EDITOR -->
          <div
            class="code-editor__code-pre"
            id="code-editor__code-pre"
            style="height: calc(100vh - 300px)"></div>
          <!-- EDITOR -->
        </div>
      </div>
    </div>
  </div>
</template>
