<template>
  <div class="pf-c-card">
    <div class="pf-c-card__header pf-l-flex">
      <h2 class="pf-c-title pf-m-xl pf-l-flex__item">Config Output</h2>
      <div class="pf-c-check pf-l-flex__item pf-m-align-right">
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
          style="cursor: default; color: #6a6e73">
          <small>Dark Mode</small>
        </label>
      </div>
      <div
        class="pf-c-check"
        style="align-content: center">
        <input
          class="pf-c-check__input pf-u-hidden pf-u-display-inline-block-on-md"
          type="checkbox"
          id="lineNumbers"
          name="lineNumbers"
          @change="toggleEditorLineNumbers($event)"
          :checked="lineNumbers == 'on'"
          style="margin-left: 0.5rem" />

        <label
          class="pf-c-check__label pf-u-hidden pf-u-display-inline-block-on-md"
          style="cursor: default; color: #6a6e73">
          <small>Line Numbers</small>
        </label>
      </div>
      <div
        class="pf-c-check"
        style="align-content: center">
        <input
          class="pf-c-check__input pf-u-hidden pf-u-display-inline-block-on-md"
          type="checkbox"
          id="lineNumbers"
          name="lineNumbers"
          @change="toggleEditorMinimap($event)"
          :checked="minimap.enabled == 'true'"
          style="margin-left: 0.5rem" />
        <label
          class="pf-c-check__label pf-u-hidden pf-u-display-inline-block-on-md"
          style="cursor: default; color: #6a6e73">
          <small>Minimap</small>
        </label>
      </div>
    </div>
    <div class="pf-c-card__body">
      <div class="pf-c-code-editor">
        <div class="pf-c-code-editor__header">
          <div class="pf-c-code-editor__controls">
            <br />
            <button
              class="pf-c-button pf-m-small pf-m-control"
              type="button"
              label="Copy to clipboard"
              title="Copy to clipboard"
              @click="copy"
              style="padding-left: 6px; padding-right: 6px">
              <span class="pf-c-button__icon pf-m-start">
                <i
                  class="fas fa-copy"
                  aria-hidden="true"></i>
              </span>
              Config
            </button>
            <button
              class="pf-c-button pf-m-small pf-m-control pf-u-hidden pf-u-display-inline-block-on-md"
              type="button"
              label="Copy to clipboard"
              title="Copy to clipboard"
              @click="copyPath(configModel.config_location)"
              style="padding-left: 6px; padding-right: 6px">
              <span class="pf-c-button__icon pf-m-start">
                <i
                  class="fas fa-copy"
                  aria-hidden="true"></i>
              </span>
              Path
            </button>
            <button
              class="pf-c-button pf-m-small pf-m-control pf-u-hidden pf-u-display-inline-block-on-md"
              type="button"
              label="Download config file"
              title="Download config file"
              @click="download(configModel.config_filename)"
              style="padding-left: 6px; padding-right: 6px">
              <span class="pf-c-button__icon pf-m-start">
                <i
                  class="fa fa-download"
                  aria-hidden="true"></i>
              </span>
              Download
            </button>
            <button
              class="pf-c-button pf-m-small pf-m-control pf-u-hidden pf-u-display-inline-block-on-md"
              type="button"
              label="Search the config file"
              title="Search the config file"
              @click="search"
              style="padding-left: 6px; padding-right: 6px">
              <span class="pf-c-button__icon pf-m-start">
                <i
                  class="fa fa-search"
                  aria-hidden="true"></i>
              </span>
              Search
            </button>
            <button
              class="pf-c-button pf-m-control pf-u-hidden pf-u-display-inline-block-on-md"
              type="button"
              title="full screen"
              alt="full screen"
              @click="showConfigFullScreen">
              <i class="fas fa-expand"></i>
            </button>
          </div>
          <div class="pf-c-code-editor__tab">
            <span class="pf-c-code-editor__tab-icon">
              <i class="fas fa-code"></i>
            </span>
            <span class="pf-c-code-editor__tab-text">Configuration</span>
          </div>
        </div>
        <div
          class="pf-c-code-editor__main"
          id="pf-c-code-editor__main">
          <code class="pf-c-code-editor__code">
            <div
              class="pf-c-code-editor__code-pre"
              id="pf-c-code-editor__code-pre"
              style="height: 80vh"></div>
          </code>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import * as monaco from 'monaco-editor';
import useCodeEditor from '../../../.archive/composables/codeEditorFunctions';
import { inject, onMounted, ref } from 'vue';

