<script setup>
import { computed } from "vue";

const props = defineProps({
	height: {
		type: Number,
		default: 16,
	},
	width: {
		type: Number,
		default: 16,
	},
	class: {
		type: String,
		default: "",
	},
	active: {
		type: Boolean,
		default: false,
	},
});

// Combine default class with incoming class
const combinedClass = computed(() => {
	return `notification-icon ${props.active ? "notification-active" : ""} ${props.class}`;
});
</script>

<style scoped>
@keyframes notification-pulse {
	0%,
	100% {
		transform: scale(1);
		filter: drop-shadow(0 0 0 rgba(238, 212, 159, 0));
		stroke: #eed49f;
	}
	50% {
		transform: scale(1.05); /* Reduced from 1.1 to minimize layout impact */
		filter: drop-shadow(0 0 3px rgba(238, 212, 159, 0.6));
		stroke: #f5a97f;
	}
}

@keyframes notification-shake {
	0%,
	100% {
		transform: translateX(0);
	}
	10%,
	30%,
	50%,
	70%,
	90% {
		transform: translateX(-1px);
	}
	20%,
	40%,
	60%,
	80% {
		transform: translateX(1px);
	}
}

.notification-icon {
	/* More specific transitions - avoid 'all' which can cause resize issues */
	transition: opacity 0.3s ease, filter 0.3s ease;
	/* Prevent layout shifts during animations */
	transform-origin: center;
	will-change: transform;
}

.notification-active .notification-bolt {
	animation: notification-pulse 1.5s infinite ease-in-out;
}

.notification-icon:hover .notification-bolt {
	animation: notification-shake 0.82s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
}
</style>

<template>
	<svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 16 16" :class="combinedClass" style="flex-shrink: 0; display: block;">
		<path class="notification-bolt" fill="none" stroke="#eed49f" stroke-linecap="round" stroke-linejoin="round" d="M2.85 9.301a.644.65 0 0 1-.502-1.06L8.72 1.605a.322.325 0 0 1 .554.3L8.039 5.82a.644.65 0 0 0 .605.878h4.506a.644.65 0 0 1 .502 1.06L7.28 14.395a.322.325 0 0 1-.554-.3l1.236-3.916a.644.65 0 0 0-.605-.878Z" stroke-width="1" />
	</svg>
</template>
