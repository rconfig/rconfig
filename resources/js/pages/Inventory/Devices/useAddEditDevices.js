import axios from 'axios';
import { ref, onMounted, onUnmounted } from 'vue';
import { useToaster } from '@/composables/useToaster'; // Import the composable
import { useDialogStore } from '@/stores/dialogActions';

export function useAddEditDevices(editId, emit, isClone) {
  const { toastSuccess, toastError, toastInfo, toastWarning, toastDefault } = useToaster();
  const dialogStore = useDialogStore();
  const { closeDialog, isDialogOpen } = dialogStore;
  const showConfirmCloseAlert = ref(false);
  const isLoading = ref(false);

  const errors = ref([]);

  const model = ref({
    device_name: '',
    device_ip: '',
    device_port_override: '',
    device_cred_id: 0,
    device_cred: [],
    device_vendor: [],
    device_model: '',
    selectedCategoryArray: [],
    selectedModelArray: [],
    device_category_id: 0,
    device_tags: [],
    device_username: '',
    device_password: '',
    device_enable_password: '',
    device_template: [],
    device_main_prompt: '',
    device_enable_prompt: ''
  });

  onMounted(() => {
    console.log('editId', editId);
    console.log('isClone', isClone);
    if (editId > 0) {
      isLoading.value = true;
      axios.get(`/api/devices/${editId}`).then(response => {
        model.value.device_name = response.data.device_name;
        model.value.device_ip = response.data.device_ip;
        if (isClone === true) {
          model.value.device_name = response.data.device_name + '-Clone';
          model.value.device_ip = response.data.device_ip + '-Clone';
        }
        model.value.device_port_override = response.data.device_port_override;
        model.value.device_cred_id = response.data.device_cred_id;
        model.value.device_cred.push(response.data.device_cred);
        model.value.device_vendor.push(response.data.vendor[0]);
        model.value.device_model = response.data.device_model;
        model.value.selectedCategoryArray.push(response.data.category[0]);
        model.value.selectedModelArray = [{ name: response.data.device_model }];
        model.value.device_category_id = response.data.device_category_id;
        response.data.tag.forEach(tag => {
          model.value.device_tags.push(tag);
        });
        model.value.device_username = response.data.device_username;
        model.value.device_password = response.data.device_password;
        model.value.device_enable_password = response.data.device_enable_password;
        model.value.device_template.push(response.data.template[0]);
        model.value.device_main_prompt = response.data.device_main_prompt;
        model.value.device_enable_prompt = response.data.device_enable_prompt;
        isLoading.value = false;
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
    if (model.value.selectedModelArray.length > 0) {
      model.value.device_model = model.value.selectedModelArray[0].name;
    }
    if (model.value.selectedCategoryArray.length > 0) {
      model.value.device_category_id = model.value.selectedCategoryArray[0].id;
    }

    let id = editId > 0 ? `/${editId}` : ''; // determine if we are creating or updating
    let method = editId > 0 ? 'patch' : 'post'; // determine if we are creating or updating

    if (isClone === true) {
      id = '';
      method = 'post';
      console.log('id', id);
    }
    axios[method]('/api/devices' + id, model.value)
      .then(response => {
        emit('save', response.data);
        toastSuccess('Device created', 'The device has been created successfully.');
        closeDialog('DialogNewDevice');
      })
      .catch(error => {
        toastError('Error', 'There was an error saving the device.');
        console.log(error);
        if (error.response && error.response.data && error.response.data.errors) {
          errors.value = error.response.data.errors;
        }
      });
  }

  function generatePrompts() {
    model.value.device_main_prompt = model.value.device_name + '#';
    model.value.device_enable_prompt = model.value.device_name + '>';
  }

  function showConfirmCloseDialog() {
    showConfirmCloseAlert.value = true;
  }

  function cancelCloseDialog() {
    showConfirmCloseAlert.value = false;
  }

  function confirmCloseDialog() {
    showConfirmCloseAlert.value = false;
    emit('close');
    closeDialog('DialogNewDevice');
  }

  return { isLoading, model, saveDialog, errors, isDialogOpen, generatePrompts, showConfirmCloseAlert, showConfirmCloseDialog, cancelCloseDialog, confirmCloseDialog };
}
