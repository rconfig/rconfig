import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { useToaster } from '@/composables/useToaster';

export default function useTemplateAddEdit(props, emit) {
  const { toastSuccess, toastError } = useToaster();
  const errors = ref([]);
  const code = ref('');
  const model = ref({
    code: '',
    templateName: '',
    description: ''
  });

  function getDefaultTemplate(meditor) {
    axios
      .get('/api/get-default-template')
      .then(response => {
        code.value = response.data;
        meditor.setValue(response.data);
      })
      .catch(error => {
        meditor.updateOptions({
          value: 'Something went wrong - could not retrieve the default template from the file system!'
        });
        toastError('Error', 'Something went wrong - could not retrieve the default template from the file system!');
      });
  }

  function showTemplate(editId, meditor, model) {
    axios
      .get('/api/templates/' + editId)
      .then(response => {
        // handle success
        model.fileName = response.data.fileName;
        code.value = response.data.code;
        meditor.getModel().setValue(response.data.code);
      })
      .catch(error => {
        meditor.updateOptions({
          value: 'Something went wrong - could not retrieve the template from the file system!'
        });
        toastError('Error', 'Something went wrong - could not retrieve the template from the file system!');
      });
  }

  function saveDialog(editId, model, meditor, emit, close) {
    model.value.code = meditor.getValue();

    let id = editId > 0 ? `/${editId}` : ''; // determine if we are creating or updating
    let method = editId > 0 ? 'patch' : 'post'; // determine if we are creating or updating

    axios[method]('/api/templates' + id, {
      templateName: model.value.templateName,
      description: model.value.description,
      code: model.value.code
    })
      .then(response => {
        emit('save', response.data);
        toastSuccess('Template saved', 'The template has been saved successfully.');
        close();
      })
      .catch(error => {
        errors.value = error.response.data.errors;
      });
  }

  function handleKeyDown(event, saveFunction) {
    if (event.ctrlKey && event.key === 'Enter') {
      saveFunction();
    }
  }

  function fetchTemplateData(editId) {
    if (editId > 0) {
      axios.get(`/api/templates/${editId}`).then(response => {
        model.value = response.data;
      });
    }
  }

  onMounted(() => {
    fetchTemplateData(props.editId);
    window.addEventListener('keydown', event => handleKeyDown(event, () => saveDialog(props.editId, model, null, emit, () => emit('close'))));
  });

  onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
  });

  return {
    errors,
    code,
    model,
    getDefaultTemplate,
    showTemplate,
    saveDialog,
    handleKeyDown,
    fetchTemplateData
  };
}
