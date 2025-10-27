<script setup> 
 import { computed } from "vue";

const props = defineProps({
  width: {
    type: Number,
    default: 16,
  },
  height: {
    type: Number,
    default: 16,
  },
  class: {
    type: String,
    default: "",
  },
});

// Combine default class with incoming class
const combinedClass = computed(() => {
  return `  ${props.class}`;
});
</script>

<template>
   <svg 
   :width="width"
   :height="height"
   :class="combinedClass"
   xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path fill="none" stroke="#91d7e3" stroke-linejoin="round" d="M4.289 1.5L14.5 4.245L11.777 14.5L1.5 11.711Z" stroke-width="1"/><path fill="none" stroke="#cad3f5" stroke-linejoin="round" d="m9.2 5l-.7 2.8l2.8.7l.7-2.8z" stroke-width="1"/></svg>
</template>