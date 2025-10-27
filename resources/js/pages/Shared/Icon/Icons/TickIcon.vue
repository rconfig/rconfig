<script setup>
import { ref } from "vue";

const props = defineProps({
	width: {
		type: Number,
		default: 48,
	},
	height: {
		type: Number,
		default: 48,
	},
	color: {
		type: String,
		default: "currentColor",
	},
});
</script>

<template>
	<svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 24 24" :class="color"><path :fill="color" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4 12l6 6L20 6" /></svg>
</template>
