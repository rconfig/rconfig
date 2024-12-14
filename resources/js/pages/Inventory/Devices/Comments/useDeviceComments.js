import axios from 'axios';
import { onMounted, ref, inject } from 'vue';
import { useCommentsStore } from '@/stores/useCommentsStore';
import { useRouter } from 'vue-router';
import { useSheetStore } from '@/stores/sheetActions';

export function useDeviceComments(props, emit) {
  const sheetStore = useSheetStore();
  const commentsStore = useCommentsStore();
  const { incrementCounter, decrementCounter } = commentsStore;
  const { openSheet, closeSheet, isSheetOpen } = sheetStore;
  const comments = ref([]);
  const isLoading = ref(false);
  const router = useRouter();
  const formatters = inject('formatters');
  const activeCommentsView = ref(true);
  const closedCommentsView = ref(false);
  const newCommentKey = ref(1);

  onMounted(() => {
    if (props.deviceId != 0) {
      commentsStore.initializeCommentsForDevice(props.deviceId); // Initialize comments for the device
    }

    if (isSheetOpen('DeviceCommentSheet') || props.isDeviceCommentsPanelView) {
      if (activeCommentsView.value) {
        getActiveComments();
      }

      if (closedCommentsView.value) {
        getCloseComments();
      }
    }
  });

  function getActiveComments() {
    comments.value = [];
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

  function getCloseComments() {
    comments.value = [];
    isLoading.value = true;
    axios
      .get(`/api/device-closed-comments/${props.deviceId}`)
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
    comments.value.unshift({
      user: { name: 'New User' },
      created_at: new Date().toISOString(),
      comment: '',
      is_open: true,
      isEditable: true
    });
  }

  function saveComment(commentContent) {
    newCommentKey.value++;
    axios
      .post(`/api/device/comments`, {
        comment: commentContent,
        device_id: props.deviceId
      })
      .then(response => {
        emit('commentsaved');
        getActiveComments();
        incrementCounter(props.deviceId); // Increment counter
      })
      .catch(error => {
        console.error(error);
      });
  }

  function closeComment(id) {
    axios
      .get(`/api/device/comments/${id}/close`)
      .then(response => {
        emit('commentsaved');
        getActiveComments();
        decrementCounter(props.deviceId); // Decrement counter
      })
      .catch(error => {
        console.error(error);
      });
  }

  function deleteComment(id) {
    axios
      .delete(`/api/device/comments/${id}`)
      .then(response => {
        emit('commentsaved');
        getActiveComments();
        decrementCounter(props.deviceId); // Decrement counter
      })
      .catch(error => {
        console.error(error);
      });
  }

  function changeView() {
    activeCommentsView.value = !activeCommentsView.value;
    closedCommentsView.value = !closedCommentsView.value;

    if (activeCommentsView.value) {
      getActiveComments();
    }

    if (closedCommentsView.value) {
      getCloseComments();
    }
  }

  return {
    changeView,
    addComment,
    closeSheet,
    comments,
    formatters,
    isLoading,
    isSheetOpen,
    saveComment,
    viewDevice,
    closeComment,
    deleteComment,
    activeCommentsView,
    closedCommentsView,
    newCommentKey
  };
}
