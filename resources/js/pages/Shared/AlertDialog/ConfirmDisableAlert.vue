<script setup>
import { onUnmounted, onMounted } from 'vue';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
const emits = defineEmits(['handleDisable', 'close']);

defineProps({
  ids: {
    type: Array, // singles should be convert to arrays before passing
    required: true
  },
  showConfirmDisable: {
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

function handleDisable() {
  emits('handleDisable');
}
function handleClose() {
  emits('close');
}
</script>

<template>
  <AlertDialog :open="showConfirmDisable">
    <!-- <AlertDialogTrigger>Open</AlertDialogTrigger> -->
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>Disable device with ID {{ ids }}?</AlertDialogTitle>
        <AlertDialogDescription>Are you absolutely sure you want to disable this device? Disabling a device stops it from downloading configs via scheduled tasks and other operations.</AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel @cancel="handleClose()">Cancel</AlertDialogCancel>
        <AlertDialogAction @action="handleDisable()">Continue</AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
