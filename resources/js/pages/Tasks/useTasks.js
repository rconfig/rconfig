import axios from 'axios';
import { ref, watch, inject } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { useDialogStore } from '@/stores/dialogActions';
import { useToaster } from '@/composables/useToaster'; // Import the composable
import { eventBus } from '@/composables/eventBus';

export function useTasks() {
  const currentPage = ref(1);
  const perPage = ref(parseInt(localStorage.getItem('perPage') || '10'));
  const sortParam = ref('-id');
  const searchTerm = ref('');
  const filters = ref({});
  const dialogStore = useDialogStore();
  const editId = ref(0);
  const isLoading = ref(false);
  const lastPage = ref(1);
  const newTaskModalKey = ref(1);
  const tasks = ref([]);
  const showConfirmConfirmProceedAlertAlert = ref(false);
  const proceedEditId = ref(null);
  const showConfirmDelete = ref(false);
  const { openDialog } = dialogStore;
  const { toastSuccess, toastError } = useToaster(); // Using toaster for notifications
  const formatters = inject('formatters');

  // Fetch Tasks
  async function fetchTasks(params = {}) {
    isLoading.value = true;
    try {
      const response = await axios.get('/api/tasks', {
        params: {
          page: currentPage.value,
          perPage: perPage.value,
          sort: sortParam.value,
          ...filters.value
        }
      });
      tasks.value = response.data;
      lastPage.value = response.data.last_page;
    } catch (error) {
      console.error('Error fetching tasks:', error);
      toastError('Error', 'Failed to fetch tasks.');
    } finally {
      isLoading.value = false;
    }
  }

  // Create Task
  const createTask = async => {
    editId.value = 0;
    newTaskModalKey.value = Math.random(); // Force re-render of the dialog component
    openDialog('DialogNewTask');
  };

  function updateTask(id) {
    editId.value = id;
    newTaskModalKey.value = Math.random(); // Force re-render of the dialog component
    openDialog('DialogNewTask');
  }

  // Delete Task
  const deleteTask = async id => {
    try {
      await axios.delete(`/api/tasks/${id}`);
      fetchTasks(); // Refresh tasks list after deletion
      toastSuccess('Task Deleted', 'The task has been deleted successfully.');
    } catch (error) {
      console.error('Error deleting task:', error);
      toastError('Error', 'Failed to delete task.');
    }
  };

  // Delete Many Tasks
  const deleteManyTasks = async ids => {
    try {
      await axios.post('/api/tasks/delete-many', { ids });
      fetchTasks(); // Refresh tasks list after deletion
      toastSuccess('Tasks Deleted', 'The tasks have been deleted successfully.');
      showConfirmDelete.value = false;
      eventBus.emit('deleteManyTasksSuccess');
    } catch (error) {
      if (error.status === 422) {
        toastError('Error', error.response.data.message);
      } else {
        console.error('Error deleting Tasks:', error);
        toastError('Error', 'Failed to delete Tasks.');
      }
      showConfirmDelete.value = false;
    }
  };

  const viewEditDialog = id => {
    editId.value = id;
    newTaskModalKey.value = Math.random(); // Force re-render of the dialog component
    openDialog('DialogNewTask');
  };

  // Re-render Dialog
  const handleSave = () => {
    fetchTasks(); // Fetch the updated tasks after saving
    newTaskModalKey.value = Math.random(); // Force re-render of the dialog component
  };

  function handleKeyDown(event) {
    if (event.altKey && event.key === 'n') {
      event.preventDefault(); // Prevent default behavior (e.g., opening a new window in some browsers)
      openDialog('DialogNewTask');
    }
  }

  const debouncedFilter = useDebounceFn(() => {
    filters.value[`filter[q]`] = searchTerm.value;
    currentPage.value = 1;
    fetchTasks();
  }, 500);

  // Watchers for state changes
  watch([currentPage, perPage], () => {
    fetchTasks();
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
    fetchTasks();
  }

  function runTaskConfirm(id) {
    showConfirmConfirmProceedAlertAlert.value = true;
    proceedEditId.value = id;
  }

  function runTaskNow(id) {
    console.log('Running task now...', id);
    if (!id) {
      return;
    }
    axios
      .post('/api/tasks/run-manual-task', {
        id: id
      })
      .then(response => {
        showConfirmConfirmProceedAlertAlert.value = false;
        toastSuccess('Task Run', 'The task has been run successfully.');
      })
      .catch(error => {
        console.error('Error running task:', error);
        toastError('Error', 'Failed to run task.');
      });
  }

  function pauseTask(id) {
    axios.get(`/api/tasks/toggle-pause-task/${id}`).then(() => {
      fetchTasks();
    });
  }

  return {
    createTask,
    currentPage,
    deleteTask,
    editId,
    fetchTasks,
    formatters,
    handleKeyDown,
    handleSave,
    isLoading,
    lastPage,
    newTaskModalKey,
    openDialog,
    pauseTask,
    perPage,
    proceedEditId,
    runTaskConfirm,
    runTaskNow,
    searchTerm,
    showConfirmConfirmProceedAlertAlert,
    sortParam,
    tasks,
    toggleSort,
    updateTask,
    viewEditDialog,
    showConfirmDelete,
    deleteManyTasks
  };
}
