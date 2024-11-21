import axios from 'axios';
import useClipboard from 'vue-clipboard3';
import { ref, onMounted, onUnmounted } from 'vue';
import { usePanelStore } from '@/stores/panelStore'; // Import the Pinia store
import { useToaster } from '@/composables/useToaster'; // Import the composable

export function useConfigViewPane(props) {
  const configData = ref(null);
  const isLoading = ref(false);
  const mainNavSelected = ref('notifications');
  const panelElement2 = ref(null);
  const panelStore = usePanelStore(); // Access the panel store
  const { toClipboard } = useClipboard();
  const { toastSuccess, toastError } = useToaster(); // Using toaster for notifications

  onMounted(() => {
    fetchConfig(props.editId);

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

  function fetchConfig(id) {
    isLoading.value = true;
    axios.get(`/api/configs/${id}`).then(response => {
      configData.value = response.data;
      isLoading.value = false;
    });
  }

  function close() {
    emit('close');
  }

  function onEsc() {
    close();
  }

  function closeNav() {
    panelElement2?.value.isCollapsed ? panelElement2?.value.expand() : panelElement2?.value.collapse();
  }

  return {
    closeNav,
    configData,
    isLoading,
    panelElement2
  };
}
