<script setup>
import { computed } from "vue";

const props = defineProps({
	state: {
		type: Boolean,
		default: true,
	},
	color: {
		type: String,
		default: "white",
	},
	fillColor: {
		type: String,
		default: "white",
	},
	size: {
		type: Number,
		default: 16,
	},
	speed: {
		type: String,
		default: "normal", // slow, normal, fast
	},
	pulse: {
		type: Boolean,
		default: false,
	},
});

// Compute animation speed class
const animationSpeedClass = computed(() => {
	switch (props.speed) {
		case "slow":
			return "animate-duration-2000";
		case "fast":
			return "animate-duration-500";
		default:
			return "animate-duration-1000";
	}
});

// Compute size class (width and height)
const sizeClass = computed(() => {
	return `w-[${props.size}px] h-[${props.size}px]`;
});

// Create combined classes for spinner
const spinnerClass = computed(() => {
	return ["inline-block", sizeClass.value, `spinner-${props.pulse ? "pulse" : "spin"}`, animationSpeedClass.value];
});

// Create color classes
const ringColorClass = computed(() => `ring-color-${props.color}`);
const fillColorClass = computed(() => `fill-color-${props.fillColor}`);
</script>

<style scoped>
@keyframes spin {
	0% {
		transform: rotate(0deg);
	}
	100% {
		transform: rotate(360deg);
	}
}

@keyframes pulse-glow {
	0%,
	100% {
		filter: drop-shadow(0 0 2px rgba(147, 197, 253, 0));
		transform: scale(1) rotate(0deg);
	}
	50% {
		filter: drop-shadow(0 0 5px rgba(147, 197, 253, 0.5));
		transform: scale(1.05) rotate(180deg);
	}
}

.spinner-spin {
	animation: spin 1s linear infinite;
}

.spinner-pulse {
	animation: pulse-glow 1.5s ease-in-out infinite;
}

.animate-duration-500 {
	animation-duration: 500ms;
}

.animate-duration-1000 {
	animation-duration: 1000ms;
}

.animate-duration-2000 {
	animation-duration: 2000ms;
}

/* Color classes */
.ring-color-white {
	color: rgba(231, 226, 226, 0.2);
}
.ring-color-gray {
	color: rgba(156, 163, 175, 0.2);
}
.ring-color-blue {
	color: rgba(96, 165, 250, 0.2);
}
.ring-color-red {
	color: rgba(248, 113, 113, 0.2);
}
.ring-color-green {
	color: rgba(74, 222, 128, 0.2);
}
.ring-color-yellow {
	color: rgba(250, 204, 21, 0.2);
}

.fill-color-blue-600 {
	fill: #2563eb;
}
.fill-color-red-600 {
	fill: #dc2626;
}
.fill-color-green-600 {
	fill: #16a34a;
}
.fill-color-yellow-600 {
	fill: #ca8a04;
}
.fill-color-white {
	fill: #ffffff;
}
</style>

<template>
	<svg v-if="state" aria-hidden="true" :class="[spinnerClass, ringColorClass]" :style="{ width: `${size}px`, height: `${size}px` }" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg" role="status">
		<path class="spinner-ring" d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor" />
		<path
			class="spinner-loader"
			:class="fillColorClass"
			d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
		/>
	</svg>
</template>
