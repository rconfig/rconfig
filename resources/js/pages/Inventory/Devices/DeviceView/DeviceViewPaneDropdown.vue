<script setup>
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuShortcut, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { ref } from 'vue';

const showConfirmDelete = ref(false);
const emits = defineEmits(['openDeviceEdit', 'onDelete']);

defineProps({
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

function openDeviceEdit() {
  emits('openDeviceEdit');
}
</script>

<template>
  <div>
    <DropdownMenu>
      <DropdownMenuTrigger as-child>
        <Button
          variant="ghost"
          class="h-8 ml-2">
          <Icon
            icon="radix-icons:dots-vertical"
            class="" />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent
        class="w-56"
        align="end"
        side="bottom">
        <DropdownMenuItem
          v-if="showEditBtn"
          class="cursor-pointer hover:bg-rcgray-600"
          @click="openDeviceEdit()">
          <span>Edit Device</span>
          <DropdownMenuShortcut>
            <Icon icon="fluent-color:text-edit-style-16" />
          </DropdownMenuShortcut>
        </DropdownMenuItem>
        <DropdownMenuItem
          v-if="showEditBtn"
          class="cursor-pointer hover:bg-rcgray-600">
          <span>View Configs</span>
          <DropdownMenuShortcut>
            <Icon icon="fluent-mdl2:configuration-solid" />
          </DropdownMenuShortcut>
        </DropdownMenuItem>

        <DropdownMenuItem
          v-if="showEditBtn"
          class="cursor-pointer hover:bg-rcgray-600">
          <span>Clone Device</span>
          <DropdownMenuShortcut>
            <Icon icon="fa6-solid:clone" />
          </DropdownMenuShortcut>
        </DropdownMenuItem>
        <DropdownMenuItem
          v-if="showEditBtn"
          class="cursor-pointer hover:bg-rcgray-600">
          <span>Purge Failed Configs</span>
          <DropdownMenuShortcut>
            <Icon icon="icon-park-twotone:delete-five" />
          </DropdownMenuShortcut>
        </DropdownMenuItem>

        <DropdownMenuSeparator v-if="showEditBtn" />
        <DropdownMenuItem
          class="cursor-pointer hover:bg-rcgray-600"
          @click="showAlert">
          <span class="text-red-400">Delete</span>
          <DropdownMenuShortcut>
            <Icon icon="flat-color-icons:full-trash" />
          </DropdownMenuShortcut>
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  </div>
</template>
