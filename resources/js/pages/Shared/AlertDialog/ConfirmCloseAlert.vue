<script setup>
import { onUnmounted, onMounted } from 'vue';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
const emits = defineEmits(['handleConfirm', 'handleClose']);

defineProps({
  showConfirmCloseAlert: {
    type: Boolean,
    required: true
  }
});

// add onMounted to close on ESC or confirm on Spacebar
function handleKeyDown(e) {
  if (e.key === 'Escape') {
    handleClose();
  } else if (e.key === ' ') {
    handleConfirm();
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
});

// Cleanup event listener on unmount
onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});

function handleConfirm() {
  emits('handleConfirm');
}
function handleClose() {
  emits('handleClose');
}
</script>

<template>
  <AlertDialog :open="showConfirmCloseAlert">
    <!-- <AlertDialogTrigger>Open</AlertDialogTrigger> -->
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
        <AlertDialogDescription>Confirming will close the previous dialog box and result in unsaved changes.</AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel
          class="px-2 py-1 ml-2 text-sm hover:bg-gray-700 hover:animate-pulse"
          @cancel="handleClose()">
          Cancel
        </AlertDialogCancel>
        <AlertDialogAction
          class="px-2 py-1 ml-2 text-sm hover:bg-gray-700 hover:animate-pulse"
          @action="handleConfirm()">
          Continue
          <div class="pl-2 ml-auto">
            <kbd class="rc-kdb-class">SPC</kbd>
          </div>
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
