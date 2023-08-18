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
          style="margin-left: 0.5rem"
        />

        <label class="pf-c-check__label" style="cursor: default; color: #6a6e73"
          ><small>Dark Mode</small></label
        >
      </div>
      <div class="pf-c-check" style="align-content: center">
        <input
          class="pf-c-check__input pf-u-hidden pf-u-display-inline-block-on-md"
          type="checkbox"
          id="lineNumbers"
          name="lineNumbers"
          @change="toggleEditorLineNumbers($event)"
          :checked="lineNumbers == 'on'"
          style="margin-left: 0.5rem"
        />

        <label
          class="pf-c-check__label pf-u-hidden pf-u-display-inline-block-on-md"
          style="cursor: default; color: #6a6e73"
          ><small>Line Numbers</small></label
        >
      </div>
      <div class="pf-c-check" style="align-content: center">
        <input
          class="pf-c-check__input pf-u-hidden pf-u-display-inline-block-on-md"
          type="checkbox"
          id="lineNumbers"
          name="lineNumbers"
          @change="toggleEditorMinimap($event)"
          :checked="minimap.enabled == 'true'"
          style="margin-left: 0.5rem"
        />
        <label
          class="pf-c-check__label pf-u-hidden pf-u-display-inline-block-on-md"
          style="cursor: default; color: #6a6e73"
          ><small>Minimap</small></label
        >
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
              @click="copy('Config')"
              style="padding-left: 6px; padding-right: 6px"
            >
              <span class="pf-c-button__icon pf-m-start">
                <i class="fas fa-copy" aria-hidden="true"></i>
              </span>
              Config
            </button>
            <button
              class="pf-c-button pf-m-small pf-m-control pf-u-hidden pf-u-display-inline-block-on-md"
              type="button"
              label="Copy to clipboard"
              title="Copy to clipboard"
              @click="copyPath(configModel.config_location)"
              style="padding-left: 6px; padding-right: 6px"
            >
              <span class="pf-c-button__icon pf-m-start">
                <i class="fas fa-copy" aria-hidden="true"></i>
              </span>
              Path
            </button>
            <button
              class="pf-c-button pf-m-small pf-m-control pf-u-hidden pf-u-display-inline-block-on-md"
              type="button"
              label="Download config file"
              title="Download config file"
              @click="download(configModel.config_filename)"
              style="padding-left: 6px; padding-right: 6px"
            >
              <span class="pf-c-button__icon pf-m-start">
                <i class="fa fa-download" aria-hidden="true"></i>
              </span>
              Download
            </button>
            <button
              class="pf-c-button pf-m-small pf-m-control pf-u-hidden pf-u-display-inline-block-on-md"
              type="button"
              label="Search the config file"
              title="Search the config file"
              @click="search"
              style="padding-left: 6px; padding-right: 6px"
            >
              <span class="pf-c-button__icon pf-m-start">
                <i class="fa fa-search" aria-hidden="true"></i>
              </span>
              Search
            </button>
            <button
              class="pf-c-button pf-m-control pf-u-hidden pf-u-display-inline-block-on-md"
              type="button"
              title="full screen"
              alt="full screen"
              @click="showConfigFullScreen"
            >
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
        <div class="pf-c-code-editor__main" id="pf-c-code-editor__main">
          <code class="pf-c-code-editor__code">
            <div
              class="pf-c-code-editor__code-pre"
              id="code-editor"
              style="height: 80vh"
            ></div>
          </code>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
// import loader from '@monaco-editor/loader';
// import useCodeEditor from '../composables/codeEditorFunctions';
import * as monaco from "monaco-editor";
import useClipboard from "vue-clipboard3";
import { inject, onMounted, ref, reactive } from "vue";
import { saveAs } from "file-saver";
import { useRouter } from "vue-router";

