import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useToaster } from '@/composables/useToaster'; // Import the composable

export function useCompareResults(props) {
  const isLoadingComponent = ref(true);
  const configResultsLeft = ref([]);
  const configResultsRight = ref([]);
  const leftId = ref(parseInt(props.leftSelectedId[0], 10));
  const rightId = ref(parseInt(props.rightSelectedId[0], 10));
  const { toastSuccess, toastError } = useToaster(); // Using toaster for notifications

  onMounted(() => {
    isLoadingComponent.value = true;
    getConfigLeft(leftId.value);
    getConfigRight(rightId.value);

    setTimeout(
      () => {
        isLoadingComponent.value = false;
      },
      Math.floor(Math.random() * (2000 - 500 + 1)) + 500
    );
  });

  function getConfigLeft(id) {
    axios
      .get('/api/configs/' + id)
      .then(async response => {
        configResultsLeft.value = response.data;
        configResultsLeft.value['filecontent'] = await getConfigFileContent(id);
      })
      .catch(error => {
        console.error('Error fetching commands:', error);
        toastError('Error', 'Failed to fetch commands.');
      });
  }

  function getConfigRight(id) {
    axios
      .get('/api/configs/' + id)
      .then(async response => {
        configResultsRight.value = response.data;
        configResultsRight.value['filecontent'] = await getConfigFileContent(id);
      })
      .catch(error => {
        console.error('Error fetching commands:', error);
        toastError('Error', 'Failed to fetch commands.');
      });
  }

  function getConfigFileContent(id) {
    return axios
      .get('/api/configs/view-config/' + id)
      .then(response => response.data.data.content)
      .catch(error => {
        console.error('Error fetching commands:', error);
        toastError('Error', 'Failed to fetch commands.');
      });
  }

  return {
    isLoadingComponent,
    configResultsLeft,
    configResultsRight,
    getConfigLeft,
    getConfigRight,
    getConfigFileContent
  };
}
