import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { useToaster } from '@/composables/useToaster';

export default function useConfigViewMainPanel(props) {
  const { toastSuccess, toastError } = useToaster();
  const errors = ref([]);
  const code = ref('');
  const model = ref({
    code: '',
    templateName: '',
    description: ''
  });

  function getDefaultEditorCode(meditor) {
    meditor.updateOptions({
      value: 'Something went wrong - could not retrieve the configuration from the file system!'
    });
    toastError('Error', 'Something went wrong - could not retrieve the configuration from the file system!');
  }

  function showConfiguration(configId, meditor) {
    axios
      .get('/api/configs/view-config/' + configId)
      .then(response => {
        // handle success
        meditor.getModel().setValue(response.data.data.content);
      })
      .catch(error => {
        console.error(error);
        meditor.updateOptions({
          value: 'Something went wrong - could not retrieve the configuration from the file system!'
        });
        toastError('Error', 'Something went wrong - could not retrieve the configuration from the file system!');
      });
  }

  function handleKeyDown(event, saveFunction) {
    if (event.ctrlKey && event.key === 'Enter') {
      saveFunction();
    }
  }

  onMounted(() => {
    window.addEventListener('keydown', event => handleKeyDown(event, () => saveDialog(props.configId, model, null, emit, () => emit('close'))));
  });

  onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
  });

  return {
    errors,
    code,
    model,
    getDefaultEditorCode,
    showConfiguration,
    handleKeyDown
  };
}
