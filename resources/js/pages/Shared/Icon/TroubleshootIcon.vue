<script setup>
import { ref } from 'vue';

const props = defineProps({
  class: {
    type: String,
    required: true
  }
});
</script>

<template>
  <Icon
    icon="ph:gps-fix-duotone"
    :class="class" />
</template>
