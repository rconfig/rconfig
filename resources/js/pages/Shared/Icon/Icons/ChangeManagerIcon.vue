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
	color: {
		type: String,
		default: "#179299", // default color
	},
});
</script>

<template>
	<svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 16 16">
		<g fill="none" :stroke="color" stroke-linecap="round" stroke-linejoin="round" stroke-width="1">
			<path d="M4 1.5h8A2.5 2.5 0 0 1 14.5 4v8a2.5 2.5 0 0 1-2.5 2.5H4A2.5 2.5 0 0 1 1.5 12V4A2.5 2.5 0 0 1 4 1.5" />
		</g>
		<text x="4" y="11" font-size="6" font-family="Arial, sans-serif" :fill="color">
			CM
		</text>
	</svg>
</template>
