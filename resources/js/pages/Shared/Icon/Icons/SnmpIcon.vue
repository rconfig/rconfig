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

const combinedClass = computed(() => {
	return `sort-asc-icon ${props.class}`;
});
</script>

<template>
	<svg xmlns="http://www.w3.org/2000/svg" :class="combinedClass" :width="width" :height="height" viewBox="0 0 16 16"><path fill="none" stroke="#a6da95" stroke-linecap="round" stroke-linejoin="round" d="M8 15.5L15.5 8V4L12 .5l-4 4l-4-4L.5 4v4zM.5 4L8 11.5L15.5 4M8 15.5v-4m3.5.5V8L8 4.5L4.5 8v4" stroke-width="1" /></svg>
</template>
