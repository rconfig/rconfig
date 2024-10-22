import axios from 'axios';
import { ref, onMounted, onUnmounted } from 'vue';
import { useToaster } from '@/composables/useToaster'; // Import the composable

export function useAddEditDevices(editId) {
  const { toastSuccess, toastError, toastInfo, toastWarning, toastDefault } = useToaster();
  const errors = ref([]);

  const model = ref({
    device_name: '',
    device_ip: '',
    device_port_override: '',
    device_vendor: '',
    device_model: '',
    device_category_id: '',
    device_tags: '',
    device_username: '',
    device_password: '',
    device_enable_password: '',
    device_template: '',
    device_main_prompt: '',
    device_enable_prompt: ''
  });

  onMounted(() => {
    if (editId > 0) {
      axios.get(`/api/devices/${editId}`).then(response => {
        model.value = response.data;
      });
    }

    window.addEventListener('keydown', handleKeyDown);
  });

  onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
  });

  function handleKeyDown(event) {
    if (event.ctrlKey && event.key === 'Enter') {
      saveDialog();
    }
  }

  function saveDialog() {
    let id = editId > 0 ? `/${editId}` : ''; // determine if we are creating or updating
    let method = editId > 0 ? 'patch' : 'post'; // determine if we are creating or updating
    axios[method]('/api/devices' + id, model.value)
      .then(response => {
        // emit('save', response.data);
        toastSuccess('Device created', 'The device has been created successfully.');
        closeDialog('DialogNewDevice');
      })
      .catch(error => {
        errors.value = error.response.data.errors;
      });
  }
  return { model, saveDialog, errors };
}
