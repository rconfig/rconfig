import { ref } from 'vue';
import useClipboard from 'vue-clipboard3';

export function useCopy() {
  const { toClipboard } = useClipboard();
  const activeCopyIcon = ref({});

  const copy = async (key, value) => {
    try {
      await toClipboard(JSON.stringify(value));
      activeCopyIcon.value[key] = true;
      setTimeout(() => {
        activeCopyIcon.value[key] = false;
      }, 1500);
    } catch (e) {
      console.error('Failed to copy:', e);
    }
  };

  return {
    copy,
    activeCopyIcon
  };
}
