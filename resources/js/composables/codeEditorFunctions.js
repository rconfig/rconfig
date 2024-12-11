import { useClipboard } from '@vueuse/core';
import useMonacoDarkmodeTheme from '@/composables/MonacoDarkmodeTheme.js';
import userConfigPolicyLanguage from '@/composables/rConfigPolicyLanguage.js';
import userConfigPolicyLanguageAutoComplete from '@/composables/rConfigPolicyLanguageAutoComplete.js';
import { ref, reactive, inject, onUnmounted } from 'vue';
import { saveAs } from 'file-saver';

export default function useCodeEditor(monaco) {
  // const createNotification = inject('create-notification');
  const meditorValue = ref(['Loading file...'].join('\n'));
  const { text, copy, copied, isSupported } = useClipboard();
  let meditor = null;
  let completionItemProvider;

  /* Init the Editor */
  function initEditor(divName, language) {
    if (language === 'policy') {
      monaco.languages.register({ id: 'rConfigPolicyLanguage' });
      monaco.languages.setMonarchTokensProvider('rConfigPolicyLanguage', userConfigPolicyLanguage());

      completionItemProvider = monaco.languages.registerCompletionItemProvider('rConfigPolicyLanguage', {
        triggerCharacters: ['#'],
        provideCompletionItems: function (model, position) {
          return { suggestions: userConfigPolicyLanguageAutoComplete(monaco) };
        }
      });

      language = 'rConfigPolicyLanguage';
    }

    const codeEditorDiv = document.getElementById(divName);
    meditor = monaco.editor.create(codeEditorDiv, {
      stickyScroll: {
        enabled: localStorage.getItem('rConfig.editorStickyScroll') === 'true' ? true : false
      },
      value: meditorValue.value,
      language: language || 'javascript',
      lineNumbers: lineNumbers.value,
      roundedSelection: false,
      scrollBeyondLastLine: true,
      readOnly: false,
      theme: darkmode.value,
      scrollBeyondLastLine: false,
      automaticLayout: true,
      wordWrap: 'on',
      minimap: {
        enabled: localStorage.getItem('rConfig.editorMinimap') === 'true' ? true : false
      },
      automaticLayout: true
    });

    return meditor;
  }

  /* Init the Editor */
  function initDiffEditor(divName, language) {
    if (language === 'policy') {
      monaco.languages.register({ id: 'rConfigPolicyLanguage' });
      monaco.languages.setMonarchTokensProvider('rConfigPolicyLanguage', userConfigPolicyLanguage());

      completionItemProvider = monaco.languages.registerCompletionItemProvider('rConfigPolicyLanguage', {
        triggerCharacters: ['#'],
        provideCompletionItems: function (model, position) {
          return { suggestions: userConfigPolicyLanguageAutoComplete(monaco) };
        }
      });

      language = 'rConfigPolicyLanguage';
    }

    const codeEditorDiv = document.getElementById(divName);
    meditor = monaco.editor.createDiffEditor(codeEditorDiv, {
      stickyScroll: {
        enabled: localStorage.getItem('rConfig.editorStickyScroll') === 'true' ? true : false
      },
      enableSplitViewResizing: true,
      value: meditorValue.value,
      language: language || 'javascript',
      lineNumbers: lineNumbers.value,
      renderOverviewRuler: false, //https://github.com/microsoft/monaco-editor/issues/1689
      roundedSelection: false,
      scrollBeyondLastLine: true,
      readOnly: false,
      theme: darkmode.value,
      scrollBeyondLastLine: false,
      automaticLayout: true,
      wordWrap: 'on',
      minimap: {
        enabled: localStorage.getItem('rConfig.editorMinimap') === 'true' ? true : false
      },
      automaticLayout: true
    });

    return meditor;
  }

  /** EDITOR DARKMODE */
  const darkmode = ref('vs');

  function checkDarkModeIsSet() {
    if (localStorage.getItem('rConfig.editordarkmode') === null) {
      darkmode.value = 'vs';
      localStorage.setItem('rConfig.editordarkmode', darkmode.value);
    } else {
      darkmode.value = localStorage.getItem('rConfig.editordarkmode');
      useMonacoDarkmodeTheme();
    }
    return darkmode.value == 'vs-dark' ? true : false;
  }

  function toggleEditorDarkMode() {
    if (localStorage.getItem('rConfig.editordarkmode') === null || localStorage.getItem('rConfig.editordarkmode') === 'vs') {
      darkmode.value = 'vs-dark';
      useMonacoDarkmodeTheme();
      localStorage.setItem('rConfig.editordarkmode', darkmode.value);
    } else {
      darkmode.value = 'vs';
      localStorage.setItem('rConfig.editordarkmode', darkmode.value);
    }
    monaco.editor.setTheme(darkmode.value);
  }

  /** EDITOR LINNUMBERS */
  const lineNumbers = ref('on');

  function checkLineNumbersIsSet() {
    if (localStorage.getItem('rConfig.editorlineNumbers') === null) {
      lineNumbers.value = 'on';
      localStorage.setItem('rConfig.editorlineNumbers', lineNumbers.value);
    } else {
      lineNumbers.value = localStorage.getItem('rConfig.editorlineNumbers');
    }
    return lineNumbers.value == 'on' ? true : false;
  }

  function toggleEditorLineNumbers() {
    if (localStorage.getItem('rConfig.editorlineNumbers') === null || localStorage.getItem('rConfig.editorlineNumbers') === 'off') {
      lineNumbers.value = 'on';
      localStorage.setItem('rConfig.editorlineNumbers', lineNumbers.value);
    } else {
      lineNumbers.value = 'off';
      localStorage.setItem('rConfig.editorlineNumbers', lineNumbers.value);
    }
    meditor.updateOptions({
      lineNumbers: lineNumbers.value
    });
  }

  /* MINIMAP */
  const minimap = reactive({
    enabled: false
  });

  function checkMiniMapIsSet() {
    if (localStorage.getItem('rConfig.editorMinimap') === null) {
      minimap.enabled = false;
      localStorage.setItem('rConfig.editorMinimap', minimap.enabled);
    } else {
      minimap.enabled = localStorage.getItem('rConfig.editorMinimap');
    }

    return minimap.enabled == 'true' ? true : false;
  }

  function toggleEditorMinimap() {
    if (localStorage.getItem('rConfig.editorMinimap') === null || localStorage.getItem('rConfig.editorMinimap') == 'false') {
      minimap.enabled = true;
      localStorage.setItem('rConfig.editorMinimap', minimap.enabled);
    } else {
      minimap.enabled = false;
      localStorage.setItem('rConfig.editorMinimap', minimap.enabled);
    }

    meditor.updateOptions({
      minimap: {
        enabled: minimap.enabled
      }
    });
  }

  /* STICKY SCROLL */
  const stickyScroll = reactive({
    enabled: false
  });

  function checkStickyScrollIsSet() {
    if (localStorage.getItem('rConfig.editorStickyScroll') === null) {
      stickyScroll.enabled = false;
      localStorage.setItem('rConfig.editorStickyScroll', stickyScroll.enabled);
    } else {
      stickyScroll.enabled = localStorage.getItem('rConfig.editorStickyScroll');
    }

    return stickyScroll.enabled == 'true' ? true : false;
  }

  function toggleStickyScroll() {
    if (localStorage.getItem('rConfig.editorStickyScroll') === null || localStorage.getItem('rConfig.editorStickyScroll') == 'false') {
      stickyScroll.enabled = true;
      localStorage.setItem('rConfig.editorStickyScroll', stickyScroll.enabled);
    } else {
      stickyScroll.enabled = false;
      localStorage.setItem('rConfig.editorStickyScroll', stickyScroll.enabled);
    }

    meditor.updateOptions({
      stickyScroll: {
        enabled: stickyScroll.enabled
      }
    });
  }

  const copyItem = async value => {
    try {
      var newValue = typeof value === 'string' ? value : meditor.getValue(); // path is a string, else an object is passed by detault
      await copy(newValue);
      copied.value = true;
      setTimeout(() => {
        copied.value = false;
      }, 3000);
    } catch (e) {
      console.error('Failed to copy:', e);
    }
  };

  const copyPath = async path => {
    copyItem(path);
  };

  function download(filename = null) {
    const blob = new Blob([meditor.getValue()], { type: 'text/plain;charset=utf-8' });
    saveAs(blob, filename != null ? filename : 'template.yml');
  }

  function search() {
    //https://stackoverflow.com/questions/45629937/monaco-editor-pre-populate-find-control-with-text
    // const model = meditor.getModel();
    // // console.log(model.findMatches('console', false, true, false));
    // const range = model.findMatches('st')[0].range;
    // meditor.setSelection(range);
    meditor.focus();
    meditor.getAction('actions.find').run();
  }

  onUnmounted(() => {
    if (meditor.value) {
      meditor.value.dispose();
    }
    if (completionItemProvider) {
      completionItemProvider.dispose();
    }
  });

  return {
    checkDarkModeIsSet,
    checkLineNumbersIsSet,
    checkMiniMapIsSet,
    checkStickyScrollIsSet,
    copied,
    copyItem,
    copyPath,
    darkmode,
    download,
    initEditor,
    initDiffEditor,
    lineNumbers,
    meditorValue,
    minimap,
    search,
    toggleEditorDarkMode,
    toggleEditorLineNumbers,
    toggleEditorMinimap,
    toggleStickyScroll
  };
}
