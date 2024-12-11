<script setup>
import Loading from '@/pages/Shared/Loading.vue';
import CompareDiffEditorComponent from './CompareDiffEditorComponent.vue';
import { useCompareResults } from './useCompareResults';

const props = defineProps({
  leftSelectedId: Array,
  rightSelectedId: Array
});

const { isLoadingComponent, configResultsLeft, configResultsRight, getConfigLeft, getConfigRight, getConfigFileContent } = useCompareResults(props);
</script>

<template>
  <div
    v-if="isLoadingComponent"
    class="flex items-center justify-center h-full mt-48">
    <Loading :text="'Running comparison'" />
  </div>

  <CompareDiffEditorComponent
    v-if="!isLoadingComponent"
    :configResultsRight="configResultsRight"
    :configResultsLeft="configResultsLeft"
    :leftSelectedId="leftSelectedId"
    :rightSelectedId="rightSelectedId" />
</template>
