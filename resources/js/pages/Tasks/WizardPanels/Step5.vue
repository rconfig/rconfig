<script setup>
import { ref, defineEmits } from 'vue';

const selectedValue = ref(null);
const emit = defineEmits(['itemChecked']);

defineProps({});

const handleCheck = value => {
  selectedValue.value = value;
  emit('itemChecked', value);
};
</script>

<template>
  <div>
    STEP5
    <h3 class="mb-5 text-lg font-medium text-gray-900 dark:text-white">Select a task type</h3>
  </div>
</template>
