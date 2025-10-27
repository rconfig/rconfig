<script setup>
import { Copy, CheckCircle } from "lucide-vue-next";
import { computed } from "vue";

const props = defineProps({
	isActive: {
		type: Boolean,
		default: false,
	},
	size: {
		type: Number,
		default: 16,
	},
	activeColor: {
		type: String,
		default: "text-green-500",
	},
	duration: {
		type: Number,
		default: 300,
	},
});

const iconSize = computed(() => {
	return `h-${props.size / 4} w-${props.size / 4}`;
});

const transitionStyle = computed(() => {
	return `transition-all duration-${props.duration} ease-in-out`;
});
</script>

<template>
	<div class="relative" :class="iconSize">
		<CheckCircle class="absolute top-0 left-0 transition-all ease-in-out" :class="[iconSize, transitionStyle, isActive ? `opacity-100 ${activeColor} scale-100` : 'opacity-0 scale-75']" />
		<Copy class="absolute top-0 left-0 transition-all ease-in-out" :class="[iconSize, transitionStyle, !isActive ? 'opacity-100 scale-100' : 'opacity-0 scale-75']" />
	</div>
</template>
