import axios from 'axios';
import { ref, onMounted, reactive, computed } from 'vue';
import { useToaster } from '@/composables/useToaster'; // Import the composable

export function useAbout() {
  const { toastSuccess, toastError } = useToaster(); // Using toaster for notifications
  const licenseInfo = reactive({});

  onMounted(() => {
    getLicenseInfo();
  });

  function getLicenseInfo() {
    axios.get('/api/license-info').then(response => {
      Object.assign(licenseInfo, response.data.data);
    });
  }

  return {
    licenseInfo
  };
}
