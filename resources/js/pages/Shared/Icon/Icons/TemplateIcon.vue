<script setup>
 
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
		default: "#f5bde6",
	},
});
</script>

<template>
<svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 16 16"><path fill="none" :stroke="color" stroke-linecap="round" stroke-linejoin="round" d="M2.5 1.5h3l3 4l3-4h3l-9 13h-3L7 8z" stroke-width="1"/></svg></template>