<script setup>
import Loading from '@/pages/Shared/Loaders/Loading.vue';
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
		class="flex items-center justify-center h-full mt-48"
	>
		<Loading :text="'Running comparison'" />
	</div>

	<CompareDiffEditorComponent
		v-if="!isLoadingComponent"
		:config-results-right="configResultsRight"
		:config-results-left="configResultsLeft"
		:left-selected-id="leftSelectedId"
		:right-selected-id="rightSelectedId"
	/>
</template>