export default {
  props: {
    config_id: {
      type: [Number, String],
      required: true,
    },
    viewstate: {
      type: Object,
      required: true,
    },
    configModel: {
      type: Object,
      required: true,
    },
  },

  components: {},

  emits: ["showConfigFullScreen"],
  setup(props, { emit }) {
    // const meditorValue = ref(['function x() {', '\tconsole.log("If you see this, something went wrong!");', '}'].join('\n'));
    const code = ref("");
    const copied = ref(false);
    const createNotification = inject("create-notification");
    const language = ref("rconfig");
    const meditorValue = ref(["Loading file..."].join("\n"));
    const router = useRouter();
    const { toClipboard } = useClipboard();
    let meditor = null;

    onMounted(() => {
      checkDarkModeIsSet();
      checkLineNumbersIsSet();
      checkMiniMapIsSet();

      getConfigFile();
      // meditor = initEditor('code-editor', 'rconfig');
      const codeEditorDiv = document.getElementById("code-editor");
      // loader.init().then((monaco) => {
      //     meditor = monaco.editor.create(codeEditorDiv, {
      //         value: meditorValue.value,
      //         language: 'javascript'
      //     });
      // });

      meditor = monaco.editor.create(codeEditorDiv, {
        value: meditorValue.value,
        language: language.value || "javascript",
        lineNumbers: lineNumbers.value,
        roundedSelection: false,
        readOnly: false,
        theme: darkmode.value,
        scrollBeyondLastLine: true,
        wordWrap: "on",
        wrappingStrategy: "advanced",
        automaticLayout: true,
        minimap: {
          enabled: false,
        },
      });
    });

    function getConfigFile() {
      // props.viewstate.isLoading = true;
      axios
        .get("/api/configs/view-config/" + props.config_id)
        .then((response) => {
          // console.log(response.data.data);
          code.value = response.data.data;
          meditor.getModel().setValue(response.data.data);
          // props.viewstate.isLoading = false;
        })
        .catch((error) => {
          meditor.updateOptions({
            value:
              "Something went wrong - could not retrieve the configuration from the file system!",
          });
          createNotification({
            type: "danger",
            title: "Error",
            message: error.response,
          });
          // props.viewstate.isLoading = false;
        });
    }

    function showConfigFullScreen() {
      emit("showConfigFullScreen", {
        code: code.value,
        filename: props.configModel.config_filename,
      });
    }

    /** EDITOR DARKMODE */
    const darkmode = ref("vs");

    function checkDarkModeIsSet() {
      if (localStorage.getItem("rConfig.editordarkmode") === null) {
        darkmode.value = "vs";
        localStorage.setItem("rConfig.editordarkmode", darkmode.value);
      } else {
        darkmode.value = localStorage.getItem("rConfig.editordarkmode");
      }
    }

    function toggleEditorDarkMode(event) {
      if (event.target.checked) {
        darkmode.value = "vs-dark";
        localStorage.setItem("rConfig.editordarkmode", darkmode.value);
      } else {
        darkmode.value = "vs";
        localStorage.setItem("rConfig.editordarkmode", darkmode.value);
      }
      monaco.editor.setTheme(darkmode.value);
    }

    /** EDITOR LINNUMBERS */
    const lineNumbers = ref("on");

    function checkLineNumbersIsSet() {
      if (localStorage.getItem("rConfig.editorlineNumbers") === null) {
        lineNumbers.value = "on";
        localStorage.setItem("rConfig.editorlineNumbers", lineNumbers.value);
      } else {
        lineNumbers.value = localStorage.getItem("rConfig.editorlineNumbers");
      }
    }

    function toggleEditorLineNumbers(event) {
      if (event.target.checked) {
        lineNumbers.value = "on";
        localStorage.setItem("rConfig.editorlineNumbers", lineNumbers.value);
      } else {
        lineNumbers.value = "off";
        localStorage.setItem("rConfig.editorlineNumbers", lineNumbers.value);
      }
      meditor.updateOptions({
        lineNumbers: lineNumbers.value,
      });
    }

    /* MINIMAP */
    const minimap = reactive({
      enabled: true,
    });

    function checkMiniMapIsSet() {
      if (localStorage.getItem("rConfig.editorMinimap") === null) {
        minimap.enabled = false;
        localStorage.setItem("rConfig.editorMinimap", minimap.enabled);
      } else {
        minimap.enabled = localStorage.getItem("rConfig.editorMinimap");
      }
    }

    function toggleEditorMinimap(event) {
      if (event.target.checked) {
        minimap.enabled = true;
        localStorage.setItem("rConfig.editorMinimap", minimap.enabled);
      } else {
        minimap.enabled = false;
        localStorage.setItem("rConfig.editorMinimap", minimap.enabled);
      }
      meditor.updateOptions({
        minimap: {
          enabled: minimap.enabled,
        },
      });
    }

    const copy = async (type, value) => {
      try {
        var newValue = typeof value === "string" ? value : meditor.getValue(); // path is a string, else an object is passed by detault
        await toClipboard(newValue);
        copied.value = true;
        setTimeout(() => {
          copied.value = false;
        }, 3000);
        createNotification({
          type: "success",
          title: "Copy Success",
          message: type + " copied to clipboard",
        });
      } catch (e) {
        createNotification({
          type: "danger",
          title: "Error",
          message: e,
        });
      }
    };

    const copyPath = async (path) => {
      copy("Path", path);
    };

    function download(filename = null) {
      const blob = new Blob([meditor.getValue()], {
        type: "text/plain;charset=utf-8",
      });
      saveAs(blob, filename != null ? filename : "template.yml");
    }

    function search() {
      //https://stackoverflow.com/questions/45629937/monaco-editor-pre-populate-find-control-with-text
      // const model = meditor.getModel();
      // // console.log(model.findMatches('console', false, true, false));
      // const range = model.findMatches('st')[0].range;
      // meditor.setSelection(range);
      meditor.focus();
      meditor.getAction("actions.find").run();
    }

    return {
      checkDarkModeIsSet,
      checkLineNumbersIsSet,
      checkMiniMapIsSet,
      copy,
      copyPath,
      darkmode,
      download,
      lineNumbers,
      minimap,
      search,
      showConfigFullScreen,
      toggleEditorDarkMode,
      toggleEditorLineNumbers,
      toggleEditorMinimap,
    };
  },
};
</script>

