import axios from 'axios';
import { ref, onMounted, reactive, computed } from 'vue';
import { useToaster } from '@/composables/useToaster'; // Import the composable

export function useSystemSettingsEmail() {
  const { toastSuccess, toastError } = useToaster(); // Using toaster for notifications
  const settings = ref({});

  onMounted(() => {
    getSettings();
  });

  function getSettings() {
    axios.get('/api/settings/email/1').then(response => {
      settings.value = response.data;
    });
  }

  return {
    settings
  };
}
