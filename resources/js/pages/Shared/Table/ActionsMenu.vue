<script setup lang="ts">
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuShortcut, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
import { Icon } from '@iconify/vue';

const showConfirmDelete = ref(false);
const emits = defineEmits(['onEdit', 'onDelete']);

defineProps({
  rowData: {
    type: Object,
    required: true
  }
});

function handleEdit() {
  emits('onEdit');
}

function showAlert() {
  showConfirmDelete.value = true;
}

function handleDelete() {
  emits('onDelete');
  showConfirmDelete.value = false;
}
</script>

<template>
  <div>
    <DropdownMenu>
      <DropdownMenuTrigger as-child>
        <Button
          variant="ghost"
          class="hover:animate-pulse">
          ...
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent
        class="w-56"
        align="end"
        side="bottom">
        <DropdownMenuItem
          class="cursor-pointer hover:bg-rcgray-800"
          @click="handleEdit">
          <span>Edit</span>
          <DropdownMenuShortcut>
            <Icon icon="fluent-color:text-edit-style-16" />
          </DropdownMenuShortcut>
        </DropdownMenuItem>
        <DropdownMenuSeparator />
        <DropdownMenuItem
          class="cursor-pointer hover:bg-rcgray-800"
          @click="showAlert">
          <span class="text-red-400">Delete</span>
          <DropdownMenuShortcut>
            <Icon icon="flat-color-icons:full-trash" />
          </DropdownMenuShortcut>
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>

    <AlertDialog :open="showConfirmDelete">
      <!-- <AlertDialogTrigger>Open</AlertDialogTrigger> -->
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
          <AlertDialogDescription>This action cannot be undone. This will permanently delete the selected data (ID: {{ rowData.id }}).</AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @cancel="showConfirmDelete = false">Cancel</AlertDialogCancel>
          <AlertDialogAction @action="handleDelete()">Continue</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </div>
</template>
