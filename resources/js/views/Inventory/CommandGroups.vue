<script setup>
import { ref } from 'vue';

const test = ref(false);

defineProps({
  title: {
    type: String,
    required: true
  }
});
</script>

<template></template>
