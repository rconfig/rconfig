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
});
</script>

<template>
	<svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 16 16"><path fill="none" stroke="#eed49f" stroke-linecap="round" stroke-linejoin="round" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14m.5-10.5h2L13 8l-2.5 3.5h-2L11 8zM4 4.5h2L8.5 8L6 11.5H4L6.5 8z" stroke-width="1" /></svg>
</template>
