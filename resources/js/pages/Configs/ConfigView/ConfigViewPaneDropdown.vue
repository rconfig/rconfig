<script setup>
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuShortcut, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useToaster } from '@/composables/useToaster'; // Import the composable
import { useClipboard } from '@vueuse/core';

const emits = defineEmits(['onDelete', 'onPurge']);
const { text, copy, copied, isSupported } = useClipboard();
const { toastSuccess } = useToaster();

const props = defineProps({
  editId: {
    type: Number,
    required: true
  }
});

function handleDelete() {
  emits('onDelete');
}

function onCopy() {
  copy(props.editId);
  toastSuccess('Success', 'Copied to clipboard');
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
          class="cursor-pointer hover:bg-rcgray-600"
          @click="onCopy()">
          <span>Copy ID</span>
          <DropdownMenuShortcut>
            <Icon icon="icon-park-twotone:delete-five" />
          </DropdownMenuShortcut>
        </DropdownMenuItem>

        <DropdownMenuSeparator />
        <DropdownMenuItem
          class="cursor-pointer hover:bg-rcgray-600"
          @click="handleDelete()">
          <span class="text-red-400">Delete</span>
          <DropdownMenuShortcut>
            <Icon icon="flat-color-icons:full-trash" />
          </DropdownMenuShortcut>
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  </div>
</template>
