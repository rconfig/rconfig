import axios from 'axios';
import { ref, onMounted, onUnmounted } from 'vue';
import { usePanelStore } from '@/stores/panelStore'; // Import the Pinia store
import { useToaster } from '@/composables/useToaster'; // Import the composable

export function useConfigViewPane(props, emit) {
  const configData = ref(null);
  const isLoading = ref(false);
  const panelElement2 = ref(null);
  const panelStore = usePanelStore(); // Access the panel store
  const { toastError } = useToaster(); // Using toaster for notifications

  onMounted(() => {
    fetchConfig();

    panelStore.panelRef2 = panelElement2.value;

    window.addEventListener('keydown', e => {
      if (e.key === 'Escape') {
        onEsc();
      }
    });
  });

  onUnmounted(() => {
    window.removeEventListener('keydown', e => {
      if (e.key === 'Escape') {
        onEsc();
      }
    });
  });

  function fetchConfig() {
    axios
      .get(`/api/configs/${props.configId}`)
      .then(response => {
        configData.value = response.data;
        isLoading.value = false;
      })
      .catch(error => {
        console.error(error);
        toastError('Error', 'Something went wrong - could not retrieve the configuration from the file system!');
      });
  }

  function close() {
    emit('close');
  }

  function onEsc() {
    close();
  }

  return {
    configData,
    isLoading,
    panelElement2
  };
}
