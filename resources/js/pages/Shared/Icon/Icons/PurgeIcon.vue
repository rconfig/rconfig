<script setup>
import { ref } from "vue";
import { BrushCleaning } from "lucide-vue-next";

const isHovered = ref(false);

const props = defineProps({
	size: {
		type: [Number, String],
		default: 16,
	},
	isPinned: {
		type: Boolean,
		default: false,
	},
	class: {
		type: String,
		default: "text-blue-300",
	},
});

// Handle hover state
function handleMouseEnter() {
	isHovered.value = true;
}

function handleMouseLeave() {
	isHovered.value = false;
}

const emit = defineEmits(["click"]);

function handleClick() {
	emit("click");
}
</script>

<template>
	<div class="pin-icon-wrapper" @mouseenter="handleMouseEnter" @mouseleave="handleMouseLeave" @click="handleClick">
		<BrushCleaning
			:size="props.size"
			:class="[
				'transition-all duration-300 ease-in-out text-amber-500 ',
				{
					'pin-hover-animation': isHovered,
					'text-amber-500 fill-amber-500/20': isPinned,
					'text-amber-300': isHovered && !isPinned,
				},
				props.class,
			]"
		/>
	</div>
</template>

<style scoped>
.pin-icon-wrapper {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	cursor: pointer;
}

.pin-hover-animation {
	animation: pin-bounce 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

@keyframes pin-bounce {
	0% {
		transform: translateY(0) scale(1);
	}
	30% {
		transform: translateY(-4px) scale(1.2);
	}
	50% {
		transform: translateY(0) scale(0.95);
	}
	75% {
		transform: translateY(-2px) scale(1.05);
	}
	100% {
		transform: translateY(0) scale(1);
	}
}
</style>
