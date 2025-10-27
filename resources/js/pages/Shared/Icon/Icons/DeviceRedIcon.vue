<script setup>
const props = defineProps({
	height: {
		type: Number,
		default: 16,
	},
	width: {
		type: Number,
		default: 16,
	},
});
</script>

<template>
	<svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 16 16">
		<g fill="none" stroke-linecap="round" stroke-linejoin="round">
			<path stroke="#ef4444" d="M10.5 5a2.5 2.5 0 1 1-5 0a2.5 2.5 0 0 1 5 0" stroke-width="1" />
			<path stroke="#f87171" d="M6.5 11a2.5 2.5 0 1 1-5 0a2.5 2.5 0 0 1 5 0" stroke-width="1" />
			<path stroke="#dc2626" d="M14.5 11a2.5 2.5 0 1 1-5 0a2.5 2.5 0 0 1 5 0" stroke-width="1" />
		</g>
	</svg>
</template>
