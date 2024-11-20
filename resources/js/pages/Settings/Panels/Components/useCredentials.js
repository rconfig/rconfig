import axios from 'axios';
import { eventBus } from '@/composables/eventBus';
import { ref, onMounted, watch, inject } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { useDialogStore } from '@/stores/dialogActions';
import { useToaster } from '@/composables/useToaster'; // Import the composable

export function useCredentials() {
  const { toastSuccess, toastError } = useToaster(); // Using toaster for notifications
  const dialogStore = useDialogStore();
  const { openDialog } = dialogStore;

  const editId = ref(0);
  const newCredModalKey = ref(1);
  const currentPage = ref(1);
  const filters = ref({});
  const isLoading = ref(false);
  const lastPage = ref(1);
  const creds = ref({});
  const perPage = ref(parseInt(localStorage.getItem('perPage') || '10'));
  const searchTerm = ref('');
  const showConfirmDelete = ref(false);
  const sortParam = ref('-id');
  const formatters = inject('formatters');

  onMounted(() => {
    fetchCreds();
  });

  async function fetchCreds(params = {}) {
    isLoading.value = true;
    try {
      const response = await axios.get('/api/device-credentials', {
        params: {
          page: currentPage.value,
          perPage: perPage.value,
          sort: sortParam.value,
          ...filters.value
        }
      });
      creds.value = response.data;
      lastPage.value = response.data.last_page;
    } catch (error) {
      console.error('Error fetching creds:', error);
      toastError('Error', 'Failed to fetch creds.');
    } finally {
      isLoading.value = false;
    }
  }

  // Create Cred
  const createCred = async => {
    editId.value = 0;
    newCredModalKey.value = Math.random(); // Force re-render of the dialog component
    openDialog('DialogNewCred');
  };

  // Edit Cred
  const editCred = id => {
    editId.value = id;
    newCredModalKey.value = Math.random(); // Force re-render of the dialog component
    openDialog('DialogNewCred');
  };

  const handleSave = () => {
    fetchCreds(); // Fetch the updated tags after saving
    newCredModalKey.value = Math.random(); // Force re-render of the dialog component
  };

  // Delete Credential
  const deleteCredential = async id => {
    try {
      await axios.delete(`/api/device-credentials/${id}`);
      fetchCreds(); // Refresh creds list after deletion
      toastSuccess('Credential Deleted', 'The credential has been deleted successfully.');
      showConfirmDelete.value = false;
    } catch (error) {
      console.error('Error deleting credential:', error);
      toastError('Error', 'Failed to delete credential.');
      showConfirmDelete.value = false;
    }
  };

  // Delete Many Creds
  const deleteManyCredentials = async ids => {
    try {
      await axios.post('/api/device-credentials/delete-many', { ids });
      fetchCreds(); // Refresh creds list after deletion
      toastSuccess('Credentials Deleted', 'The creds have been deleted successfully.');
      showConfirmDelete.value = false;
      eventBus.emit('deleteManyCredentialsSuccess');
    } catch (error) {
      console.error('Error deleting creds:', error);
      toastError('Error', 'Failed to delete creds.');
      showConfirmDelete.value = false;
    }
  };

  const debouncedFilter = useDebounceFn(() => {
    filters.value[`filter[cred_name]`] = searchTerm.value;
    currentPage.value = 1;
    fetchCreds();
  }, 500);

  // Watchers for state changes
  watch([currentPage, perPage], () => {
    fetchCreds();
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
    fetchCreds();
  }

  return {
    createCred,
    editCred,
    creds,
    currentPage,
    deleteCredential,
    deleteManyCredentials,
    editId,
    formatters,
    handleSave,
    isLoading,
    lastPage,
    newCredModalKey,
    perPage,
    searchTerm,
    showConfirmDelete,
    sortParam,
    toggleSort
  };
}