export default {
  props: {
    config_id: {
      type: [Number, String],
      required: true
    },
    viewstate: {
      type: Object,
      required: true
    },
    configModel: {
      type: Object,
      required: true
    }
  },

  emits: ['showConfigFullScreen'],
  setup(props, { emit }) {
    const code = ref('');
    const createNotification = inject('create-notification');
    let meditor = null;

    const { checkDarkModeIsSet, checkLineNumbersIsSet, checkMiniMapIsSet, copied, copy, copyPath, darkmode, download, initEditor, lineNumbers, minimap, search, toggleEditorDarkMode, toggleEditorLineNumbers, toggleEditorMinimap } = useCodeEditor(monaco);

    onMounted(() => {
      checkDarkModeIsSet();
      checkLineNumbersIsSet();
      checkMiniMapIsSet();

      getConfigFile();
      meditor = initEditor('pf-c-code-editor__code-pre', 'rconfig');
    });

    function getConfigFile() {
      // props.viewstate.isLoading = true;
      axios
        .get('/api/configs/view-config/' + props.config_id)
        .then(response => {
          // console.log(response.data.data);
          code.value = response.data.data;

          meditor.getModel().setValue(response.data.data);
          // props.viewstate.isLoading = false;
        })
        .catch(error => {
          meditor.updateOptions({
            value: 'Something went wrong - could not retrieve the configuration from the file system!'
          });
          createNotification({
            type: 'danger',
            title: 'Error',
            message: error.response
          });
          // props.viewstate.isLoading = false;
        });
    }

    function compare() {
      createNotification({
        type: 'danger',
        title: 'Error',
        message: 'Compare is not working and needs to be coded!'
      });
    }

    function restore() {
      createNotification({
        type: 'danger',
        title: 'Error',
        message: 'restore is not working and needs to be coded!'
      });
    }

    function deleteConfig() {
      createNotification({
        type: 'danger',
        title: 'Error',
        message: 'deleteConfig is not working and needs to be coded!'
      });
    }

    function showConfigFullScreen() {
      emit('showConfigFullScreen', { code: code.value, filename: props.configModel.config_filename });
    }

    return {
      checkDarkModeIsSet,
      checkLineNumbersIsSet,
      checkMiniMapIsSet,
      compare,
      copy,
      copyPath,
      darkmode,
      deleteConfig,
      download,
      lineNumbers,
      minimap,
      restore,
      search,
      showConfigFullScreen,
      toggleEditorDarkMode,
      toggleEditorLineNumbers,
      toggleEditorMinimap
    };
  }
};
</script>

<style scoped>
/*add pf-m-link for buttons with tertiary and warning color*/
.pf-c-button {
  --pf-c-button--m-link--m-warning--BackgroundColor: transparent;
  --pf-c-button--m-link--m-warning--Color: var(--pf-global--warning-color--100);
  --pf-c-button--m-link--m-warning--hover--BackgroundColor: transparent;
  --pf-c-button--m-link--m-warning--hover--Color: var(--pf-global--warning-color--200);
  --pf-c-button--m-link--m-warning--focus--BackgroundColor: transparent;
  --pf-c-button--m-link--m-warning--focus--Color: var(--pf-global--warning-color--200);
  --pf-c-button--m-link--m-warning--active--BackgroundColor: transparent;
  --pf-c-button--m-link--m-warning--active--Color: var(--pf-global--warning-color--200);
}

.pf-c-button.pf-m-warning.pf-m-link {
  --pf-c-button--m-warning--Color: var(--pf-c-button--m-link--m-warning--Color);
  --pf-c-button--m-warning--BackgroundColor: var(--pf-c-button--m-link--m-warning--BackgroundColor);
}
.pf-c-button.pf-m-warning.pf-m-link:hover {
  --pf-c-button--m-link--m-warning--Color: var(--pf-c-button--m-link--m-warning--hover--Color);
  --pf-c-button--m-link--m-warning--BackgroundColor: var(--pf-c-button--m-link--m-warning--hover--BackgroundColor);
}
.pf-c-button.pf-m-warning.pf-m-link:focus {
  --pf-c-button--m-link--m-warning--Color: var(--pf-c-button--m-link--m-warning--focus--Color);
  --pf-c-button--m-link--m-warning--BackgroundColor: var(--pf-c-button--m-link--m-warning--focus--BackgroundColor);
}
.pf-c-button.pf-m-warning.pf-m-link:active,
.pf-c-button.pf-m-warning.pf-m-link.pf-m-active {
  --pf-c-button--m-link--m-warning--Color: var(--pf-c-button--m-link--m-warning--active--Color);
  --pf-c-button--m-link--m-warning--BackgroundColor: var(--pf-c-button--m-link--m-warning--active--BackgroundColor);
}
</style>
