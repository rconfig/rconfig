import axios from 'axios';
import { eventBus } from '@/composables/eventBus';
import { ref, onMounted, watch, inject } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { useToaster } from '@/composables/useToaster'; // Import the composable

export function useSystemLogs() {
  const { toastSuccess, toastError } = useToaster(); // Using toaster for notifications

  const currentPage = ref(1);
  const filters = ref({});
  const isLoading = ref(false);
  const lastPage = ref(1);
  const logs = ref({});
  const perPage = ref(parseInt(localStorage.getItem('perPage') || '10'));
  const searchTerm = ref('');
  const showConfirmDelete = ref(false);
  const sortParam = ref('-id');
  const formatters = inject('formatters');
  const filterSeverity = ref([]);

  onMounted(() => {
    fetchLogs();
  });

  async function fetchLogs(params = {}) {
    isLoading.value = true;
    try {
      const response = await axios.get('/api/activitylogs', {
        params: {
          page: currentPage.value,
          perPage: perPage.value,
          sort: sortParam.value,
          ...filters.value
        }
      });
      logs.value = response.data;
      lastPage.value = response.data.last_page;
    } catch (error) {
      console.error('Error fetching logs:', error);
      toastError('Error', 'Failed to fetch logs.');
    } finally {
      isLoading.value = false;
    }
  }

  watch(
    filterSeverity,
    (newVal, oldVal) => {
      console.log('Filter severity:', newVal);
      if (newVal && newVal.length > 0) {
        const names = newVal.map(item => item.name);
        filters.value[`filter[log_name]`] = names.join(',');
      } else {
        delete filters.value[`filter[log_name]`];
      }
      fetchLogs();
    },
    { deep: true }
  );

  // Delete Log
  const deleteLog = async id => {
    try {
      await axios.delete(`/api/activitylogs/${id}`);
      fetchLogs(); // Refresh logs list after deletion
      toastSuccess('Log Deleted', 'The log has been deleted successfully.');
      showConfirmDelete.value = false;
    } catch (error) {
      console.error('Error deleting log:', error);
      toastError('Error', 'Failed to delete log.');
      showConfirmDelete.value = false;
    }
  };

  // Delete Many Tags
  const deleteManyLogs = async ids => {
    try {
      await axios.post('/api/activitylogs/delete-many', { ids });
      fetchLogs(); // Refresh logs list after deletion
      toastSuccess('Logs Deleted', 'The logs have been deleted successfully.');
      showConfirmDelete.value = false;
      eventBus.emit('deleteManyLogsSuccess');
    } catch (error) {
      console.error('Error deleting logs:', error);
      toastError('Error', 'Failed to delete logs.');
      showConfirmDelete.value = false;
    }
  };

  const debouncedFilter = useDebounceFn(() => {
    filters.value[`filter[description]`] = searchTerm.value;
    currentPage.value = 1;
    fetchLogs();
  }, 500);

  // Watchers for state changes
  watch([currentPage, perPage], () => {
    fetchLogs();
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
    fetchLogs();
  }

  function clearFilters() {
    filters.value = {};
    filterSeverity.value = [];
    searchTerm.value = '';
    fetchLogs();
  }

  return {
    clearFilters,
    currentPage,
    deleteLog,
    deleteManyLogs,
    fetchLogs,
    filters,
    filterSeverity,
    formatters,
    isLoading,
    lastPage,
    logs,
    perPage,
    searchTerm,
    showConfirmDelete,
    sortParam,
    toggleSort
  };
}
