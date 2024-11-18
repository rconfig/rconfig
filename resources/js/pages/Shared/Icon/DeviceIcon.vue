<script setup>
import { ref } from 'vue';

const props = defineProps({
  class: {
    type: String
  }
});
</script>

<template>
  <Icon
    icon="fluent-color:org-16"
    :class="class" />
</template>
