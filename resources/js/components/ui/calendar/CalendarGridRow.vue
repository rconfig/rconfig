<script setup>
import { cn } from '@/lib/utils';
import { CalendarGridRow, useForwardProps } from 'radix-vue';
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
  <CalendarGridRow :class="cn('flex', props.class)" v-bind="forwardedProps">
    <slot />
  </CalendarGridRow>
</template>
