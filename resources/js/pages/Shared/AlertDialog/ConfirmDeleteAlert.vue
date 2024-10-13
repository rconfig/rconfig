<script setup>
import { onUnmounted, onMounted } from 'vue';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
const emits = defineEmits(['handleDelete', 'close']);

defineProps({
  ids: {
    type: Array, // singles should be convert to arrays before passing
    required: true
  },
  showConfirmDelete: {
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

function handleDelete() {
  emits('handleDelete');
}
function handleClose() {
  emits('close');
}
</script>

<template>
  <AlertDialog :open="showConfirmDelete">
    <!-- <AlertDialogTrigger>Open</AlertDialogTrigger> -->
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
        <AlertDialogDescription>This action cannot be undone. This will permanently delete the selected data (ID: {{ ids }}).</AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel @cancel="handleClose()">Cancel</AlertDialogCancel>
        <AlertDialogAction @action="handleDelete()">Continue</AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
