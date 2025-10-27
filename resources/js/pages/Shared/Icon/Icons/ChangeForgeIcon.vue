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
		default: "#179299", // default color
	},
});
</script>

<template>
	<svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 16 16">
		<path fill="none" stroke="#6f97df" stroke-linecap="round" stroke-linejoin="round" d="M1.5 9.604H8v3.21h3.25v-3.21h3.25V6.396H8v-3.21H4.75v3.21H1.5Z" stroke-width="1" />
		<path fill="none" stroke="#6f97df" stroke-linecap="round" stroke-linejoin="round" d="M4.75 9.604V6.396H8v3.208h3.25V6.396" stroke-width="1" />
	</svg>
</template>
