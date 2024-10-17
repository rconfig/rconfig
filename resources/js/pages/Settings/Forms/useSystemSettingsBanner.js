import axios from 'axios';
import { ref, watch } from 'vue';
import { useToaster } from '@/composables/useToaster'; // Import the composable

export function useSystemSettingsBanner() {
  const banner = ref('');
  const { toastSuccess, toastError } = useToaster(); // Using toaster for notifications

  function getLoginBanner() {
    axios
      .get('/api/settings/banner/1')
      .then(response => {
        // handle success
        banner.value = response.data.login_banner;
        toastSuccess('Success', 'Banner loaded successfully');
      })
      .catch(error => {
        // handle error
        console.log(error.response);
        toastError('Error', 'Failed to load banner');
      });
  }

  function saveBanner() {
    axios
      .patch('/api/settings/banner/1', {
        login_banner: banner.value
      })
      .then(response => {
        // handle success
        toastSuccess('Success', 'Banner saved successfully');
      })
      .catch(error => {
        // handle error
        console.log(error);
        toastError('Error', 'Failed to save banner');
      });
  }

  function resetBanner() {
    banner.value = 'Authorization message - You must be an authorized user to login and use this system.';
    saveBanner();
  }

  return {
    banner,
    getLoginBanner,
    saveBanner,
    resetBanner
  };
}
