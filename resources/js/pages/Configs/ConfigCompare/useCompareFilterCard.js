import axios from 'axios';
import { computed, ref, watch } from 'vue';
import { useToaster } from '@/composables/useToaster';

export function useCompareFilterCard(emit) {
  const { toastError } = useToaster();
  const daterange = ref(null);
  const commands = ref([]);
  const isLoadingCommands = ref(false);
  let latestCommandsRequest = 0;

  const model = ref({
    selectedCommand: '',
    device: {},
    start_date: '',
    end_date: ''
  });

  const selectedDeviceId = computed(() => model.value.device?.id ?? null);
  const hasDevice = computed(() => selectedDeviceId.value !== null);

  // Only list commands stored for the selected device; a command from the previous device is never valid.
  watch(selectedDeviceId, deviceId => {
    model.value.selectedCommand = '';

    if (deviceId === null) {
      latestCommandsRequest++;
      commands.value = [];
      isLoadingCommands.value = false;
      return;
    }

    getDistinctCommands(deviceId);
  });

  function getDistinctCommands(deviceId) {
    const requestId = ++latestCommandsRequest;
    commands.value = [];
    isLoadingCommands.value = true;

    axios
      .get(`/api/configs/distinct-commands/${deviceId}`)
      .then(response => {
        if (requestId !== latestCommandsRequest) {
          return;
        }
        commands.value = response.data.data;
      })
      .catch(error => {
        if (requestId !== latestCommandsRequest) {
          return;
        }
        toastError('Error', error.response?.data?.message || 'Failed to load commands.');
      })
      .finally(() => {
        if (requestId === latestCommandsRequest) {
          isLoadingCommands.value = false;
        }
      });
  }

  function clearAll() {
    // Reset the model
    model.value.selectedCommand = '';
    model.value.device = {};
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
    hasDevice,
    isLoadingCommands,
    model,
    performFilter,
    setDates
  };
}
