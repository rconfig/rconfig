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
	<svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 16 16"><g fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="#cad3f5" d="M4.5 4.5H12A1.5 1.5 0 0 1 13.5 6v.5m-7.5 7H2A1.5 1.5 0 0 1 .5 12V3.5a1 1 0 0 1 1-1h5a1 1 0 0 1 1 1v1" stroke-width="1"/><path stroke="#f5a97f" d="M12 15.337v-3.919L8.5 9.214m3.5 2.204l3.5-2.204M12 7.5l3.5 1.714v4.408L12 15.5l-3.5-1.878V9.214Z" stroke-width="1"/></g></svg>
	</template>
	
