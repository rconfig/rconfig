import axios from 'axios';
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { useDialogStore } from '@/stores/dialogActions';
import { useToaster } from '@/composables/useToaster'; // Import the composable

export function useTemplates(emit) {
  const currentPage = ref(1);
  const perPage = ref(parseInt(localStorage.getItem('perPage') || '10'));
  const sortParam = ref('-id');
  const searchTerm = ref('');
  const filters = ref({});
  const dialogStore = useDialogStore();
  const editId = ref(0);
  const isLoading = ref(false);
  const lastPage = ref(1);
  const newTemplateModalKey = ref(1);
  const templates = ref([]);
  const { openDialog } = dialogStore;
  const { toastSuccess, toastError } = useToaster(); // Using toaster for notifications

  // Fetch useTemplates
  async function fetchTemplates(params = {}) {
    isLoading.value = true;
    try {
      const response = await axios.get('/api/templates', {
        params: {
          page: currentPage.value,
          perPage: perPage.value,
          sort: sortParam.value,
          ...filters.value
        }
      });
      templates.value = response.data;
      lastPage.value = response.data.last_page;
    } catch (error) {
      console.error('Error fetching templates:', error);
      toastError('Error', 'Failed to fetch templates.');
    } finally {
      isLoading.value = false;
    }
  }

  // Create useTemplate
  function createTemplate(editId) {
    emit('createTemplate', { id: editId, type: 'template' });
  }

  function updateTemplate(id) {
    editId.value = id;
    newTemplateModalKey.value = Math.random(); // Force re-render of the dialog component
    openDialog('DialogNewTemplate');
  }

  // Delete Template
  const deleteTemplate = async id => {
    try {
      await axios.delete(`/api/templates/${id}`);
      fetchTemplates(); // Refresh templates list after deletion
      toastSuccess('Template Deleted', 'The template has been deleted successfully.');
    } catch (error) {
      console.error('Error deleting template:', error);
      toastError('Error', 'Failed to delete template.');
    }
  };

  const viewEditDialog = id => {
    editId.value = id;
    newTemplateModalKey.value = Math.random(); // Force re-render of the dialog component
    openDialog('DialogNewTemplate');
  };

  // Re-render Dialog
  const handleSave = () => {
    fetchTemplates(); // Fetch the updated templates after saving
    newTemplateModalKey.value = Math.random(); // Force re-render of the dialog component
  };

  function handleKeyDown(event) {
    if (event.altKey && event.key === 'n') {
      event.preventDefault(); // Prevent default behavior (e.g., opening a new window in some browsers)
      openDialog('DialogNewTemplate');
    }
  }

  const debouncedFilter = useDebounceFn(() => {
    filters.value[`filter[templateName]`] = searchTerm.value;
    currentPage.value = 1;
    fetchTemplates();
  }, 500);

  // Watchers for state changes
  watch([currentPage, perPage], () => {
    fetchTemplates();
  });

  watch(searchTerm, () => {
    debouncedFilter();
  });

  watch(perPage, newVal => {
    localStorage.setItem('perPage', newVal.toString());
  });

  function toggleSort(field) {
    if (sortParam.value === field) {
      sortParam.value = `-${field}`;
    } else {
      sortParam.value = field;
    }
    fetchTemplates();
  }

  return {
    templates,
    isLoading,
    currentPage,
    perPage,
    lastPage,
    editId,
    newTemplateModalKey,
    searchTerm,
    openDialog,
    fetchTemplates,
    createTemplate,
    updateTemplate,
    deleteTemplate,
    handleSave,
    handleKeyDown,
    viewEditDialog,
    toggleSort,
    sortParam
  };
}
