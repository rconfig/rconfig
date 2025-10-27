<script setup>
import { ref } from "vue";

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
		default: "#8087a2", // Default muted gray color
	},
});
</script>

<template>
	<svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 16 16"><g fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="#cad3f5" d="m1.875 8l.686-2.743a1 1 0 0 1 .97-.757h10.938a1 1 0 0 1 .97 1.243l-.315 1.26M6 13.5H2.004A1.5 1.5 0 0 1 .5 12V3.5a1 1 0 0 1 1-1h5a1 1 0 0 1 1 1v1" stroke-width="1"/><path stroke="#eed49f" d="M8.5 9.5h7v2h-7zm2 0v2m-2 2h7v2h-7zm2 0v2" stroke-width="1"/></g></svg>
</template>
