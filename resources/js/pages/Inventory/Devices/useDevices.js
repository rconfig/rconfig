import axios from 'axios';
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { useDialogStore } from '@/stores/dialogActions';
import { useToaster } from '@/composables/useToaster'; // Import the composable

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

  function toggleSort(field) {
    if (sortParam.value === field) {
      sortParam.value = `-${field}`;
    } else {
      sortParam.value = field;
    }
    fetchDevices();
  }

  return {
    devices,
    isLoading,
    currentPage,
    perPage,
    lastPage,
    editId,
    newDeviceModalKey,
    searchTerm,
    openDialog,
    fetchDevices,
    createDevice,
    updateDevice,
    deleteDevice,
    handleSave,
    handleKeyDown,
    viewEditDialog,
    toggleSort,
    sortParam
  };
}
