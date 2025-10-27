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
		<g fill="none" stroke-linecap="round" stroke-linejoin="round">
			<path stroke="#f5a97f" d="M12.58 1.877a1.345 1.377 0 0 1-1.345 1.377A1.345 1.377 0 0 1 9.89 1.877A1.345 1.377 0 0 1 11.235.5a1.345 1.377 0 0 1 1.345 1.377m-7.619 10.87V5.709c0-2.114 1.672-3.827 3.734-3.827H9.89" stroke-width="1" />
			<path stroke="#ed8796" d="M6.305 14.13a1.345 1.377 0 0 1-1.345 1.377a1.345 1.377 0 0 1-1.345-1.377a1.345 1.377 0 0 1 1.345-1.377a1.345 1.377 0 0 1 1.345 1.377m6.273-7.038a1.345 1.377 0 0 1-1.345 1.377a1.345 1.377 0 0 1-1.345-1.377a1.345 1.377 0 0 1 1.345-1.377a1.345 1.377 0 0 1 1.345 1.377m-7.619 5.664v-1.837c0-2.114 1.672-3.827 3.734-3.827h1.195" stroke-width="1" />
		</g>
	</svg>
</template>
