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

// add onMOunted to close on ESC
onMounted(() => {
  window.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      handleClose();
    }
  });
});

// Cleanup event listener on unmount
onUnmounted(() => {
  window.removeEventListener('keydown', e => {
    if (e.key === 'Escape') {
      handleClose();
    }
  });
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
        <AlertDialogCancel @cancel="handleClose()">Cancel</AlertDialogCancel>
        <AlertDialogAction @action="handleConfirm()">Continue</AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
