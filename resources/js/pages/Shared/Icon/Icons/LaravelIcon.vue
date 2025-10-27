<script setup>
defineProps({
	width: {
		type: [String, Number],
		default: 16,
	},
	height: {
		type: [String, Number],
		default: 16,
	},
});
</script>
<template>
	<svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 16 16">
		<g fill="none" stroke="#ed8796" stroke-linecap="round" stroke-linejoin="round" stroke-width="1">
			<path d="M12.51 5.49v3.29M9.64 3.89l2.86 1.6l2.74-1.53M6.5 12v3.5m-3-12l3-1.5" />
			<path d="m3.5 10.5l6-3.5V3.5L12.51 2l2.99 1.5V7l-3.06 1.5L9.5 7" />
			<path d="m.5 2l3-1.5l3 1.5v6.5" />
			<path d="M.5 2v10.17l6 3.33l6.02-3.41V8.5L6.5 12.04l-3-1.54v-7z" />
		</g>
	</svg>
</template>
