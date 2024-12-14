<script setup>
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
const emit = defineEmits(['resolveComment', 'deleteComment']);

const test = ref(false);

const props = defineProps({});

function resolveComment() {
  emit('resolveComment');
}

function deleteComment() {
  emit('deleteComment');
}
</script>

<template>
  <div
    data-visible="true"
    class="opacity-100 absolute top-1.5 right-1.5">
    <div
      class="flex flex-row items-center p-0.5 bg-rcgray-900 rounded-lg shadow-inner border"
      style="box-shadow: rgb(49, 51, 55) 0px 0px 0px 0px">
      <Button
        @click="resolveComment()"
        variant="ghost"
        aria-label="Resolve comment"
        title="Resolve comment"
        data-state="closed"
        class="flex items-center justify-center h-6 px-2 cursor-pointer">
        <div
          data-is-small-ghost="false"
          class="inline-flex items-center justify-center">
          <Icon
            icon="teenyicons:tick-circle-outline"
            class="text-gray-500 hover:text-green-500"></Icon>
        </div>
      </Button>
      <Button
        variant="ghost"
        aria-label="React"
        title="React"
        class="flex items-center justify-center h-6 px-2 cursor-pointer">
        <div
          data-is-small-ghost="false"
          class="inline-flex items-center justify-center">
          <Icon
            icon="fluent:emoji-add-24-regular"
            class="text-gray-500"></Icon>
        </div>
      </Button>
      <Button
        variant="ghost"
        aria-label="Reply to comment"
        title="Reply to comment"
        class="flex items-center justify-center h-6 px-2 cursor-pointer">
        <Icon
          icon="carbon:reply"
          class="text-gray-500"></Icon>
      </Button>
      <Button
        @click="deleteComment()"
        variant="ghost"
        aria-label="Delete"
        title="Delete"
        data-state="closed"
        class="h-6 px-2 cursor-pointer">
        <Icon
          icon="iconamoon:trash"
          class="text-red-500" />
      </Button>
    </div>
  </div>
</template>
