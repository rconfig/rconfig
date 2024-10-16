<script setup>
import { ref, defineEmits } from 'vue';

const selectedValue = ref(null);
const emit = defineEmits(['itemChecked']);

const props = defineProps({
  model: Object
});

const handleCheck = value => {
  selectedValue.value = value;
  emit('itemChecked', value);
};
</script>

<template>
  <div>
    STEP5
    <h3 class="mb-5 text-lg font-medium text-gray-900 dark:text-white">Finalize Task</h3>
    <pre> {{ model }}</pre>
  </div>
</template>
