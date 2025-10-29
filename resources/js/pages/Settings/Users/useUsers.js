import axios from 'axios';
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { useDialogStore } from '@/stores/dialogActions';
import { useToaster } from '@/composables/useToaster'; // Import the composable

export function useUsers() {
  const currentPage = ref(1);
  const perPage = ref(parseInt(localStorage.getItem('perPage') || '10'));
  const sortParam = ref('-id');
  const searchTerm = ref('');
  const filters = ref({});
  const dialogStore = useDialogStore();
  const editId = ref(0);
  const isLoading = ref(false);
  const lastPage = ref(1);
  const newUserModalKey = ref(1);
  const users = ref([]);
  const { openDialog } = dialogStore;
  const { toastSuccess, toastError } = useToaster(); // Using toaster for notifications

  // Fetch Users
  async function fetchUsers(params = {}) {
    isLoading.value = true;
    try {
      const response = await axios.get('/api/users', {
        params: {
          page: currentPage.value,
          perPage: perPage.value,
          sort: sortParam.value,
          ...filters.value
        }
      });
      users.value = response.data;
      lastPage.value = response.data.last_page;
    } catch (error) {
      console.error('Error fetching users:', error);
      toastError('Error', 'Failed to fetch users.');
    } finally {
      isLoading.value = false;
    }
  }

  // Create User
  const createUser = async => {
    editId.value = 0;
    newUserModalKey.value = Math.random(); // Force re-render of the dialog component
    openDialog('DialogNewUser');
  };

  function updateUser(id) {
    editId.value = id;
    newUserModalKey.value = Math.random(); // Force re-render of the dialog component
    openDialog('DialogNewUser');
  }

  // Delete User
  const deleteUser = async id => {
    try {
      await axios.delete(`/api/users/${id}`);
      fetchUsers(); // Refresh users list after deletion
      toastSuccess('User Deleted', 'The user has been deleted successfully.');
    } catch (error) {
      console.error('Error deleting user:', error);
      toastError('Error', 'Failed to delete user.');
    }
  };

  const viewEditDialog = id => {
    editId.value = id;
    newUserModalKey.value = Math.random(); // Force re-render of the dialog component
    openDialog('DialogNewUser');
  };

  // Re-render Dialog
  const handleSave = () => {
    fetchUsers(); // Fetch the updated users after saving
    newUserModalKey.value = Math.random(); // Force re-render of the dialog component
  };

  function handleKeyDown(event) {
    if (event.altKey && event.key === 'n') {
      event.preventDefault(); // Prevent default behavior (e.g., opening a new window in some browsers)
      openDialog('DialogNewUser');
    }
  }

  const debouncedFilter = useDebounceFn(() => {
    filters.value[`filter[name]`] = searchTerm.value;
    currentPage.value = 1;
    fetchUsers();
  }, 500);

  // Watchers for state changes
  watch([currentPage, perPage], () => {
    fetchUsers();
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
    fetchUsers();
  }

  return {
    users,
    isLoading,
    currentPage,
    perPage,
    lastPage,
    editId,
    newUserModalKey,
    searchTerm,
    openDialog,
    fetchUsers,
    createUser,
    updateUser,
    deleteUser,
    handleSave,
    handleKeyDown,
    viewEditDialog,
    toggleSort,
    sortParam
  };
}
