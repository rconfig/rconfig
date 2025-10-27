<script setup>
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
</script>

<template>
	<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" :width="width" :height="height" :class="class">
		<g fill="none" stroke-linecap="round" stroke-linejoin="round">
			<path stroke="#cad3f5" d="m1.875 8l.686-2.743a1 1 0 0 1 .97-.757h10.938a1 1 0 0 1 .97 1.243l-.315 1.26M6 13.5H2.004A1.5 1.5 0 0 1 .5 12V3.5a1 1 0 0 1 1-1h5a1 1 0 0 1 1 1v1" />
			<path stroke="#f5a97f" d="M9.5 8.5h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1h-1a1 1 0 0 1-1-1v-1a1 1 0 0 1 1-1m4 4h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1h-1a1 1 0 0 1-1-1v-1a1 1 0 0 1 1-1m-4-1v3h3" />
		</g>
	</svg>
</template>
