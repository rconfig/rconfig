import axios from 'axios';
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { useDialogStore } from '@/stores/dialogActions';
import { useToaster } from '@/composables/useToaster'; // Import the composable
import { eventBus } from '@/composables/eventBus';
import { useRoute, useRouter } from 'vue-router'; // Import the useRoute from Vue Router

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
  const showConfirmDelete = ref(false);
  const router = useRouter();
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

  function viewTemplateDetailsPane(editId) {
    router.push({ name: 'templatesview', params: { id: editId } });
  }

  // Delete Template
  const deleteTemplate = async id => {
    try {
      await axios.delete(`/api/templates/${id}`);
      fetchTemplates(); // Refresh templates list after deletion
      toastSuccess('Template Deleted', 'The template has been deleted successfully.');
    } catch (error) {
      if (error.status === 422) {
        toastError('Error', error.response.data.message);
      } else {
        console.error('Error deleting template:', error);
        toastError('Error', 'Failed to delete template.');
      }
    }
  };

  // Delete Many Templates
  const deleteManyTemplates = async ids => {
    try {
      await axios.post('/api/templates/delete-many', { ids });
      fetchTemplates(); // Refresh templates list after deletion
      toastSuccess('Templates Deleted', 'The templates have been deleted successfully.');
      showConfirmDelete.value = false;
      eventBus.emit('deleteManyTemplatesSuccess');
    } catch (error) {
      if (error.status === 422) {
        toastError('Error', error.response.data.message);
      } else {
        console.error('Error deleting Templates:', error);
        toastError('Error', 'Failed to delete Templates.');
      }
      showConfirmDelete.value = false;
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
    viewTemplateDetailsPane,
    deleteTemplate,
    deleteManyTemplates,
    handleSave,
    handleKeyDown,
    viewEditDialog,
    toggleSort,
    sortParam,
    showConfirmDelete
  };
}
