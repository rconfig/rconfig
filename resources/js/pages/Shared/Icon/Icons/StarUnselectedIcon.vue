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
	return `star-icon-unselected ${props.class}`;
});
</script>

<style scoped>
.star-icon-unselected {
	color: #8087a2; /* text-muted in Catppuccin */
}

.star-outline {
	fill: none;
	stroke: currentColor;
	stroke-width: 1;
}
</style>

<template>
	<svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 24 24" :class="combinedClass">
		<!-- Star outline with no fill -->
		<path class="star-outline" d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
	</svg>
</template>
