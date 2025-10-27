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
	<svg xmlns="http://www.w3.org/2000/svg" :width="props.width" :height="props.height" viewBox="0 0 3 2">
		<rect width="3" height="2" fill="#009246" />
		<rect width="2" height="2" x="1" fill="#fff" />
		<rect width="1" height="2" x="2" fill="#ce2b37" />
	</svg>
</template>
