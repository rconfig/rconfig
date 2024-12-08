<script setup>
import { ref } from 'vue';
import { useCompareResults } from './useCompareResults';
import Loading from '@/pages/Shared/Loading.vue';

const props = defineProps({
  leftSelectedId: String,
  rightSelectedId: String
});

const { configResultsLeft, configResultsRight, isLoadingComponent } = useCompareResults(props);
</script>

<template>
  <div
    v-if="isLoadingComponent"
    class="flex items-center justify-center h-full mt-48">
    <Loading :text="'Running comparison'" />
  </div>
  <div
    class="flex items-center justify-center h-full"
    v-if="!isLoadingComponent">
    <pre style="white-space: pre-wrap"> {{ configResultsLeft }}</pre>
    <pre style="white-space: pre-wrap"> {{ configResultsRight }}</pre>
  </div>
</template>
