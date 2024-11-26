import axios from 'axios';
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { useDialogStore } from '@/stores/dialogActions';
import { useToaster } from '@/composables/useToaster'; // Import the composable
import { eventBus } from '@/composables/eventBus';

export function useCommands() {
  const currentPage = ref(1);
  const perPage = ref(parseInt(localStorage.getItem('perPage') || '10'));
  const sortParam = ref('-id');
  const searchTerm = ref('');
  const filters = ref({});
  const dialogStore = useDialogStore();
  const editId = ref(0);
  const isLoading = ref(false);
  const lastPage = ref(1);
  const newCommandModalKey = ref(1);
  const commands = ref([]);
  const showConfirmDelete = ref(false);
  const { openDialog } = dialogStore;
  const { toastSuccess, toastError } = useToaster(); // Using toaster for notifications

  // Fetch Commands
  async function fetchCommands(params = {}) {
    isLoading.value = true;
    try {
      const response = await axios.get('/api/commands', {
        params: {
          page: currentPage.value,
          perPage: perPage.value,
          sort: sortParam.value,
          ...filters.value
        }
      });
      commands.value = response.data;
      lastPage.value = response.data.last_page;
    } catch (error) {
      console.error('Error fetching commands:', error);
      toastError('Error', 'Failed to fetch commands.');
    } finally {
      isLoading.value = false;
    }
  }

  // Create Command
  const createCommand = async => {
    editId.value = 0;
    newCommandModalKey.value = Math.random(); // Force re-render of the dialog component
    openDialog('DialogNewCommand');
  };

  function updateCommand(id) {
    editId.value = id;
    newCommandModalKey.value = Math.random(); // Force re-render of the dialog component
    openDialog('DialogNewCommand');
  }

  // Delete Command
  const deleteCommand = async id => {
    try {
      await axios.delete(`/api/commands/${id}`);
      fetchCommands(); // Refresh commands list after deletion
      toastSuccess('Command Deleted', 'The command has been deleted successfully.');
      showConfirmDelete.value = false;
    } catch (error) {
      if (error.status === 422) {
        toastError('Error', error.response.data.message);
      } else {
        console.error('Error deleting Command Group:', error);
        toastError('Error', 'Failed to delete Command Group.');
      }
    }
  };

  // Delete Many Commands
  const deleteManyCommands = async ids => {
    try {
      await axios.post('/api/commands/delete-many', { ids });
      fetchCommands(); // Refresh commands list after deletion
      toastSuccess('Commands Deleted', 'The commands have been deleted successfully.');
      showConfirmDelete.value = false;
      eventBus.emit('deleteManyCommandsSuccess');
    } catch (error) {
      if (error.status === 422) {
        toastError('Error', error.response.data.message);
      } else {
        console.error('Error deleting Command Group:', error);
        toastError('Error', 'Failed to delete Command Group.');
      }
    }
  };

  const viewEditDialog = id => {
    editId.value = id;
    newCommandModalKey.value = Math.random(); // Force re-render of the dialog component
    openDialog('DialogNewCommand');
  };

  // Re-render Dialog
  const handleSave = () => {
    fetchCommands(); // Fetch the updated commands after saving
    newCommandModalKey.value = Math.random(); // Force re-render of the dialog component
  };

  function handleKeyDown(event) {
    if (event.altKey && event.key === 'n') {
      event.preventDefault(); // Prevent default behavior (e.g., opening a new window in some browsers)
      openDialog('DialogNewCommand');
    }
  }

  const debouncedFilter = useDebounceFn(() => {
    filters.value[`filter[command]`] = searchTerm.value;
    currentPage.value = 1;
    fetchCommands();
  }, 500);

  // Watchers for state changes
  watch([currentPage, perPage], () => {
    fetchCommands();
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
    fetchCommands();
  }

  return {
    commands,
    isLoading,
    currentPage,
    perPage,
    lastPage,
    editId,
    newCommandModalKey,
    searchTerm,
    openDialog,
    fetchCommands,
    createCommand,
    updateCommand,
    deleteCommand,
    deleteManyCommands,
    handleSave,
    handleKeyDown,
    viewEditDialog,
    toggleSort,
    sortParam,
    showConfirmDelete
  };
}
