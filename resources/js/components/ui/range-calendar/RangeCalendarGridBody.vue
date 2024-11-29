<script setup>
import { RangeCalendarGridBody } from 'radix-vue';

const props = defineProps({
  asChild: { type: Boolean, required: false },
  as: { type: null, required: false },
});
</script>

<template>
  <RangeCalendarGridBody v-bind="props">
    <slot />
  </RangeCalendarGridBody>
</template>
