import { onMounted, ref, inject } from 'vue';
import { useSheetStore } from '@/stores/sheetActions';
import axios from 'axios';
import { useRouter } from 'vue-router';

export function useDeviceComments(props) {
  const sheetStore = useSheetStore();
  const { openSheet, closeSheet, isSheetOpen } = sheetStore;
  const comments = ref([]);
  const isLoading = ref(false);
  const hasAddedComment = ref(false);
  const router = useRouter();
  const formatters = inject('formatters');

  onMounted(() => {
    if (isSheetOpen('DeviceCommentSheet')) {
      getComments();
    }
  });

  function getComments() {
    isLoading.value = true;
    axios
      .get(`/api/device-comments/${props.deviceId}`)
      .then(response => {
        comments.value = response.data;
        isLoading.value = false;
      })
      .catch(error => {
        console.error(error);
        isLoading.value = false;
      });
  }

  function viewDevice(deviceId) {
    closeSheet('DeviceCommentSheet');
    router.push({ name: 'device', params: { id: deviceId } });
  }

  function addComment() {
    if (!hasAddedComment.value) {
      comments.value.unshift({
        user: { name: 'New User' },
        created_at: new Date().toISOString(),
        comment: '',
        is_open: true,
        isEditable: true
      });
      hasAddedComment.value = true;
    }
  }

  function saveComment(index) {
    comments.value[index].isEditable = false;
    axios
      .post(`/api/device/comments`, {
        comment: comments.value[index].comment,
        device_id: props.deviceId
      })
      .then(response => {
        // Add code here to update the comment with the response data if needed
      })
      .catch(error => {
        console.error(error);
      });
    // Add code here to persist the comment changes to the backend if needed
  }

  return {
    addComment,
    closeSheet,
    comments,
    formatters,
    isLoading,
    isSheetOpen,
    saveComment,
    viewDevice
  };
}
