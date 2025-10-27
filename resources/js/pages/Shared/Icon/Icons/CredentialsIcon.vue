<script setup>
import { computed } from "vue";

const props = defineProps({
	width: {
		type: Number,
		default: 24,
	},
	height: {
		type: Number,
		default: 24,
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
	<svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 16 16" :class="combinedClass">
		<g fill="none" fill-rule="evenodd">
			<path stroke="#8087a2" d="M14.998 11.5a.5.5 0 0 1 .5.5v2.999a.5.5 0 0 1-.5.5H10a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 1 .5-.5zM11 11.5V10a1.5 1.5 0 0 1 3 0v1.5" stroke-width="1" />
			<path stroke="#f5a97f" stroke-linecap="round" stroke-linejoin="round" d="m.5 6l6 2.996l6-3.498m-9-2.001l3 2.003L12.498 2M3.5 4v7m3-5.502V12.5" stroke-width="1" />
			<path stroke="#f5a97f" stroke-linecap="round" stroke-linejoin="round" d="M6.499 2.5L9.5 4v3" stroke-width="1" />
			<path stroke="#f5a97f" stroke-linecap="round" stroke-linejoin="round" d="M12.498 5.5V2L9.538.5L3.5 3.5v.997l-3 1.501v3.54l6 2.962" stroke-width="1" />
		</g>
	</svg>
</template>
