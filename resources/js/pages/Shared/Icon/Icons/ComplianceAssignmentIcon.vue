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
	<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
		<rect width="13" height="13" x="1.5" y="1.5" fill="none" stroke="#7dc4e4" rx=".722" ry=".722" stroke-width="1" />
		<path fill="none" stroke="#cad3f5" stroke-linecap="round" stroke-linejoin="round" d="M3.5 12.5h6v-9h3v6h-9v-3h9v-3h-6v9zv-3" stroke-width="1" />
	</svg>
</template>
