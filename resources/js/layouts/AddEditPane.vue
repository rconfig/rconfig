<script setup>
import { ref } from 'vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';

const emit = defineEmits(['close']);
defineProps({
  editId: Number,
  name: String
});

function close() {
  emit('close');
}
</script>

<template>
  <div
    class="w-screen h-[calc(100vh-72px)] border"
    style="display: flex; flex-direction: column; background-color: rgb(27, 29, 33); border-radius: 16px; margin: 4px 8px 8px; max-width: calc(100% - 16px); overflow: hidden">
    <div class="flex justify-between p-2 border-b">
      <Button
        @click="close()"
        size="sm"
        variant="outline"
        class="gap-1 border-none hover:bg-rcgray-800">
        <Icon
          icon="mingcute:close-line"
          class="hover:animate-pulse" />
      </Button>
      <h2 class="items-center content-center text-muted-foreground">{{ editId === 0 ? 'Add' : 'Edit' }} {{ name }} {{ editId === 0 ? '' : '(' + editId + ')' }}</h2>

      <div class="flex justify-end">
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="ghost">
              <Icon
                icon="radix-icons:dots-vertical"
                class="" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end">
            <DropdownMenuItem>
              <Icon
                icon="mdi:content-copy"
                class="mr-2" />
              Copy Record ID
            </DropdownMenuItem>
            <!-- <DropdownMenuItem>
            <Icon
              icon="mdi:star-outline"
              class="mr-2" />
            Add to Favorites
          </DropdownMenuItem> -->
            <DropdownMenuSeparator />
            <DropdownMenuItem class="text-red-500">
              <Icon
                icon="mdi:trash-can-outline"
                class="mr-2" />
              Delete Record
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
    </div>
    <slot name="default"></slot>
  </div>
</template>
