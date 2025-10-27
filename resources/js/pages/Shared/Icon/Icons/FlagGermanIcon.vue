<script setup>
import { ref } from "vue";

const props = defineProps({
	width: {
		type: Number,
		default: 24,
	},
	height: {
		type: Number,
		default: 24,
	},
	color: {
		type: String,
		default: "currentColor",
	},
});
</script>

<template>
	<svg xmlns="http://www.w3.org/2000/svg" :width="props.width" :height="props.height" viewBox="0 0 32 32">
		<rect x="1" y="4" width="30" height="24" rx="4" ry="4" fill="#000"></rect>
		<rect x="1" y="4" width="30" height="8" fill="#d00"></rect>
		<rect x="1" y="12" width="30" height="8" fill="#ffce00"></rect>
	</svg>
</template>
