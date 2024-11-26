import axios from 'axios';
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { useDialogStore } from '@/stores/dialogActions';
import { useToaster } from '@/composables/useToaster'; // Import the composable
import { eventBus } from '@/composables/eventBus';

export function useCommandGroups() {
  const currentPage = ref(1);
  const perPage = ref(parseInt(localStorage.getItem('perPage') || '10'));
  const sortParam = ref('-id');
  const searchTerm = ref('');
  const filters = ref({});
  const dialogStore = useDialogStore();
  const editId = ref(0);
  const isLoading = ref(false);
  const lastPage = ref(1);
  const newCommandGroupsModalKey = ref(1);
  const categories = ref([]);
  const showConfirmDelete = ref(false);
  const { openDialog } = dialogStore;
  const { toastSuccess, toastError } = useToaster(); // Using toaster for notifications

  // Fetch CommandGroupss
  async function fetchCommandGroups(params = {}) {
    isLoading.value = true;
    try {
      const response = await axios.get('/api/categories', {
        params: {
          page: currentPage.value,
          perPage: perPage.value,
          sort: sortParam.value,
          ...filters.value
        }
      });
      categories.value = response.data;
      lastPage.value = response.data.last_page;
    } catch (error) {
      console.error('Error fetching categories:', error);
      toastError('Error', 'Failed to fetch categories.');
    } finally {
      isLoading.value = false;
    }
  }

  // Create CommandGroups
  const createCommandGroup = async => {
    editId.value = 0;
    newCommandGroupsModalKey.value = Math.random(); // Force re-render of the dialog component
    openDialog('DialogNewCommandGroups');
  };

  function updateCommandGroup(id) {
    editId.value = id;
    newCommandGroupsModalKey.value = Math.random(); // Force re-render of the dialog component
    openDialog('DialogNewCommandGroups');
  }

  // Delete CommandGroups
  const deleteCommandGroup = async id => {
    try {
      await axios.delete(`/api/categories/${id}`);
      fetchCommandGroups(); // Refresh categories list after deletion
      toastSuccess('CommandGroups Deleted', 'The Command Group has been deleted successfully.');
    } catch (error) {
      if (error.status === 422) {
        toastError('Error', error.response.data.message);
      } else {
        console.error('Error deleting Command:', error);
        toastError('Error', 'Failed to delete Command.');
      }
    }
  };

  // Delete Many CommandGroups
  const deleteManyCommandGroups = async ids => {
    try {
      await axios.post('/api/categories/delete-many', { ids });
      fetchCommandGroups(); // Refresh categories list after deletion
      toastSuccess('CommandGroups Deleted', 'The command groups have been deleted successfully.');
      showConfirmDelete.value = false;
      eventBus.emit('deleteManyCommandGroupsSuccess');
    } catch (error) {
      if (error.status === 422) {
        toastError('Error', error.response.data.message);
      } else {
        console.error('Error deleting Commands:', error);
        toastError('Error', 'Failed to delete Commands.');
      }
      showConfirmDelete.value = false;
    }
  };

  const viewEditDialog = id => {
    editId.value = id;
    newCommandGroupsModalKey.value = Math.random(); // Force re-render of the dialog component
    openDialog('DialogNewCommandGroups');
  };

  // Re-render Dialog
  const handleSave = () => {
    fetchCommandGroups(); // Fetch the updated categories after saving
    newCommandGroupsModalKey.value = Math.random(); // Force re-render of the dialog component
  };

  function handleKeyDown(event) {
    if (event.altKey && event.key === 'n') {
      event.preventDefault(); // Prevent default behavior (e.g., opening a new window in some browsers)
      openDialog('DialogNewCommandGroups');
    }
  }

  const debouncedFilter = useDebounceFn(() => {
    filters.value[`filter[categoryName]`] = searchTerm.value;
    currentPage.value = 1;
    fetchCommandGroups();
  }, 500);

  // Watchers for state changes
  watch([currentPage, perPage], () => {
    fetchCommandGroups();
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
    fetchCommandGroups();
  }

  return {
    categories,
    isLoading,
    currentPage,
    perPage,
    lastPage,
    editId,
    newCommandGroupsModalKey,
    searchTerm,
    openDialog,
    fetchCommandGroups,
    createCommandGroup,
    updateCommandGroup,
    deleteCommandGroup,
    deleteManyCommandGroups,
    handleSave,
    handleKeyDown,
    viewEditDialog,
    toggleSort,
    sortParam,
    showConfirmDelete
  };
}
