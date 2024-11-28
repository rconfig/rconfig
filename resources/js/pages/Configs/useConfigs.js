import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router'; // Import useRoute for accessing route parameters

export function useConfigs() {
  const route = useRoute();
  const configsId = ref(parseInt(route.params.id) || 0);
  const statusIdParam = ref(route.query || null);

  return {
    configsId,
    statusIdParam
  };
}
