<script setup>
import { ref } from 'vue';

// Import your components here
// import StepActivePanelItem from './StepActivePanelItem.vue';
// import StepCompletePanelItem from './StepCompletePanelItem.vue';
// import StepTodoPanelItem from './StepTodoPanelItem.vue';

const props = defineProps({
  currentStep: {
    type: Number,
    required: true,
    default: 1
  }
});

function getStepLabel(step) {
  const labels = ['Task Type', 'Task Info', 'Task Configuration', 'Task Schedule', 'Task Finalize'];
  return labels[step - 1];
}
</script>

<template>
  <ol class="space-y-8 overflow-hidden">
    <li
      v-for="step in 5"
      :key="step"
      :class="{
        'relative flex-1': true,
        'after:w-0.5 after:h-full after:bg-blue-600 after:inline-block after:absolute after:-bottom-7 lg:after:-bottom-8 after:left-3 lg:after:left-4': step < currentStep,
        'after:w-0.5 after:h-full after:bg-gray-200 after:inline-block after:absolute after:-bottom-7 lg:after:-bottom-8 after:left-3 lg:after:left-4': step >= currentStep
      }">
      <a class="flex items-center w-full font-medium cursor-default">
        <span
          class="flex items-center justify-center w-6 h-6 mr-3 text-sm border-2 rounded-full lg:w-8 lg:h-8"
          :class="{
            'text-white bg-blue-600 border-transparent': step < currentStep,
            'text-blue-600 border-blue-600 bg-blue-50': step === currentStep,
            'text-gray-600 border-gray-600 bg-gray-50': step > currentStep
          }">
          <Icon
            v-if="step < currentStep"
            icon="teenyicons:tick-outline"
            class="w-5 h-5 stroke-white" />
          <span v-else>{{ step }}</span>
        </span>
        <div class="block">
          <span class="text-sm">
            {{ getStepLabel(step) }}
          </span>
        </div>
      </a>
    </li>
  </ol>
</template>
