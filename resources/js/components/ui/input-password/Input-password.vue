<script setup lang="ts">
import { ref } from 'vue';
import type { HTMLAttributes } from 'vue';
import { useVModel } from '@vueuse/core';
import { cn } from '@/lib/utils';

const props = defineProps<{
  defaultValue?: string | number;
  modelValue?: string | number;
  class?: HTMLAttributes['class'];
  mainDivClass?: HTMLAttributes['mainclass'];
  id?: string;
}>();

const emits = defineEmits<{
  (e: 'update:modelValue', payload: string | number): void;
}>();

const modelValue = useVModel(props, 'modelValue', emits, {
  passive: true,
  defaultValue: props.defaultValue
});
const showPassword = ref(false);
</script>

<template>
  <div :class="cn('relative', props.mainDivClass)">
    <input
      :id="props.id"
      v-model="modelValue"
      :type="showPassword ? 'text' : 'password'"
      autocomplete="off"
      :class="cn('flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 focus:outline-none focus-visible:ring-0', props.class, { 'text-gray-500': !showPassword })" />
    <button
      tabindex="-1"
      @click="showPassword = !showPassword"
      type="button"
      :class="cn('absolute inset-y-0 z-20 flex items-center px-3 cursor-pointer end-0 rounded-e-md focus:outline-none', showPassword ? 'text-blue-600 dark:text-blue-500' : 'text-gray-400 dark:text-neutral-600')">
      <Icon
        v-if="showPassword"
        icon="lucide:eye"
        class="size-3.5 transition-transform duration-200 ease-in-out" />
      <Icon
        v-else
        icon="lucide:eye-off"
        class="size-3.5 transition-transform duration-200 ease-in-out" />
    </button>
  </div>
</template>