<style scoped>
/*add pf-m-link for buttons with tertiary and warning color*/
.pf-c-button {
  --pf-c-button--m-link--m-warning--BackgroundColor: transparent;
  --pf-c-button--m-link--m-warning--Color: var(--pf-global--warning-color--100);
  --pf-c-button--m-link--m-warning--hover--BackgroundColor: transparent;
  --pf-c-button--m-link--m-warning--hover--Color: var(
    --pf-global--warning-color--200
  );
  --pf-c-button--m-link--m-warning--focus--BackgroundColor: transparent;
  --pf-c-button--m-link--m-warning--focus--Color: var(
    --pf-global--warning-color--200
  );
  --pf-c-button--m-link--m-warning--active--BackgroundColor: transparent;
  --pf-c-button--m-link--m-warning--active--Color: var(
    --pf-global--warning-color--200
  );
}

.pf-c-button.pf-m-warning.pf-m-link {
  --pf-c-button--m-warning--Color: var(--pf-c-button--m-link--m-warning--Color);
  --pf-c-button--m-warning--BackgroundColor: var(
    --pf-c-button--m-link--m-warning--BackgroundColor
  );
}
.pf-c-button.pf-m-warning.pf-m-link:hover {
  --pf-c-button--m-link--m-warning--Color: var(
    --pf-c-button--m-link--m-warning--hover--Color
  );
  --pf-c-button--m-link--m-warning--BackgroundColor: var(
    --pf-c-button--m-link--m-warning--hover--BackgroundColor
  );
}
.pf-c-button.pf-m-warning.pf-m-link:focus {
  --pf-c-button--m-link--m-warning--Color: var(
    --pf-c-button--m-link--m-warning--focus--Color
  );
  --pf-c-button--m-link--m-warning--BackgroundColor: var(
    --pf-c-button--m-link--m-warning--focus--BackgroundColor
  );
}
.pf-c-button.pf-m-warning.pf-m-link:active,
.pf-c-button.pf-m-warning.pf-m-link.pf-m-active {
  --pf-c-button--m-link--m-warning--Color: var(
    --pf-c-button--m-link--m-warning--active--Color
  );
  --pf-c-button--m-link--m-warning--BackgroundColor: var(
    --pf-c-button--m-link--m-warning--active--BackgroundColor
  );
}
</style>
