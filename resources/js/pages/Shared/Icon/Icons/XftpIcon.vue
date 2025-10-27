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
	return ` ${props.class}`;
});
</script>

<template>
	<svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" :class="combinedClass" viewBox="0 0 16 16">
		<g fill="none" stroke-linecap="round" stroke-linejoin="round">
			<path stroke="#cad3f5" d="m1.87 8l.7-2.74a1 1 0 0 1 .96-.76h10.94a1 1 0 0 1 .97 1.24L15 7.49M6 13.5H2A1.5 1.5 0 0 1 .5 12V3.5a1 1 0 0 1 1-1h5a1 1 0 0 1 1 1v1" stroke-width="1" />
			<path stroke="#f5bde6" d="m9.5 15l2-3l-2-3l-2 3zM14 9l1.5 3l-1.5 3m-2-6l1.5 3l-1.5 3" stroke-width="1" />
		</g>
	</svg>
</template>
