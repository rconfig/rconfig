import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useToaster } from '@/composables/useToaster'; // Import the composable

export function useCompareResults(props, emit) {
  const { toastSuccess, toastError } = useToaster(); // Using toaster for notifications

  const isLoadingComponent = ref(true);
  const configResultsLeft = ref([]);
  const configResultsRight = ref([]);
  const leftId = ref(parseInt(props.leftSelectedId[0], 10));
  const rightId = ref(parseInt(props.rightSelectedId[0], 10));

  onMounted(() => {
    getConfigLeft(leftId.value);
    getConfigRight(rightId.value);

    setTimeout(
      () => {
        isLoadingComponent.value = false;
      },
      Math.floor(Math.random() * (2000 - 500 + 1)) + 500
    );
  });

  async function getConfigLeft(id) {
    try {
      const response = await axios.get('/api/configs/' + id);
      configResultsLeft.value = response.data;
      configResultsLeft.value['content'] = await getConfigFileContent(id);
    } catch (error) {
      console.error('Error fetching commands:', error);
      toastError('Error', 'Failed to fetch commands.');
    } finally {
    }
  }

  async function getConfigRight(id) {
    try {
      const response = await axios.get('/api/configs/' + id);
      configResultsRight.value = response.data;
      configResultsRight.value['content'] = await getConfigFileContent(id);
    } catch (error) {
      console.error('Error fetching commands:', error);
      toastError('Error', 'Failed to fetch commands.');
    } finally {
    }
  }

  async function getConfigFileContent(id) {
    try {
      const response = await axios.get('/api/configs/view-config/' + id);
      return response.data.data.content;
    } catch (error) {
      console.error('Error fetching commands:', error);
      toastError('Error', 'Failed to fetch commands.');
    }
  }

  return {
    configResultsLeft,
    configResultsRight,
    isLoadingComponent
  };
}
