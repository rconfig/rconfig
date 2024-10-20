<script setup>
import ConfirmDeleteAlert from '@/pages/Shared/AlertDialog/ConfirmDeleteAlert.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuShortcut, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { ref } from 'vue';

const showConfirmDelete = ref(false);
const emits = defineEmits(['onEdit', 'onDelete']);

defineProps({
  rowData: {
    type: Object,
    required: true
  },
  showEditBtn: {
    type: Boolean,
    default: true
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
          v-if="showEditBtn"
          class="cursor-pointer hover:bg-rcgray-800"
          @click="handleEdit">
          <span>Edit</span>
          <DropdownMenuShortcut>
            <Icon icon="fluent-color:text-edit-style-16" />
          </DropdownMenuShortcut>
        </DropdownMenuItem>
        <DropdownMenuSeparator v-if="showEditBtn" />
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

    <ConfirmDeleteAlert
      :ids="[rowData.id]"
      :showConfirmDelete="showConfirmDelete"
      @close="showConfirmDelete = false"
      @handleDelete="handleDelete" />
  </div>
</template>
