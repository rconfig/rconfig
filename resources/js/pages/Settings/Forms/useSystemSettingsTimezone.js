import axios from 'axios';
import { ref, onMounted, reactive, computed } from 'vue';
import { useToaster } from '@/composables/useToaster'; // Import the composable

export function useSystemSettingsTimezone() {
  const { toastSuccess, toastError } = useToaster(); // Using toaster for notifications

  const popoverState = ref(false);
  const timezones = reactive({});
  const currentTimezone = ref('');
  const searchTerm = ref('');

  // Computed property for filtered timezones based on search term
  const filteredTimezones = computed(() => {
    if (!searchTerm.value) {
      return timezones; // Return all timezones if searchTerm is empty
    }
    // Filter the timezones object and return a new object
    return Object.fromEntries(Object.entries(timezones).filter(([key, value]) => value.toLowerCase().includes(searchTerm.value.toLowerCase())));
  });

  onMounted(() => {
    getConfiguredTimeZone();
    getTimezonelist();
  });

  function getConfiguredTimeZone() {
    axios
      .get('/api/settings/timezone/1', {})
      .then(response => {
        currentTimezone.value = response.data.timezone;
      })
      .catch(error => {
        // handle error
        console.log(error);
      });
  }

  function getTimezonelist() {
    axios
      .get('/api/settings/get-timezone-list')
      .then(response => {
        Object.assign(timezones, response.data);
      })
      .catch(error => {
        console.log(error);
        toastError('Error', 'Failed to get Timezone list');

        return [];
      });
  }

  function changeTimezone(timezone) {
    axios
      .patch('/api/settings/timezone/1', {
        timezone: timezone
      })
      .then(response => {
        popoverState.value = false;
        toastSuccess('Timezone set successfully', 'Timezone offset set to ' + timezone);
      })
      .catch(error => {
        console.log(error);
        toastError('Error', 'Failed to set timezone');
        popoverState.value = false;
      });
  }

  return {
    popoverState,
    changeTimezone,
    currentTimezone,
    searchTerm,
    filteredTimezones
  };
}
