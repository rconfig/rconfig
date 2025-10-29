<script setup>
import { cn } from '@/lib/utils';
import { StepperRoot, useForwardPropsEmits } from 'reka-ui';

import { computed } from 'vue';

const props = defineProps({
  defaultValue: { type: Number, required: false },
  orientation: { type: String, required: false },
  dir: { type: String, required: false },
  modelValue: { type: Number, required: false },
  linear: { type: Boolean, required: false },
  asChild: { type: Boolean, required: false },
  as: { type: null, required: false },
  class: { type: null, required: false },
});
const emits = defineEmits(['update:modelValue']);

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props;

  return delegated;
});

const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
  <StepperRoot
    v-slot="slotProps"
    :class="cn('flex gap-2', props.class)"
    v-bind="forwarded"
  >
    <slot v-bind="slotProps" />
  </StepperRoot>
</template>
