<script setup>
import { cn } from '@/lib/utils';
import { ProgressIndicator, ProgressRoot } from 'radix-vue';
import { computed } from 'vue';

const props = defineProps({
  modelValue: { type: [Number, null], required: false, default: 0 },
  max: { type: Number, required: false },
  getValueLabel: { type: Function, required: false },
  asChild: { type: Boolean, required: false },
  as: { type: null, required: false },
  class: { type: null, required: false }
});

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props;

  return delegated;
});
</script>

<template>
  <ProgressRoot
    v-bind="delegatedProps"
    :class="cn('relative h-2 w-full overflow-hidden rounded-full bg-primary/40', props.class)">
    <ProgressIndicator
      class="flex-1 w-full h-full transition-all"
      :class="{
        'bg-red-500': props.modelValue < 25,
        'bg-yellow-500': props.modelValue >= 25 && props.modelValue < 50,
        'bg-blue-500': props.modelValue >= 50 && props.modelValue < 75,
        'bg-green-500': props.modelValue >= 75 && props.modelValue < 100,
        'bg-green-500': props.modelValue === 100
      }"
      :style="`transform: translateX(-${100 - (props.modelValue ?? 0)}%);`" />
  </ProgressRoot>
</template>
