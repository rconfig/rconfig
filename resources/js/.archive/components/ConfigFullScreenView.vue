<template>
  <div class="pf-c-backdrop">
    <div class="pf-l-bullseye">
      <div
        class="pf-c-modal-box pf-m-sm pf-m-warning"
        role="dialog"
        ref="clickOutsidetarget"
        style="--pf-c-modal-box--Width: 100%; height: 100%">
        <button
          class="pf-c-button pf-m-plain"
          type="button"
          aria-label="Close dialog"
          @click="closeModal">
          <i
            class="fas fa-times"
            aria-hidden="true"></i>
        </button>
        <header class="pf-c-modal-box__header">
          <h1
            class="pf-c-modal-box__title"
            id="warning-alert-title">
            <span class="pf-c-modal-box__title-text">View code for {{ filename }}</span>
          </h1>
        </header>
        <div
          class="pf-c-modal-box__body"
          id="modal-description">
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
                  @click="download()">
                  <i class="fas fa-download"></i>
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
                <span class="pf-c-code-editor__tab-text">{{ language.toUpperCase() || 'YAML' }}</span>
              </div>
            </div>
            <div
              class="pf-c-code-editor__main"
              id="pf-c-code-editor__main"
              style="height: 100vh">
              <code class="pf-c-code-editor__code">
                <div
                  class="pf-c-code-editor__code-pre2"
                  id="pf-c-code-editor__code-pre2-fullscreen"
                  style="height: 100%"></div>
              </code>
            </div>
          </div>
        </div>
        <footer class="pf-c-modal-box__footer">
          <!-- <button class="pf-c-button pf-m-primary pf-m-small" type="button" @click="confirmUpdate()">Update</button> -->
          <button
            class="pf-c-button pf-m-link"
            type="button"
            @click="closeModal">
            Close
          </button>
        </footer>
      </div>
    </div>
  </div>
</template>

<script>
import * as monaco from 'monaco-editor';
import useCodeEditor from '../.archive/composables/codeEditorFunctions';
import { onClickOutside } from '@vueuse/core';
import { ref, onMounted } from 'vue';

export default {
  props: {
    editid: {
      type: Number,
      required: true
    },
    code: {
      type: String,
      required: true
    },
    filename: {
      type: String,
      required: true
    },
    language: {
      type: String,
      required: true
    }
  },
  emits: ['close', 'confirmdelete', 'closeModal'],

  setup(props, { emit }) {
    let meditor = null;
    const clickOutsidetarget = ref(null);

    onClickOutside(clickOutsidetarget, event => close());

    const { checkDarkModeIsSet, checkLineNumbersIsSet, checkMiniMapIsSet, copy, copied, darkmode, download, initEditor, lineNumbers, minimap, toggleEditorDarkMode, toggleEditorLineNumbers, toggleEditorMinimap } = useCodeEditor(monaco);

    onMounted(() => {
      checkDarkModeIsSet();
      checkLineNumbersIsSet();
      checkMiniMapIsSet();
      meditor = initEditor('pf-c-code-editor__code-pre2-fullscreen', props.language);
      meditor.getModel().setValue(props.code);
    });

    function closeModal() {
      emit('closeModal');
    }

    function confirmUpdate() {
      emit('confirmUpdate', props.editid);
    }

    return {
      clickOutsidetarget,
      closeModal,
      confirmUpdate,
      copied,
      copy,
      darkmode,
      download,
      lineNumbers,
      minimap,
      toggleEditorDarkMode,
      toggleEditorLineNumbers,
      toggleEditorMinimap
    };
  }
};
</script>
