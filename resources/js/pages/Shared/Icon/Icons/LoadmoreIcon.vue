<script setup>
const props = defineProps({
	height: {
		type: Number,
		default: 24,
	},
	width: {
		type: Number,
		default: 24,
	},
});
</script>

<template>
	<svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
		<path d="M12 8v8m0 0l4-4m-4 4l-4-4" />
		<path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
	</svg>
</template>
