<script setup>
import { computed } from "vue";

const props = defineProps({
	width: {
		type: Number,
		default: 16,
	},
	height: {
		type: Number,
		default: 16,
	},
	class: {
		type: String,
		default: "",
	},
});

// Combine default class with incoming class
const combinedClass = computed(() => {
	return ` ${props.class}`;
});
</script>

<template>
	<svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" :class="combinedClass" viewBox="0 0 16 16"><path fill="none" stroke="#f5a97f" stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5a1 1 0 1 0 0-2a1 1 0 0 0 0 2m-11-2h7a1 1 0 0 1 0 2h-7a1 1 0 1 1 0-2m11 12a1 1 0 1 0 0-2a1 1 0 0 0 0 2m-11-2h7a1 1 0 0 1 0 2h-7a1 1 0 0 1 0-2m0-3a1 1 0 1 0 0-2a1 1 0 0 0 0 2m4-2h7a1 1 0 0 1 0 2h-7a1 1 0 1 1 0-2" stroke-width="1" /></svg>
</template>
