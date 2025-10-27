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
	<svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" :class="combinedClass" viewBox="0 0 16 16"><path fill="none" stroke="#c6a0f6" stroke-linecap="round" stroke-linejoin="round" d="M9.024 12.85L9 10.505h6.5v2.35zM5.4 8.005L.5 3.155l3.433.005L8 7.155c.47.476.47 1.24 0 1.7l-4 4H.5z" stroke-width="1" /></svg>
</template>
