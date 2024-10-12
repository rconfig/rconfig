import axios from 'axios';
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { useDialogStore } from '@/stores/dialogActions';
import { useToaster } from '@/composables/useToaster'; // Import the composable

export function useTemplatesGithub(emit) {
  const { toastSuccess, toastError } = useToaster(); // Using toaster for notifications

  const dialogStore = useDialogStore();
  const { openDialog } = dialogStore;
  const importingTemplates = ref(false);
  const hasVendorTemplateOptions = ref(false);
  const vendorTemplateOptions = ref([]);
  const vendorOptionSelected = ref({});
  const vendorTemplateOptionSelected = ref(null);
  const showFileOptions = ref(false);
  const listedFiles = ref([]);
  const hasReadmeFile = ref(false);
  const fileOptionSelected = ref(false);

  function importTemplates() {
    importingTemplates.value = true;
    axios
      .get('/api/import-github-templates', {
        fileName: this.fileName,
        code: this.code
      })
      .then(response => {
        toastSuccess('Templates imported from github successfully');
      })
      .catch(error => {
        toastError('Error importing templates from github');
      });
    setTimeout(() => {
      importingTemplates.value = false;
    }, 1000);
  }

  watch(vendorOptionSelected, () => {
    if (vendorOptionSelected.value !== null) {
      getTemplatesList(vendorOptionSelected.value);
    }
  });

  watch(fileOptionSelected, () => {
    if (fileOptionSelected.value !== null) {
      getTemplateFileContents(fileOptionSelected.value);
    }
  });

  function openImportDialog() {
    openDialog('DialogTemplateImport');
  }

  function getTemplateRepoFolders() {
    axios
      .get('/api/list-template-repo-folders')
      .then(response => {
        vendorTemplateOptions.value = response.data.data;
        hasVendorTemplateOptions.value = true;
      })
      .catch(error => {
        console.log(error);
        if (error.response.data.message.msg === 'rConfig-templates is empty, or does not exist. Clone from "https://github.com/rconfig/rconfig-templates" may have failed! Try importing the templates again.!') {
          hasVendorTemplateOptions.value = false;
        } else {
          toastError('Error getting vendor templates');
        }
      });
  }

  function getTemplatesList(vendorOptionPath) {
    console.log(vendorOptionPath);
    showFileOptions.value = false;
    // showVendorTemplateOptions.value = false;
    // vendorTemplateOptionSelected.value = vendorOptionPath.name;
    hasReadmeFile.value = false;
    axios
      .post('/api/list-repo-folders-contents', { directory: vendorOptionPath })
      .then(response => {
        // hasListedFiles.value = true;
        listedFiles.value = response.data.data;
        showFileOptions.value = true;
        if (typeof response.data.data.readme !== 'undefined') {
          hasReadmeFile.value = true;
        }
      })
      .catch(error => {
        console.log(error);
        toastError('Error getting file list');
      });
  }

  function getTemplateFileContents(fileOptionPath) {
    // showFileOptions.value = false;
    // showVendorTemplateOptions.value = false;
    // fileOptionSelected.value = fileOption.name;
    axios
      .post('/api/get-template-file-contents', { filepath: fileOptionPath })
      .then(response => {
        console.log('response', response);
        // meditor.getModel().setValue(response.data.data.data.code);
        // model.fileName = fileOptionPath.path.split('/').reverse()[0];
      })
      .catch(error => {
        toastError('Error getting file contents');
      });
  }

  return {
    importTemplates,
    importingTemplates,
    getTemplateRepoFolders,
    hasVendorTemplateOptions,
    vendorTemplateOptions,
    vendorOptionSelected,
    getTemplatesList,
    getTemplateFileContents,
    openImportDialog,
    showFileOptions,
    listedFiles,
    hasReadmeFile,
    fileOptionSelected
  };
}
