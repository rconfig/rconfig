import { ref, onMounted, onUnmounted } from 'vue';

// this shoulw not really be edited, as most logic is in the parent component

export function useConfirmCloseAlert(editId, emit, isClone) {
  const showConfirmCloseAlert = ref(false);

  onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
  });

  onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
  });

  function handleKeyDown(event) {
    if (event.ctrlKey && event.key === 'Enter') {
      showConfirmCloseAlert.value = false;
    }
  }

  function showConfirmCloseDialog() {
    showConfirmCloseAlert.value = true;
  }

  function cancelCloseDialog() {
    showConfirmCloseAlert.value = false;
  }

  function confirmCloseDialog() {
    showConfirmCloseAlert.value = false;
  }

  return { showConfirmCloseAlert, showConfirmCloseDialog, cancelCloseDialog, confirmCloseDialog };
}
