<script setup>
import { Progress } from '@/components/ui/progress';
import { ref, watchEffect, onMounted } from 'vue';

const props = defineProps({
  model: {
    type: Object
  }
});

const emit = defineEmits(['checkSuccess', 'errors']);
const progress = ref(50);
const progressCheckStatus = ref('checking');
const errors = ref(null);

onMounted(() => {
  validateModel();
});

// watchEffect(cleanupFn => {
//   const timer = setTimeout(() => (progress.value = 100), 500);
//   cleanupFn(() => clearTimeout(timer));
// });

const validateModel = async data => {
  errors.value = '';
  try {
    await axios.post('/api/tasks/validate-task', props.model);
    progressCheckStatus.value = 'success';
    watchEffect(cleanupFn => {
      const timer = setTimeout(() => (progress.value = 100), 500);
      cleanupFn(() => clearTimeout(timer));
    });
    emit('checkSuccess');
  } catch (e) {
    watchEffect(cleanupFn => {
      const timer = setTimeout(() => (progress.value = 20), 500);
      cleanupFn(() => clearTimeout(timer));
    });

    if (e.response.status === 422) {
      progressCheckStatus.value = 'error';
      errors.value = e.response.data.errors;
    } else {
      errors.status = e.response.status;
      errors.message = e.response.data.message;
    }
    emit('errors', e.response);
  }
};
</script>

<template>
  <h2 class="mb-0 text-base font-semibold text-gray-900 dark:text-white">Task Verification</h2>
  <span
    v-if="progressCheckStatus === 'error'"
    class="text-red-500">
    Status: {{ progressCheckStatus }}
  </span>
  <Progress
    v-model="progress"
    class="w-3/5" />
</template>
