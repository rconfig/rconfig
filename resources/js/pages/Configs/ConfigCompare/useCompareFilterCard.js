import axios from 'axios';
import { ref, onMounted } from 'vue';

export function useCompareFilterCard(emit) {
  const daterange = ref(null);
  const commands = ref([]);

  const model = ref({
    selectedCommand: [],
    device: [],
    start_date: '',
    end_date: ''
  });

  onMounted(() => {
    // window.addEventListener('wheel', handleScroll, { passive: true });

    getDistinctCommands();
  });

  function getDistinctCommands() {
    axios
      .get('/api/configs/distinct-commands/0')
      .then(response => {
        commands.value = response.data.data;
      })
      .catch(error => {
        createNotification({
          type: 'danger',
          message: error.response.data.message
        });
      });
  }

  function clearAll() {
    // Reset the model
    model.value.selectedCommand = [];
    model.value.device = [];
    model.value.start_date = '';
    model.value.end_date = '';
    emit('updateConfigFilter', model.value);
  }

  function setDates(dateRange) {
    if (dateRange.start && dateRange.end) {
      // Convert the start and end dates to the desired format (YYYY-MM-DD)
      const startDate = `${dateRange.start.year}-${String(dateRange.start.month).padStart(2, '0')}-${String(dateRange.start.day).padStart(2, '0')}`;
      const endDate = `${dateRange.end.year}-${String(dateRange.end.month).padStart(2, '0')}-${String(dateRange.end.day).padStart(2, '0')}`;

      // Update the model with the transformed dates
      model.value.start_date = startDate;
      model.value.end_date = endDate;
    } else {
      // If no date range is provided, reset the model
      model.value.start_date = '';
      model.value.end_date = '';
    }

    // Optional: Update the Vue state or other reactive properties
    daterange.value = dateRange;
  }

  function performFilter() {
    emit('updateConfigFilter', model.value);
  }

  return {
    clearAll,
    commands,
    model,
    performFilter,
    setDates
  };
}
