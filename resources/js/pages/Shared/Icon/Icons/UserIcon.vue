<script setup>
import { ref } from "vue";

const props = defineProps({
	height: {
		type: Number,
		default: 8,
	},
	width: {
		type: Number,
		default: 8,
	},
});
</script>

<template>
	<svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 24 24">
		<g fill="none" stroke="#90a2fe" stroke-linecap="round" stroke-width="1.5">
			<path d="M1 20v-1a7 7 0 0 1 7-7v0a7 7 0 0 1 7 7v1" />
			<path d="M13 14v0a5 5 0 0 1 5-5v0a5 5 0 0 1 5 5v.5" />
			<path stroke-linejoin="round" d="M8 12a4 4 0 1 0 0-8a4 4 0 0 0 0 8m10-3a3 3 0 1 0 0-6a3 3 0 0 0 0 6" />
		</g>
	</svg>
</template>
