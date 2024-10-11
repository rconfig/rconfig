<template>
  <loading-spinner :showSpinner="isLoading"></loading-spinner>

  <form
    novalidate
    class="pf-c-form">
    <input
      id="editid"
      name="editid"
      type="hidden"
      :value="viewstate.editid" />
    <div class="pf-l-grid pf-m-gutter">
      <div class="pf-l-grid__item pf-m-3-col">
        <div class="pf-c-form__group">
          <div class="pf-c-form__group-label">
            <label class="pf-c-form__label">
              <span class="pf-c-form__label-text">File name</span>
              <span
                class="pf-c-form__label-required"
                aria-hidden="true">
                &#42;
              </span>
            </label>
          </div>
          <div class="pf-c-form__group-control">
            <input
              class="pf-c-form-control"
              required
              type="text"
              id="fileName"
              name="fileName"
              v-model="model.fileName"
              :aria-invalid="errors.fileName ? true : false"
              autocomplete="off" />
            <p
              v-if="errors.fileName"
              class="pf-c-form__helper-text pf-m-error"
              id="form-help-text-address-helper"
              aria-live="polite">
              {{ errors.fileName[0] }}
            </p>
            <p
              v-if="showSelectTemplateFields"
              class="pf-c-form__helper-text">
              You may overwrite this filename if a template was selected
            </p>
          </div>
        </div>
        <button
          class="pf-c-button pf-m-link pf-u-pl-xs pf-u-pl-xs pf-u-mt-lg pf-u-mb-md"
          type="button"
          @click="showSelectTemplateFields = true"
          v-if="hasVendorTemplateOptions && !showSelectTemplateFields">
          Click to choose from imported templates
        </button>
        <div
          class="pf-c-form__group pf-u-mt-lg"
          v-if="showSelectTemplateFields">
          <div class="pf-c-form__group-label">
            <label class="pf-c-form__label">
              <span class="pf-c-form__label-text">Select Vendor</span>
            </label>
          </div>

          <div
            class="pf-c-select pf-m-expanded"
            ref="clickOutsidetarget1">
            <span hidden>Choose an option</span>
            <button
              class="pf-c-select__toggle"
              type="button"
              @click.prevent="showVendorTemplateOptions = !showVendorTemplateOptions">
              <div class="pf-c-select__toggle-wrapper">
                <span
                  class="pf-c-select__toggle-text"
                  v-text="vendorTemplateOptionSelected.length != 0 ? vendorTemplateOptionSelected : 'Choose a vendor'"></span>
              </div>
              <span class="pf-c-select__toggle-arrow">
                <i
                  class="fas fa-caret-down"
                  aria-hidden="true"></i>
              </span>
            </button>
            <div v-if="showVendorTemplateOptions ? 'hidden' : ''">
              <ul
                class="pf-c-select__menu multi-select-dropdown-overflow"
                role="listbox">
                <li
                  role="presentation"
                  v-for="option in vendorTemplateOptions.data"
                  :key="option.name">
                  <button
                    class="pf-c-select__menu-item"
                    role="option"
                    @click.prevent="getTemplatesList(option)">
                    {{ option.name }}
                    <span
                      class="pf-c-select__menu-item-icon"
                      v-if="option.name === vendorTemplateOptionSelected">
                      <i
                        class="fas fa-check"
                        aria-hidden="true"></i>
                    </span>
                  </button>
                </li>
              </ul>
            </div>
          </div>
          <p class="pf-c-form__helper-text">Select a vendor first, then select a template below</p>
          <p
            class="pf-c-form__helper-text pf-u-mb-xl"
            v-if="hasReadmeFile">
            <a
              :href="'https://github.com/rconfig/rConfig-templates/tree/master/' + vendorTemplateOptionSelected"
              target="_blank">
              View readme documents
            </a>
            &nbsp;&nbsp;
            <i class="fas fa-external-link-alt pf-u-font-size-xs pf-u-color-400"></i>
          </p>
        </div>
        <div
          class="pf-c-form__group pf-u-mt-sm"
          v-if="showSelectTemplateFields && hasListedFiles">
          <div class="pf-c-form__group-label">
            <label class="pf-c-form__label">
              <span class="pf-c-form__label-text">Select Template</span>
            </label>
          </div>
          <div
            class="pf-c-select pf-m-expanded"
            ref="clickOutsidetarget2">
            <span hidden>Choose an option</span>
            <button
              class="pf-c-select__toggle"
              type="button"
              @click.prevent="showFileOptions = !showFileOptions">
              <div class="pf-c-select__toggle-wrapper">
                <span
                  class="pf-c-select__toggle-text"
                  v-text="fileOptionSelected.length != 0 ? fileOptionSelected : 'Choose a template'"></span>
              </div>
              <span class="pf-c-select__toggle-arrow">
                <i
                  class="fas fa-caret-down"
                  aria-hidden="true"></i>
              </span>
            </button>
            <div v-if="showFileOptions ? 'hidden' : ''">
              <ul
                class="pf-c-select__menu multi-select-dropdown-overflow"
                role="listbox">
                <li
                  role="presentation"
                  v-for="option in listedFiles.data"
                  :key="option.name">
                  <button
                    class="pf-c-select__menu-item"
                    role="option"
                    @click.prevent="getTemplateFileContents(option)">
                    {{ option.name }}
                    <span
                      class="pf-c-select__menu-item-icon"
                      v-if="option.name === fileOptionSelected">
                      <i
                        class="fas fa-check"
                        aria-hidden="true"></i>
                    </span>
                  </button>
                </li>
              </ul>
            </div>
          </div>
          <p class="pf-c-form__helper-text pf-u-warning-color-100">Warning: Selecting a template will instantly overwrite any edits in the yaml file</p>
        </div>
        <div class="pf-c-form__group pf-m-action">
          <div class="pf-c-form__group-control">
            <div class="pf-c-form__actions">
              <button
                class="pf-c-button pf-m-primary"
                type="submit"
                @click.prevent="saveModels">
                Save
              </button>
              <button
                class="pf-c-button pf-m-link"
                type="button"
                @click="close">
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
      <div class="pf-c-form__group pf-l-grid__item pf-m-9-col">
        <!-- by default, `copied` will be reset in 1.5s -->
        <div
          class="pf-c-tooltip pf-m-top-left"
          role="tooltip"
          v-if="copied"
          style="z-index: 999; position: absolute; top: 26%">
          <div class="pf-c-tooltip__arrow"></div>
          <div
            class="pf-c-tooltip__content"
            id="tooltip-top-content">
            Copied!
          </div>
        </div>
        <div class="pf-c-form__group-control">
          <div class="pf-c-code-editor">
            <div class="pf-c-code-editor__header">
              <div class="pf-c-code-editor__controls">
                <button
                  class="pf-c-button pf-m-control"
                  type="button"
                  alt="Copy to clipboard"
                  title="Copy to clipboard"
                  @click="copy">
                  <i
                    class="fas fa-copy"
                    aria-hidden="true"></i>
                </button>

                <button
                  class="pf-c-button pf-m-control"
                  type="button"
                  title="Download code"
                  alt="Download code"
                  @click="download(model.fileName)">
                  <i class="fas fa-download"></i>
                </button>
                <button
                  class="pf-c-button pf-m-control"
                  type="button"
                  title="full screen"
                  alt="full screen"
                  @click="showConfigFullScreen">
                  <i class="fas fa-expand"></i>
                </button>

                <div
                  class="pf-c-check"
                  style="align-content: center">
                  <input
                    class="pf-c-check__input"
                    type="checkbox"
                    id="darkmode"
                    name="darkmode"
                    @change="toggleEditorDarkMode($event)"
                    :checked="darkmode == 'vs-dark'"
                    style="margin-left: 0.5rem" />

                  <label
                    class="pf-c-check__label"
                    style="cursor: default">
                    Dark Mode
                  </label>
                </div>
                <div
                  class="pf-c-check"
                  style="align-content: center">
                  <input
                    class="pf-c-check__input"
                    type="checkbox"
                    id="lineNumbers"
                    name="lineNumbers"
                    @change="toggleEditorLineNumbers($event)"
                    :checked="lineNumbers == 'on'"
                    style="margin-left: 0.5rem" />

                  <label
                    class="pf-c-check__label"
                    style="cursor: default">
                    Line Numbers
                  </label>
                </div>
                <div
                  class="pf-c-check"
                  style="align-content: center">
                  <input
                    class="pf-c-check__input"
                    type="checkbox"
                    id="lineNumbers"
                    name="lineNumbers"
                    @change="toggleEditorMinimap($event)"
                    :checked="minimap.enabled == 'true'"
                    style="margin-left: 0.5rem" />
                  <label
                    class="pf-c-check__label"
                    style="cursor: default">
                    Minimap
                  </label>
                </div>
              </div>
              <div class="pf-c-code-editor__tab">
                <span class="pf-c-code-editor__tab-icon">
                  <i class="fas fa-code"></i>
                </span>
                <span class="pf-c-code-editor__tab-text">YAML</span>
              </div>
            </div>
            <div
              class="pf-c-code-editor__main"
              id="pf-c-code-editor__main">
              <code class="pf-c-code-editor__code">
                <div
                  class="pf-c-code-editor__code-pre"
                  id="pf-c-code-editor__code-pre"
                  style="height: calc(100vh - 400px)"></div>
              </code>
            </div>
            <p
              v-if="errors.code"
              class="pf-c-form__helper-text pf-m-error"
              id="form-help-text-address-helper"
              aria-live="polite">
              {{ errors.code[0] }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </form>
</template>

<script>
import * as monaco from 'monaco-editor';
import LoadingSpinner from '../components/LoadingSpinner.vue';
import useCodeEditor from '../composables/codeEditorFunctions';
import useModels from '../composables/ModelsFactory';
import { ref, onMounted, inject } from 'vue';
import { onClickOutside } from '@vueuse/core';

export default {
  props: {
    viewstate: {
      type: Object
    }
  },
  emits: ['closeDrawer', 'formsubmitted', 'showConfigFullScreen'],

  components: {
    LoadingSpinner
  },

  setup(props, { emit }) {
    const code = ref('');
    const clickOutsidetarget1 = ref(null);
    const clickOutsidetarget2 = ref(null);
    const createNotification = inject('create-notification');
    const fileOptionSelected = ref([]);
    const formtype = ref(props.viewstate.editid === 0 ? 'add' : 'edit');
    const hasListedFiles = ref(false);
    const hasVendorTemplateOptions = ref(false);
    const listedFiles = ref([]);
    const showFileOptions = ref(false);
    const showRoleOptions = ref(false);
    const showSelectTemplateFields = ref(false);
    const showVendorTemplateOptions = ref(false);
    const vendorTemplateOptionSelected = ref([]);
    const vendorTemplateOptions = ref([]);
    const hasReadmeFile = ref(false);
    const { errors, model, clearModel, updateModel, getModel, storeModel, isLoading } = useModels(props.viewstate.modelName, props.viewstate.modelObject);
    let meditor = null;

    onClickOutside(clickOutsidetarget1, event => (showVendorTemplateOptions.value = false));
    onClickOutside(clickOutsidetarget2, event => (showFileOptions.value = false));

    const { checkDarkModeIsSet, checkLineNumbersIsSet, checkMiniMapIsSet, copied, copy, darkmode, download, initEditor, lineNumbers, minimap, toggleEditorDarkMode, toggleEditorLineNumbers, toggleEditorMinimap } = useCodeEditor(monaco);

    onMounted(() => {
      checkDarkModeIsSet();
      checkLineNumbersIsSet();
      checkMiniMapIsSet();
      getTemplateRepoFolders();

      if (props.viewstate.editid === 0) {
        getDefaultTemplate();
      } else {
        showTemplate();
      }
      getModel(props.viewstate.editid);
      meditor = initEditor('pf-c-code-editor__code-pre', 'yaml');
    });

    function getDefaultTemplate() {
      axios
        .get('/api/get-default-template')
        .then(response => {
          // handle success
          model.fileName = 'default.yml';
          code.value = response.data;
          meditor.getModel().setValue(response.data);
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

    function showTemplate() {
      axios
        .get('/api/templates/' + props.viewstate.editid)
        .then(response => {
          // handle success
          model.fileName = response.data.fileName;
          code.value = response.data.code;
          meditor.getModel().setValue(response.data.code);
        })
        .catch(error => {
          meditor.updateOptions({
            value: 'Something went wrong - could not retrieve the template from the file system!'
          });
          createNotification({
            type: 'danger',
            title: 'Error',
            message: error.response
          });
        });
    }

    const saveModels = async () => {
      if (props.viewstate.editid != 0) {
        model.code = meditor.getValue();
        await updateModel(model);
      } else {
        model.code = meditor.getValue();
        await storeModel(model);
      }

      if (errors.value === '') {
        emit('formsubmitted', props.viewstate.pagenamesingle + ' ' + formtype.value + 'ed!');
        close();
      }
    };

    function close() {
      emit('closeDrawer');
    }

    function showConfigFullScreen() {
      console.log(model.fileName);
      emit('showConfigFullScreen', { code: code.value, filename: model.fileName });
    }

    function getTemplateRepoFolders() {
      axios
        .get('/api/list-template-repo-folders', {})
        .then(response => {
          // console.log(response.data.data);
          vendorTemplateOptions.value = response.data.data;
          hasVendorTemplateOptions.value = true;
        })
        .catch(error => {
          if (error.response.data.message.msg === 'rConfig-templates is empty, or does not exist. Clone from "https://github.com/rconfig/rconfig-templates" may have failed! Try importing the templates again.!') {
            hasVendorTemplateOptions.value = false;
          } else {
            createNotification({
              type: 'danger',
              title: 'Error',
              message: error.response.data.message
            });
          }
        });
    }

    function getTemplatesList(vendorOption) {
      showFileOptions.value = false;
      showVendorTemplateOptions.value = false;
      vendorTemplateOptionSelected.value = vendorOption.name;
      hasReadmeFile.value = false;
      axios
        .post('/api/list-repo-folders-contents', { directory: vendorOption.path })
        .then(response => {
          console.log(response.data);
          hasListedFiles.value = true;
          listedFiles.value = response.data.data;
          if (typeof response.data.data.readme !== 'undefined') {
            hasReadmeFile.value = true;
          }
        })
        .catch(error => {
          createNotification({
            type: 'danger',
            title: 'Error',
            message: error.response.data.message
          });
        });
    }

    function getTemplateFileContents(fileOption) {
      showFileOptions.value = false;
      showVendorTemplateOptions.value = false;
      fileOptionSelected.value = fileOption.name;
      axios
        .post('/api/get-template-file-contents', { filepath: fileOption.path })
        .then(response => {
          meditor.getModel().setValue(response.data.data.data.code);
          model.fileName = fileOption.path.split('/').reverse()[0];
        })
        .catch(error => {
          createNotification({
            type: 'danger',
            title: 'Error',
            message: error.response.data.message
          });
        });
    }

    return {
      clearModel,
      clickOutsidetarget1,
      clickOutsidetarget2,
      close,
      hasReadmeFile,
      copied,
      copy,
      darkmode,
      download,
      errors,
      fileOptionSelected,
      getTemplateFileContents,
      getTemplatesList,
      hasListedFiles,
      hasVendorTemplateOptions,
      isLoading,
      lineNumbers,
      listedFiles,
      minimap,
      model,
      saveModels,
      showConfigFullScreen,
      showFileOptions,
      showRoleOptions,
      showSelectTemplateFields,
      showVendorTemplateOptions,
      toggleEditorDarkMode,
      toggleEditorLineNumbers,
      toggleEditorMinimap,
      vendorTemplateOptionSelected,
      vendorTemplateOptions
    };
  }
};
</script>
