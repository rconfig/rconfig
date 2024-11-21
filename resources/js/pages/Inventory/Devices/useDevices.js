import axios from 'axios';
import { ref, watch, inject } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { useDialogStore } from '@/stores/dialogActions';
import { useToaster } from '@/composables/useToaster'; // Import the composable
import { eventBus } from '@/composables/eventBus';
import { useRoute, useRouter } from 'vue-router'; // Import the useRoute from Vue Router

export function useDevices() {
  const currentPage = ref(1);
  const perPage = ref(parseInt(localStorage.getItem('perPage') || '10'));
  const sortParam = ref('-id');
  const searchTerm = ref('');
  const filters = ref({});
  const dialogStore = useDialogStore();
  const editId = ref(0);
  const isLoading = ref(false);
  const lastPage = ref(1);
  const newDeviceModalKey = ref(1);
  const devices = ref([]);
  const showConfirmDelete = ref(false);
  const filterStatus = ref([]);
  const filterCategories = ref([]);
  const filterTags = ref([]);
  const filterVendor = ref([]);
  const router = useRouter();
  const formatters = inject('formatters');

  const { openDialog } = dialogStore;
  const { toastSuccess, toastError } = useToaster(); // Using toaster for notifications

  // Fetch Devices
  async function fetchDevices(params = {}) {
    isLoading.value = true;
    try {
      const response = await axios.get('/api/devices', {
        params: {
          page: currentPage.value,
          perPage: perPage.value,
          sort: sortParam.value,
          ...filters.value
        }
      });
      devices.value = response.data;
      lastPage.value = response.data.last_page;
    } catch (error) {
      console.error('Error fetching devices:', error);
      toastError('Error', 'Failed to fetch devices.');
    } finally {
      isLoading.value = false;
    }
  }

  // Create Device
  const createDevice = async => {
    editId.value = 0;
    newDeviceModalKey.value = Math.random(); // Force re-render of the dialog component
    openDialog('DialogNewDevice');
  };

  function updateDevice(id) {
    editId.value = id;
    newDeviceModalKey.value = Math.random(); // Force re-render of the dialog component
    openDialog('DialogNewDevice');
  }

  // Delete Device
  function purgeDeviceConfigs(id) {
    console.log('Purge device configs:', id);
    axios
      .post('/api/device/purge-failed-configs', {
        device_id: id
      })
      .then(response => {
        toastSuccess('Device Configurations Purged', 'The device failed configurations purge jobs has been successfully sent to the queue.');
      })
      .catch(error => {
        console.error('Error Purge device configs:', error);
        toastError('Error', 'Failed to purge device configurations.');
      });
  }

  // Delete Device
  const deleteDevice = async id => {
    try {
      await axios.delete(`/api/devices/${id}`);
      fetchDevices(); // Refresh devices list after deletion
      toastSuccess('Device Deleted', 'The device has been deleted successfully.');
    } catch (error) {
      console.error('Error deleting device:', error);
      toastError('Error', 'Failed to delete device.');
    }
  };

  // Delete Many Devices
  const deleteManyDevices = async ids => {
    try {
      await axios.post('/api/devices/delete-many', { ids });
      fetchDevices(); // Refresh tags list after deletion
      toastSuccess('Devices Deleted', 'The tags have been deleted successfully.');
      showConfirmDelete.value = false;
      eventBus.emit('deleteManyDevicesSuccess');
    } catch (error) {
      console.error('Error deleting tags:', error);
      toastError('Error', 'Failed to delete devices.');
      showConfirmDelete.value = false;
    }
  };

  // Disable Device
  const disableDevice = async id => {
    try {
      await axios.get(`/api/device/disable/${id}`);
      fetchDevices(); // Refresh devices list after disabling
      toastSuccess('Device Disabled', 'The device has been disabled successfully.');
    } catch (error) {
      console.error('Error disabling device:', error);
      toastError('Error', 'Failed to disable device.');
    }
  };

  // Enable Device
  const enableDevice = async id => {
    try {
      await axios.get(`/api/device/enable/${id}`);
      fetchDevices(); // Refresh devices list after disabling
      toastSuccess('Device Enabled', 'The device has been disabled successfully.');
    } catch (error) {
      console.error('Error enabling device:', error);
      toastError('Error', 'Failed to enabled device.');
    }
  };

  const viewEditDialog = id => {
    editId.value = id;
    newDeviceModalKey.value = Math.random(); // Force re-render of the dialog component
    openDialog('DialogNewDevice');
  };

  // Re-render Dialog
  const handleSave = () => {
    fetchDevices(); // Fetch the updated devices after saving
    newDeviceModalKey.value = Math.random(); // Force re-render of the dialog component
  };

  function handleKeyDown(event) {
    if (event.altKey && event.key === 'n') {
      event.preventDefault(); // Prevent default behavior (e.g., opening a new window in some browsers)
      openDialog('DialogNewDevice');
    }
  }

  const debouncedFilter = useDebounceFn(() => {
    filters.value[`filter[q]`] = searchTerm.value;
    currentPage.value = 1;
    fetchDevices();
  }, 500);

  // Watchers for state changes
  watch([currentPage, perPage], () => {
    fetchDevices();
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
        filters.value[`filter[status]`] = ids.join(',');
      } else {
        delete filters.value[`filter[status]`];
      }
      fetchDevices();
    },
    { deep: true }
  );

  watch(
    filterCategories,
    (newVal, oldVal) => {
      if (newVal && newVal.length > 0) {
        const ids = newVal.map(item => item.id);
        filters.value[`filter[category]`] = ids.join(',');
      } else {
        delete filters.value[`filter[category]`];
      }
      fetchDevices();
    },
    { deep: true }
  );

  watch(
    filterTags,
    (newVal, oldVal) => {
      if (newVal && newVal.length > 0) {
        const ids = newVal.map(item => item.id);
        filters.value[`filter[tag]`] = ids.join(',');
      } else {
        delete filters.value[`filter[tag]`];
      }
      fetchDevices();
    },
    { deep: true }
  );

  watch(
    filterVendor,
    (newVal, oldVal) => {
      if (newVal && newVal.length > 0) {
        const ids = newVal.map(item => item.id);
        filters.value[`filter[vendor]`] = ids.join(',');
      } else {
        delete filters.value[`filter[vendor]`];
      }
      fetchDevices();
    },
    { deep: true }
  );

  function toggleSort(field) {
    if (sortParam.value === field) {
      sortParam.value = `-${field}`;
    } else {
      sortParam.value = field;
    }
    fetchDevices();
  }

  function clearFilters() {
    filters.value = {};
    filterStatus.value = [];
    filterCategories.value = [];
    filterTags.value = [];
    filterVendor.value = [];
    searchTerm.value = '';
    fetchDevices();
  }

  function viewDeviceDetailsPane(id) {
    router.push({ name: 'devicesview', params: { id: parseInt(id) } });
  }

  return {
    clearFilters,
    createDevice,
    currentPage,
    deleteDevice,
    deleteManyDevices,
    devices,
    disableDevice,
    editId,
    enableDevice,
    fetchDevices,
    filterCategories,
    filterStatus,
    filterTags,
    filterVendor,
    formatters,
    handleKeyDown,
    handleSave,
    isLoading,
    lastPage,
    newDeviceModalKey,
    perPage,
    purgeDeviceConfigs,
    searchTerm,
    showConfirmDelete,
    sortParam,
    toggleSort,
    updateDevice,
    viewDeviceDetailsPane,
    viewEditDialog
  };
}
