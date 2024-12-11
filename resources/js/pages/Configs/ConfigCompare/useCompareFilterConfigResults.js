import axios from 'axios';
import { ref, onMounted, watch } from 'vue';
import { useToaster } from '@/composables/useToaster'; // Import the composable

export function useCompareFilterConfigResults(props, emit) {
  const { toastSuccess, toastError } = useToaster(); // Using toaster for notifications

  const isLoading = ref(false);
  const currentPage = ref(1);
  const lastPage = ref(1);
  const perPage = ref(parseInt(localStorage.getItem('perPage') || '5'));
  const results = ref([]);

  onMounted(() => {
    if (props.filterData.device.length >= 1) {
      getFilteredConfigRecords();
    }
  });

  function getFilteredConfigRecords() {
    isLoading.value = true;
    if (props.filterData.device.length === 0) {
      console.error('No device selected.');
      return;
    }
    axios
      .get('/api/configs/all-by-deviceid/' + props.filterData.device[0].id + '/1?filter[q]=' + props.filterData.selectedCommand, {
        params: {
          perPage: perPage.value,
          page: currentPage.value
        }
      })
      .then(response => {
        results.value = response.data;
        lastPage.value = response.data.last_page;
        isLoading.value = false;
      })
      .catch(error => {
        console.error('Error fetching commands:', error);
        toastError('Error', 'Failed to fetch commands.');
        isLoading.value = false;
      });
  }

  // Watchers for state changes
  watch([currentPage, perPage], () => {
    getFilteredConfigRecords();
  });

  watch(perPage, newVal => {
    localStorage.setItem('perPage', newVal.toString());
  });

  return {
    currentPage,
    isLoading,
    lastPage,
    perPage,
    results
  };
}
