<script setup>
import * as monaco from 'monaco-editor';
import useCodeEditor from '@/composables/codeEditorFunctions';
import { ref, onMounted, onUnmounted } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useDialogStore } from '@/stores/dialogActions';
import { Button } from '@/components/ui/button';
import axios from 'axios';
import { useToaster } from '@/composables/useToaster'; // Import the composable
const { toastSuccess, toastError, toastInfo, toastWarning, toastDefault } = useToaster();
const { checkDarkModeIsSet, checkLineNumbersIsSet, checkMiniMapIsSet, copied, copy, darkmode, download, initEditor, lineNumbers, minimap, toggleEditorDarkMode, toggleEditorLineNumbers, toggleEditorMinimap } = useCodeEditor(monaco);

const dialogStore = useDialogStore();
const { openDialog, closeDialog, isDialogOpen } = dialogStore;
const emit = defineEmits(['save', 'cancel']);
const roles = ref([]);
const errors = ref([]);
const model = ref({
  templatename: '',
  templateDescription: ''
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

  checkDarkModeIsSet();
  checkLineNumbersIsSet();
  checkMiniMapIsSet();
  // getTemplateRepoFolders();

  if (props.editId === 0) {
    getDefaultTemplate();
  } else {
    // showTemplate();
  }
  // getModel(props.viewstate.editid);
  meditor = initEditor('pf-v5-c-code-editor__code-pre', 'yaml');
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
      createNotification({
        type: 'danger',
        title: 'Error',
        message: error.response
      });
    });
}

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});

function saveDialog() {
  let id = props.editId > 0 ? `/${props.editId}` : ''; // determine if we are creating or updating
  let method = props.editId > 0 ? 'patch' : 'post'; // determine if we are creating or updating
  axios[method]('/api/templates' + id, model.value)
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
  <div class="grid gap-2 py-4">
    <div class="grid items-center grid-cols-4 gap-4">
      <Label
        for="templatename"
        class="text-right">
        Template Name
      </Label>
      <Input
        v-model="model.templatename"
        id="templatename"
        class="col-span-3" />
    </div>

    <div class="grid items-center grid-cols-4 gap-4">
      <Label
        for="templateDescription"
        class="text-right">
        Description
      </Label>
      <Input
        v-model="model.templateDescription"
        id="templateDescription"
        class="col-span-3" />
    </div>
  </div>
  <div class="pf-v5-c-form__group pf-l-grid__item pf-m-9-col">
    <div class="pf-v5-c-form__group-control">
      <div class="pf-v5-c-code-editor">
        <div class="pf-v5-c-code-editor__header">
          <div class="pf-v5-c-code-editor__controls">
            <button
              class="pf-v5-c-button pf-m-control"
              type="button"
              alt="Copy to clipboard"
              title="Copy to clipboard"
              @click="copy">
              <i
                class="fas fa-copy"
                aria-hidden="true"></i>
            </button>

            <button
              class="pf-v5-c-button pf-m-control"
              type="button"
              title="Download code"
              alt="Download code"
              @click="download(model.fileName)">
              <i class="fas fa-download"></i>
            </button>
            <button
              class="pf-v5-c-button pf-m-control"
              type="button"
              title="full screen"
              alt="full screen"
              @click="showConfigFullScreen">
              <i class="fas fa-expand"></i>
            </button>

            <div
              class="pf-v5-c-check"
              style="align-content: center">
              <input
                class="pf-v5-c-check__input"
                type="checkbox"
                id="darkmode"
                name="darkmode"
                @change="toggleEditorDarkMode($event)"
                :checked="darkmode == 'vs-dark'"
                style="margin-left: 0.5rem" />

              <label
                class="pf-v5-c-check__label"
                style="cursor: default">
                Dark Mode
              </label>
            </div>
            <div
              class="pf-v5-c-check"
              style="align-content: center">
              <input
                class="pf-v5-c-check__input"
                type="checkbox"
                id="lineNumbers"
                name="lineNumbers"
                @change="toggleEditorLineNumbers($event)"
                :checked="lineNumbers == 'on'"
                style="margin-left: 0.5rem" />

              <label
                class="pf-v5-c-check__label"
                style="cursor: default">
                Line Numbers
              </label>
            </div>
            <div
              class="pf-v5-c-check"
              style="align-content: center">
              <input
                class="pf-v5-c-check__input"
                type="checkbox"
                id="lineNumbers"
                name="lineNumbers"
                @change="toggleEditorMinimap($event)"
                :checked="minimap.enabled == 'true'"
                style="margin-left: 0.5rem" />
              <label
                class="pf-v5-c-check__label"
                style="cursor: default">
                Minimap
              </label>
            </div>
          </div>
          <div class="pf-v5-c-code-editor__tab">
            <span class="pf-v5-c-code-editor__tab-icon">
              <i class="fas fa-code"></i>
            </span>
            <span class="pf-v5-c-code-editor__tab-text">YAML</span>
          </div>
        </div>
        <div
          class="pf-v5-c-code-editor__main"
          id="pf-v5-c-code-editor__main">
          <code class="pf-v5-c-code-editor__code">
            <div
              class="pf-v5-c-code-editor__code-pre"
              id="pf-v5-c-code-editor__code-pre"
              style="height: calc(100vh - 400px)"></div>
          </code>
        </div>
        <p
          v-if="errors.code"
          class="pf-v5-c-form__helper-text pf-m-error"
          id="form-help-text-address-helper"
          aria-live="polite">
          {{ errors.code[0] }}
        </p>
      </div>
    </div>
  </div>

  <div class="flex flex-col w-full space-y-2">
    <span
      class="text-red-400"
      v-if="errors.templateDescription">
      {{ errors.templateDescription[0] }}
    </span>

    <span
      class="text-red-400"
      v-if="errors.templatename">
      {{ errors.templatename[0] }}
    </span>
  </div>
  <!-- <DialogFooter>   -->
  <div class="flex justify-end">
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
</template>
