<script setup>
import { RangeCalendarGridHead } from 'radix-vue';

const props = defineProps({
  asChild: { type: Boolean, required: false },
  as: { type: null, required: false },
});
</script>

<template>
  <RangeCalendarGridHead v-bind="props">
    <slot />
  </RangeCalendarGridHead>
</template>
