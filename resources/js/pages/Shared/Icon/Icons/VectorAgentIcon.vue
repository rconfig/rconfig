<script setup>
const props = defineProps({
	height: {
		type: [Number, String],
		default: 8,
	},
	width: {
		type: [Number, String],
		default: 8,
	},
});
</script>

<template>
	<svg xmlns="http://www.w3.org/2000/svg" :width="props.width" :height="props.height" viewBox="0 0 16 16">
		<g fill="none" stroke-linecap="round" stroke-linejoin="round">
			<path stroke="#91d7e3" d="m4.5 10.02l-3 1.73M11.47 10l3.03 1.75M8 4V.5" stroke-width="1" />
			<path stroke="#8aadf4" d="M11.5 10L8 12l-3.5-2V6L8 4l3.5 2z" stroke-width="1" />
			<path stroke="#91d7e3" d="M14.5 11.75L8 15.5l-6.5-3.75v-7.5L8 .5l6.5 3.75zm-13-7.5L8 8m6.5-3.75L8 8m0 0v7.5" stroke-width="1" />
		</g>
	</svg>
</template>
