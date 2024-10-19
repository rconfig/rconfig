import axios from 'axios';
import { ref, onMounted, reactive, computed } from 'vue';
import { useToaster } from '@/composables/useToaster'; // Import the composable

export function useSystemSettingsEmail() {
  const { toastSuccess, toastError } = useToaster(); // Using toaster for notifications
  const settings = ref({});
  const test1Loading = ref(false);
  const test2Loading = ref(false);

  onMounted(() => {
    getSettings();
  });

  function getSettings() {
    axios.get('/api/settings/email/1').then(response => {
      settings.value = response.data;
    });
  }

  function updateEmail() {
    axios
      .patch('/api/settings/email/1', {
        mail_driver: 'smtp',
        mail_host: settings.value.mail_host,
        mail_port: settings.value.mail_port,
        mail_username: settings.value.mail_username,
        mail_password: settings.value.mail_password,
        mail_from_email: settings.value.mail_from_email,
        mail_to_email: settings.value.mail_to_email,
        mail_authcheck: settings.value.mail_authcheck,
        mail_encryption: settings.value.mail_encryption
      })
      .then(response => {
        toastSuccess(response.data.message);
      })
      .catch(error => {
        toastError(error.response.data.message);
      });
  }

  function testEmail(type) {
    switch (type) {
      case 'email':
        test1Loading.value = true;
        break;
      case 'notification':
        test2Loading.value = true;
        break;
    }
    axios
      .get('/api/settings/test-' + type)
      .then(response => {
        toastSuccess(response.data.message);
        test1Loading.value = false;
        test2Loading.value = false;
      })
      .catch(error => {
        toastError(error.response.data.message);
      });
  }

  return {
    settings,
    test1Loading,
    test2Loading,
    testEmail,
    updateEmail
  };
}
