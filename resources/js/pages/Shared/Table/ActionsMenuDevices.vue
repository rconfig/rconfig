<script setup>
import ConfirmDeleteAlert from '@/pages/Shared/AlertDialog/ConfirmDeleteAlert.vue';
import ConfirmDisableAlert from '@/pages/Shared/AlertDialog/ConfirmDisableAlert.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuShortcut, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { ref } from 'vue';

const showConfirmDelete = ref(false);
const showConfirmDisable = ref(false);

const emits = defineEmits(['onEdit', 'onDelete', 'onDisable', 'onClone']);

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

function handleClone() {
  emits('onClone');
}

function showAlert() {
  showConfirmDelete.value = true;
}

function handleDelete() {
  emits('onDelete');
  showConfirmDelete.value = false;
}

function showDisableConfirm() {
  showConfirmDisable.value = true;
}

function handleDisable() {
  emits('onDisable');
  showConfirmDisable.value = false;
}
function handleEnable() {
  emits('onEnable');
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
        <DropdownMenuItem
          v-if="showEditBtn"
          class="cursor-pointer hover:bg-rcgray-800"
          @click="handleClone">
          <span>Clone</span>
          <DropdownMenuShortcut>
            <Icon icon="material-symbols:content-copy-outline" />
          </DropdownMenuShortcut>
        </DropdownMenuItem>
        <DropdownMenuSeparator v-if="showEditBtn" />
        <DropdownMenuItem
          v-if="rowData.status === 100"
          class="cursor-pointer hover:bg-rcgray-800"
          @click="handleEnable">
          <span>Enable</span>
          <DropdownMenuShortcut>
            <Icon icon="emojione-v1:up-arrow" />
          </DropdownMenuShortcut>
        </DropdownMenuItem>
        <DropdownMenuItem
          v-if="rowData.status != 100"
          class="cursor-pointer hover:bg-rcgray-800"
          @click="showDisableConfirm">
          <span>Disable</span>
          <DropdownMenuShortcut>
            <Icon icon="emojione-v1:down-arrow" />
          </DropdownMenuShortcut>
        </DropdownMenuItem>
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

    <ConfirmDisableAlert
      :ids="[rowData.id]"
      :showConfirmDisable="showConfirmDisable"
      @close="showConfirmDisable = false"
      @handleDisable="handleDisable" />

    <ConfirmDeleteAlert
      :ids="[rowData.id]"
      :showConfirmDelete="showConfirmDelete"
      @close="showConfirmDelete = false"
      @handleDelete="handleDelete" />
  </div>
</template>
