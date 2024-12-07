import axios from 'axios';
import { ref, watch, inject } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { useDialogStore } from '@/stores/dialogActions';
import { useToaster } from '@/composables/useToaster'; // Import the composable
import { eventBus } from '@/composables/eventBus';
import { useRoute, useRouter } from 'vue-router'; // Import the useRoute from Vue Router

export function useConfigsTable(props) {
  const currentPage = ref(1);
  const perPage = ref(parseInt(localStorage.getItem('perPage') || '10'));
  const sortParam = ref('-id');
  const searchTerm = ref('');
  const filters = ref({});
  const dialogStore = useDialogStore();
  const editId = ref(0);
  const isLoading = ref(false);
  const lastPage = ref(1);
  const newConfigModalKey = ref(1);
  const configs = ref([]);
  const showConfirmDelete = ref(false);
  const filterStatus = ref([]);
  const filterCommand = ref([]);
  const router = useRouter();

  const { openDialog, isDialogOpen } = dialogStore;
  const { toastSuccess, toastError } = useToaster(); // Using toaster for notifications
  const formatters = inject('formatters');

  // Fetch Configs
  function getTabledata() {
    // check for filterstatus
    if (filterStatus.value.length > 0) {
      const ids = filterStatus.value.map(item => item.id);
      filters.value[`filter[download_status]`] = ids.join(',');
    }

    if (props.configsId === 0) {
      fetchAllConfigs();
    } else {
      getTabledataByDeviceId();
    }
  }

  async function fetchAllConfigs(params = {}) {
    isLoading.value = true;
    try {
      const response = await axios.get(`/api/configs/`, {
        params: {
          page: currentPage.value,
          perPage: perPage.value,
          sort: sortParam.value,
          ...filters.value
        }
      });
      configs.value = response.data;
      lastPage.value = response.data.last_page;
    } catch (error) {
      console.error('Error fetching configs:', error);
      toastError('Error', 'Failed to fetch configs.');
    } finally {
      isLoading.value = false;
    }
  }

  async function getTabledataByDeviceId(params = {}) {
    isLoading.value = true;
    filters.value[`filter[device_id]`] = props.configsId;
    try {
      const response = await axios.get(`/api/configs/all-by-deviceid/${props.configsId}/all`, {
        params: {
          page: currentPage.value,
          perPage: perPage.value,
          sort: sortParam.value,
          ...filters.value
        }
      });
      configs.value = response.data;
      lastPage.value = response.data.last_page;
    } catch (error) {
      console.error('Error fetching configs:', error);
      toastError('Error', 'Failed to fetch configs.');
    } finally {
      isLoading.value = false;
    }
  }

  // Create Config
  const createConfig = async => {
    editId.value = 0;
    newConfigModalKey.value = Math.random(); // Force re-render of the dialog component
    openDialog('DialogNewConfig');
  };

  function updateConfig(id) {
    editId.value = id;
    newConfigModalKey.value = Math.random(); // Force re-render of the dialog component
    openDialog('DialogNewConfig');
  }

  // Delete Config
  const deleteConfig = async id => {
    try {
      await axios.delete(`/api/configs/${id}`);
      getTabledata(); // Refresh configs list after deletion
      toastSuccess('Config Deleted', 'The config has been deleted successfully.');
      showConfirmDelete.value = false;
    } catch (error) {
      console.error('Error deleting config:', error);
      toastError('Error', 'Failed to delete config.');
      showConfirmDelete.value = false;
    }
  };

  // Delete Many Configs
  const deleteManyConfigs = async ids => {
    try {
      await axios.post('/api/configs/delete-many', { ids });
      getTabledata(); // Refresh configs list after deletion
      toastSuccess('Configs Deleted', 'The configs have been deleted successfully.');
      showConfirmDelete.value = false;
      eventBus.emit('deleteManyConfigsSuccess');
    } catch (error) {
      console.error('Error deleting configs:', error);
      toastError('Error', 'Failed to delete configs.');
      showConfirmDelete.value = false;
    }
  };

  const viewEditDialog = id => {
    editId.value = id;
    newConfigModalKey.value = Math.random(); // Force re-render of the dialog component
    openDialog('DialogNewConfig');
  };

  // Re-render Dialog
  const handleSave = () => {
    getTabledata(); // Fetch the updated configs after saving
    newConfigModalKey.value = Math.random(); // Force re-render of the dialog component
  };

  function handleKeyDown(event) {
    if (event.altKey && event.key === 'n') {
      event.preventDefault(); // Prevent default behavior (e.g., opening a new window in some browsers)
      openDialog('DialogNewConfig');
    }
  }

  const debouncedFilter = useDebounceFn(() => {
    filters.value[`filter[q]`] = searchTerm.value;
    currentPage.value = 1;
    getTabledata();
  }, 500);

  // Watchers for state changes
  watch([currentPage, perPage], () => {
    getTabledata();
  });

  watch(searchTerm, () => {
    debouncedFilter();
  });

  watch(perPage, newVal => {
    localStorage.setItem('perPage', newVal.toString());
  });

  watch(
    filterStatus,
    (newVal, oldVal) => {
      if (newVal && newVal.length > 0) {
        const ids = newVal.map(item => item.id);
        filters.value[`filter[download_status]`] = ids.join(',');
      } else {
        delete filters.value[`filter[download_status]`];
      }
      getTabledata();
    },
    { deep: true }
  );

  watch(
    filterCommand,
    (newVal, oldVal) => {
      if (newVal && newVal.length > 0) {
        const ids = newVal.map(item => item.id);
        filters.value[`filter[command]`] = ids.join(',');
      } else {
        delete filters.value[`filter[command]`];
      }
      getTabledata();
    },
    { deep: true }
  );

  function toggleSort(field) {
    if (sortParam.value === field) {
      sortParam.value = `-${field}`;
    } else {
      sortParam.value = field;
    }
    getTabledata();
  }

  function clearFilters() {
    filters.value = {};
    searchTerm.value = '';
    getTabledata();
  }

  function viewDetailsPane(id) {
    router.push({ name: 'configsview', params: { id: parseInt(id) }, query: { ref: 'configs' } });
  }

  return {
    viewDetailsPane,
    clearFilters,
    filterStatus,
    filterCommand,
    configs,
    isLoading,
    currentPage,
    perPage,
    lastPage,
    isDialogOpen,
    editId,
    newConfigModalKey,
    formatters,
    searchTerm,
    openDialog,
    getTabledata,
    createConfig,
    updateConfig,
    deleteConfig,
    deleteManyConfigs,
    handleSave,
    handleKeyDown,
    viewEditDialog,
    toggleSort,
    sortParam,
    showConfirmDelete
  };
}
