<script setup>
import { ref, onMounted, onBeforeUnmount, computed, watch } from "vue";

const props = defineProps({
	message: {
		type: String,
		default: "Information alert! Please review this information.",
	},
	heading: {
		type: String,
		default: "Info!",
	},
	showDuration: {
		type: Number,
		default: 3000,
	},
	delay: {
		type: Number,
		default: 0,
	},
	bgColor: {
		type: String,
		default: "bg-blue-50 dark:bg-gray-800",
	},
	textColor: {
		type: String,
		default: "text-blue-800 dark:text-blue-300",
	},
	useGradient: {
		type: Boolean,
		default: false,
	},
	gradientFrom: {
		type: String,
		default: "from-blue-50", // Light mode start color
	},
	gradientTo: {
		type: String,
		default: "to-transparent",
	},
	darkGradientFrom: {
		type: String,
		default: "dark:from-blue-900/50", // Dark mode start color
	},
	show: {
		type: Boolean,
		default: false,
	},
	autoShow: {
		type: Boolean,
		default: false, // New prop to control auto-showing behavior
	},
});

const backgroundClasses = computed(() => {
	if (props.useGradient) {
		return ["bg-gradient-to-r", props.gradientFrom, props.gradientTo, props.darkGradientFrom];
	}
	return props.bgColor;
});

// Use an internal reactive state that we control
const isVisible = ref(props.show);
let hideTimeout = null;

// Watch for changes in the show prop
watch(
	() => props.show,
	(newVal) => {
		isVisible.value = newVal;

		// If we're showing and have a duration, set up auto-hide
		if (newVal && props.showDuration > 0) {
			clearTimeout(hideTimeout);
			hideTimeout = setTimeout(() => {
				isVisible.value = false;
			}, props.showDuration);
		}
	},
	{ immediate: true }
);

onMounted(() => {
	// Only auto-show if specified
	if (props.autoShow && !isVisible.value) {
		setTimeout(() => {
			isVisible.value = true;

			// Set up auto-hide if needed
			if (props.showDuration > 0) {
				hideTimeout = setTimeout(() => {
					isVisible.value = false;
				}, props.showDuration);
			}
		}, props.delay);
	}
});

onBeforeUnmount(() => {
	clearTimeout(hideTimeout);
});
</script>

<template>
	<transition name="fade" mode="out-in">
		<div v-if="isVisible" :class="['p-4 mb-4 text-sm rounded-lg', backgroundClasses, textColor]" role="alert">
			<span class="font-medium">{{ heading }}</span> {{ message }}
		</div>
	</transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
	transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
	opacity: 0;
}
</style>
