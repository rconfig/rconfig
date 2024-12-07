import axios from 'axios';
import { ref, onMounted } from 'vue';

export function useCompareFilterCard(emit) {
  const model = ref({
    device_name: '',
    command: '',
    device_category: '',
    search_string: '',
    lines_before: 5,
    lines_after: 5,
    latest_version_only: ref(true),
    ignore_case: ref(true),
    start_date: '',
    end_date: ''
  });
  const daterange = ref(null);
  const commands = ref([]);

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
    model.value.device_name = '';
    model.value.command = '';
    model.value.device_category = '';
    model.value.search_string = '';
    model.value.lines_before = 5;
    model.value.lines_after = 5;
    model.value.latest_version_only = ref(true);
    model.value.ignore_case = ref(true);
    model.value.start_date = '';
    model.value.end_date = '';
    emit('searchCompleted', model.value);
  }

  function setDates(dateRange) {
    if (dateRange.start && dateRange.end) {
      // Convert the start and end dates to the desired format (YYYY-MM-DD)
      const startDate = `${dateRange.start.year}-${String(dateRange.start.month).padStart(2, '0')}-${String(dateRange.start.day).padStart(2, '0')}`;
      const endDate = `${dateRange.end.year}-${String(dateRange.end.month).padStart(2, '0')}-${String(dateRange.end.day).padStart(2, '0')}`;

      // Update the model with the transformed dates
      model.start_date = startDate;
      model.end_date = endDate;
    } else {
      // If no date range is provided, reset the model
      model.start_date = '';
      model.end_date = '';
    }

    // Optional: Update the Vue state or other reactive properties
    daterange.value = dateRange;
  }

  function performSearch() {
    emit('searchCompleted', model.value);
  }

  return {
    clearAll,
    commands,
    model,
    performSearch,
    setDates
  };
}
