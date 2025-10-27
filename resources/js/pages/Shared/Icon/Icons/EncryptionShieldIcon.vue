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
	<svg class="w-12 h-12 mr-8" xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 16 16">
		<g fill="none" stroke="#fff47a" stroke-linecap="round" stroke-linejoin="round">
			<path d="m8.01 1.5l5.728 1.774C15.25 11.154 9.813 14.5 7.984 14.5s-7.281-3.357-5.7-11.226zM8 8.5v2z" />
			<circle cx="8" cy="7" r="1.5" />
		</g>
	</svg>
</template>
