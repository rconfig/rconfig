<script setup>
import { onUnmounted, onMounted } from 'vue';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
const emits = defineEmits(['handleConfirm', 'handleClose']);

const props = defineProps({
  showConfirmConfirmProceedAlertAlert: {
    type: Boolean,
    required: true
  },
  message: {
    type: String,
    default: 'Are you sure you want to proceed?'
  },
  editId: {
    type: Number,
    default: null
  }
});

// add onMounted to close on ESC or confirm on Spacebar
function handleKeyDown(e) {
  if (e.key === 'Escape') {
    handleConfirmProceedAlert();
  } else if (e.key === ' ') {
    handleConfirm();
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);

  // esc key
  window.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      handleClose();
    }
  });
});

// Cleanup event listener on unmount
onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});

function handleConfirm() {
  console.log('handleConfirm', props.editId);
  emits('handleConfirm', props.editId);
}
function handleConfirmProceedAlert() {
  emits('handleConfirmProceedAlert');
}
function handleClose() {
  emits('handleClose');
}
</script>

<template>
  <AlertDialog :open="showConfirmConfirmProceedAlertAlert">
    <!-- <AlertDialogTrigger>Open</AlertDialogTrigger> -->
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>Are you sure you want to proceed?</AlertDialogTitle>
        <AlertDialogDescription>
          {{ message }}
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel
          class="h-8 px-2 py-0 text-sm hover:bg-gray-700 hover:animate-pulse"
          @cancel="handleClose()">
          Cancel
        </AlertDialogCancel>
        <AlertDialogAction
          class="h-8 px-2 py-0 text-sm hover:bg-gray-700 hover:animate-pulse"
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
