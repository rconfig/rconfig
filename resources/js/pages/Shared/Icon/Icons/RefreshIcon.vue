<script setup>
import { ref, watch } from "vue";
import { RefreshCw } from "lucide-vue-next";

const isHovered = ref(false);

const props = defineProps({
	size: {
		type: [Number, String],
		default: 16,
	},
	color: {
		type: String,
		default: "currentColor",
	},
	animate: {
		type: Boolean,
		default: false,
	},
});

// Handle hover state
function handleMouseEnter() {
	isHovered.value = true;
}

function handleMouseLeave() {
	isHovered.value = false;
}

// Watch for changes to the animate prop
watch(
	() => props.animate,
	(newValue) => {
		if (newValue) {
			isHovered.value = true;
		} else {
			// Only set to false if not actually being hovered
			// You might want to track actual hover state separately
			isHovered.value = false;
		}
	},
	{ immediate: true }
);
</script>

<template>
	<div class="refresh-icon-wrapper" @mouseenter="handleMouseEnter" @mouseleave="handleMouseLeave">
		<RefreshCw :size="props.size" :color="props.color" :class="['transition-all duration-300 ease-in-out', isHovered || props.animate ? 'text-blue-300 animate-spin-slow' : '']" />
	</div>
</template>

<style scoped>
.refresh-icon-wrapper {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	cursor: pointer;
}

@keyframes spin-slow {
	0% {
		transform: rotate(0deg);
	}
	100% {
		transform: rotate(360deg);
	}
}

.animate-spin-slow {
	animation: spin-slow 2s linear infinite;
}
</style>
