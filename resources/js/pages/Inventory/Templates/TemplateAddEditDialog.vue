<script setup>
import * as monaco from 'monaco-editor';
import useCodeEditor from '@/composables/codeEditorFunctions';
import { ref, onMounted, onUnmounted } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';

import axios from 'axios';
import { useToaster } from '@/composables/useToaster'; // Import the composable
const { toastSuccess, toastError, toastInfo, toastWarning, toastDefault } = useToaster();
const { checkDarkModeIsSet, checkLineNumbersIsSet, checkMiniMapIsSet, checkStickyScrollIsSet, copied, copy, copyPath, darkmode, download, initEditor, lineNumbers, meditorValue, minimap, search, toggleEditorDarkMode, toggleEditorLineNumbers, toggleEditorMinimap, toggleStickyScroll } = useCodeEditor(monaco);

const emit = defineEmits(['save', 'cancel']);
const errors = ref([]);
const toggleStateMultiple = ref([]); //'dark', 'lineNumbers', 'minimap', 'stickyscroll'

const model = ref({
  code: '',
  templateName: '',
  description: ''
});
const code = ref('');
let meditor = null;

const props = defineProps({
  editId: Number
});

function handleKeyDown(event) {
  if (event.ctrlKey && event.key === 'Enter') {
    saveDialog();
  }
}

onMounted(() => {
  if (props.editId > 0) {
    axios.get(`/api/templates/${props.editId}`).then(response => {
      model.value = response.data;
    });
  }

  window.addEventListener('keydown', handleKeyDown);

  checkDarkModeIsSet() === true ? toggleStateMultiple.value.push('dark') : '';
  checkLineNumbersIsSet() === true ? toggleStateMultiple.value.push('lineNumbers') : '';
  checkMiniMapIsSet() === true ? toggleStateMultiple.value.push('minimap') : '';
  checkStickyScrollIsSet() === true ? toggleStateMultiple.value.push('stickyscroll') : '';
  // getTemplateRepoFolders();

  if (props.editId === 0) {
    getDefaultTemplate();
  } else {
    // showTemplate();
  }
  // getModel(props.viewstate.editid);
  meditor = initEditor('code-editor__code-pre', 'yaml');
});

function getDefaultTemplate() {
  axios
    .get('/api/get-default-template')
    .then(response => {
      // handle success
      // model.fileName = 'default.yml';
      code.value = response.data;
      meditor.setValue(response.data);
    })
    .catch(error => {
      meditor.updateOptions({
        value: 'Something went wrong - could not retrieve the default template from the file system!'
      });
      toastError('Error', 'Something went wrong - could not retrieve the default template from the file system!');
    });
}

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});

function saveDialog() {
  model.value.code = meditor.getValue();
  console.log(model.value);

  let id = props.editId > 0 ? `/${props.editId}` : ''; // determine if we are creating or updating
  let method = props.editId > 0 ? 'patch' : 'post'; // determine if we are creating or updating

  axios[method]('/api/templates' + id, {
    templateName: model.value.templateName,
    description: model.value.description,
    code: model.value.code
  })
    .then(response => {
      emit('save', response.data);
      toastSuccess('Template created', 'The template has been created successfully.');
      close();
    })
    .catch(error => {
      errors.value = error.response.data.errors;
    });
}

function close() {
  emit('close');
}
</script>

<template>
  <div>
    {{ model }}
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
    </div>
    <div
      class="grid items-center grid-cols-4 col-start-4 gap-4"
      v-if="errors">
      <span
        class="col-span-1 col-start-2 text-red-400"
        v-if="errors.templateName">
        {{ errors.templateName[0] }}
      </span>

      <span
        class="col-span-1 col-start-2 text-red-400"
        v-if="errors.description">
        {{ errors.description[0] }}
      </span>

      <span
        class="col-span-1 col-start-2 text-red-400"
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
            @click="copy"
            title="copy">
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
      style="height: calc(100vh - 400px)"></div>
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
          <kbd class="bxnAJf">ESC</kbd>
        </div>
      </Button>

      <Button
        v-if="props.editId === 0"
        type="submit"
        class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
        size="sm"
        @click="saveDialog()"
        variant="primary">
        Save
        <div class="pl-2 ml-auto">
          <kbd class="bxnAJf2">
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
        @click="saveDialog()"
        variant="primary">
        Update
        <div class="pl-2 ml-auto">
          <kbd class="bxnAJf2">
            Ctrl&nbsp;
            <Icon
              icon="uil:enter"
              class="" />
          </kbd>
        </div>
      </Button>
    </div>
  </div>
</template>
