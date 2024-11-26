import axios from 'axios';
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { useDialogStore } from '@/stores/dialogActions';
import { useToaster } from '@/composables/useToaster'; // Import the composable
import { eventBus } from '@/composables/eventBus';

export function useTags() {
  const currentPage = ref(1);
  const perPage = ref(parseInt(localStorage.getItem('perPage') || '10'));
  const sortParam = ref('-id');
  const searchTerm = ref('');
  const filters = ref({});
  const dialogStore = useDialogStore();
  const editId = ref(0);
  const isLoading = ref(false);
  const lastPage = ref(1);
  const newTagModalKey = ref(1);
  const tags = ref([]);
  const showConfirmDelete = ref(false);
  const { openDialog } = dialogStore;
  const { toastSuccess, toastError } = useToaster(); // Using toaster for notifications

  // Fetch Tags
  async function fetchTags(params = {}) {
    isLoading.value = true;
    try {
      const response = await axios.get('/api/tags', {
        params: {
          page: currentPage.value,
          perPage: perPage.value,
          sort: sortParam.value,
          ...filters.value
        }
      });
      tags.value = response.data;
      lastPage.value = response.data.last_page;
    } catch (error) {
      console.error('Error fetching tags:', error);
      toastError('Error', 'Failed to fetch tags.');
    } finally {
      isLoading.value = false;
    }
  }

  // Create Tag
  const createTag = async => {
    editId.value = 0;
    newTagModalKey.value = Math.random(); // Force re-render of the dialog component
    openDialog('DialogNewTag');
  };

  function updateTag(id) {
    editId.value = id;
    newTagModalKey.value = Math.random(); // Force re-render of the dialog component
    openDialog('DialogNewTag');
  }

  // Delete Tag
  const deleteTag = async id => {
    try {
      await axios.delete(`/api/tags/${id}`);
      fetchTags(); // Refresh tags list after deletion
      toastSuccess('Tag Deleted', 'The tag has been deleted successfully.');
      showConfirmDelete.value = false;
    } catch (error) {
      if (error.status === 422) {
        toastError('Error', error.response.data.message);
      } else {
        console.error('Error deleting Tag:', error);
        toastError('Error', 'Failed to delete Tag.');
      }
    }
  };

  // Delete Many Tags
  const deleteManyTags = async ids => {
    try {
      await axios.post('/api/tags/delete-many', { ids });
      fetchTags(); // Refresh tags list after deletion
      toastSuccess('Tags Deleted', 'The tags have been deleted successfully.');
      showConfirmDelete.value = false;
      eventBus.emit('deleteManyTagsSuccess');
    } catch (error) {
      if (error.status === 422) {
        toastError('Error', error.response.data.message);
      } else {
        console.error('Error deleting Tags:', error);
        toastError('Error', 'Failed to delete Tags.');
      }
      showConfirmDelete.value = false;
    }
  };

  const viewEditDialog = id => {
    editId.value = id;
    newTagModalKey.value = Math.random(); // Force re-render of the dialog component
    openDialog('DialogNewTag');
  };

  // Re-render Dialog
  const handleSave = () => {
    fetchTags(); // Fetch the updated tags after saving
    newTagModalKey.value = Math.random(); // Force re-render of the dialog component
  };

  function handleKeyDown(event) {
    if (event.altKey && event.key === 'n') {
      event.preventDefault(); // Prevent default behavior (e.g., opening a new window in some browsers)
      openDialog('DialogNewTag');
    }
  }

  const debouncedFilter = useDebounceFn(() => {
    filters.value[`filter[tagname]`] = searchTerm.value;
    currentPage.value = 1;
    fetchTags();
  }, 500);

  // Watchers for state changes
  watch([currentPage, perPage], () => {
    fetchTags();
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
    fetchTags();
  }

  return {
    tags,
    isLoading,
    currentPage,
    perPage,
    lastPage,
    editId,
    newTagModalKey,
    searchTerm,
    openDialog,
    fetchTags,
    createTag,
    updateTag,
    deleteTag,
    deleteManyTags,
    handleSave,
    handleKeyDown,
    viewEditDialog,
    toggleSort,
    sortParam,
    showConfirmDelete
  };
}
