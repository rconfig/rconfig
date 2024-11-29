<script setup>
import { cn } from '@/lib/utils';
import { CalendarGrid, useForwardProps } from 'radix-vue';
import { computed } from 'vue';

const props = defineProps({
  asChild: { type: Boolean, required: false },
  as: { type: null, required: false },
  class: { type: null, required: false },
});

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props;

  return delegated;
});

const forwardedProps = useForwardProps(delegatedProps);
</script>

<template>
  <CalendarGrid
    :class="cn('w-full border-collapse space-y-1', props.class)"
    v-bind="forwardedProps"
  >
    <slot />
  </CalendarGrid>
</template>
