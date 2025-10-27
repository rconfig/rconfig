<script setup>
import { computed } from "vue";

const props = defineProps({
	size: {
		type: [Number, String],
		default: 24,
	},
	strokeWidth: {
		type: [Number, String],
		default: 2,
	},
	class: {
		type: String,
		default: "",
	},
});

// Parse Tailwind text color classes to extract the color value
const strokeColor = computed(() => {
	const classList = props.class.split(" ");

	// Look for text color classes (text-*)
	const textColorClass = classList.find((c) => c.startsWith("text-"));

	if (textColorClass) {
		// For Tailwind classes, we'll use currentColor which inherits from text color
		return "currentColor";
	}

	// Look for stroke color classes (stroke-*)
	const strokeColorClass = classList.find((c) => c.startsWith("stroke-"));

	if (strokeColorClass) {
		return "currentColor";
	}

	// Default fallback
	return "#8087a2";
});
</script>

<template>
	<svg xmlns="http://www.w3.org/2000/svg" :width="size" :height="size" viewBox="0 0 16 16" :class="class" fill="none">
		<path :stroke="strokeColor" stroke-linecap="round" stroke-linejoin="round" :stroke-width="strokeWidth" d="M12.36 7.104c.482 0 .872.39.872.872v5.23c0 .481-.39.871-.872.871H3.639a.87.87 0 0 1-.872-.871v-5.23c0-.482.39-.872.872-.872zm-6.977 0V4.488a2.617 2.617 0 0 1 5.234 0v2.616" />
	</svg>
</template>
