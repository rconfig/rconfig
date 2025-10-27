<script setup>
import { AlertTriangle, X } from "lucide-vue-next";

const emit = defineEmits(["closed"]);

defineProps({
	title: {
		type: String,
		default: "Warning",
	},
	titleEnabled: {
		type: Boolean,
		default: true,
	},
	message: {
		type: String,
		required: true,
	},
	small: {
		type: Boolean,
		default: false,
	},
	showClose: {
		type: Boolean,
		default: false,
	},
});

function handleClose() {
	emit("closed");
}
</script>

<template>
	<div class="alert-warning">
		<div class="alert-content">
			<div class="alert-icon">
				<AlertTriangle />
				<span class="alert-icon-pulse"></span>
			</div>
			<div class="alert-body" :class="{ 'text-size-sm': small }">
				<h5 v-if="titleEnabled">{{ title }}</h5>
				<div v-html="message" class="alert-message"></div>
				<div class="alert-slot">
					<slot></slot>
				</div>
			</div>
			<button v-if="showClose" @click="handleClose" class="alert-close-btn" aria-label="Close" type="button">
				<X />
				<span class="alert-close-ripple"></span>
			</button>
		</div>
	</div>
</template>

<style scoped>
.alert-warning {
	position: relative;
	border-left: 4px solid var(--color-warning-border);
	background: linear-gradient(to right, var(--color-warning-bg-start), var(--color-warning-bg-middle), var(--color-warning-bg-end));
	color: var(--color-warning-foreground);
	border-radius: var(--radius-md);
	/* box-shadow: var(--shadow-warning); */
	/* backdrop-blur: 2px; */
	overflow: hidden;
	transition: transform 0.2s ease-in-out, box-shadow 0.2s ease;
}

.alert-warning:hover {
	/* box-shadow: var(--shadow-warning-hover); */
	/* transform: translateY(-1px) scale(1.005); */
}

.alert-warning::before {
	content: "";
	position: absolute;
	inset: 0;
	background: linear-gradient(to right, var(--color-warning-before-start), transparent);
	pointer-events: none;
	animation: subtle-shift 8s ease-in-out infinite alternate;
}

@keyframes subtle-shift {
	0% {
		opacity: 0.6;
		transform: translateX(0);
	}
	100% {
		opacity: 0.8;
		transform: translateX(10px);
	}
}

.alert-content {
	display: flex;
	gap: 0.75rem;
	padding: 1rem;
	align-items: flex-start;
}

.alert-icon {
	position: relative;
	flex-shrink: 0;
	color: var(--color-warning-icon);
	background: var(--color-warning-icon-bg);
	padding: 0.375rem;
	border-radius: 9999px;
	box-shadow: var(--shadow-sm);
}

.alert-icon-pulse {
	position: absolute;
	inset: -3px;
	border: 2px solid var(--color-warning-icon-pulse-border);
	border-radius: 9999px;
	animation: pulse 2s infinite ease-out;
}

@keyframes pulse {
	0% {
		transform: scale(1);
		opacity: 0.7;
	}
	70% {
		transform: scale(1.15);
		opacity: 0;
	}
	100% {
		transform: scale(1);
		opacity: 0;
	}
}

.alert-icon svg {
	width: 1rem;
	height: 1rem;
	filter: drop-shadow(0 1px 1px var(--color-warning-shadow));
	animation: subtle-bounce 3s ease-in-out infinite;
}

@keyframes subtle-bounce {
	0%,
	100% {
		transform: translateY(0);
	}
	50% {
		transform: translateY(-1px);
	}
}

.alert-body {
	flex: 1;
}

.alert-body h5 {
	font-weight: 500;
	margin-bottom: 0.25rem;
	color: var(--color-warning-title);
	display: flex;
	gap: 0.375rem;
	align-items: center;
	position: relative;
}

.alert-body h5::after {
	content: "";
	background: linear-gradient(to right, var(--color-warning-title-line), transparent);
	height: 2px;
	width: 2rem;
	border-radius: 2px;
	margin-left: 2px;
}

.alert-message {
	color: var(--color-warning-text);
	line-height: 1.625;
	text-shadow: 0 1px 0 rgba(255, 255, 255, 0.8);
}

.dark .alert-message {
	text-shadow: none;
}

.alert-slot {
	margin-top: 0.5rem;
	padding-left: 0rem;
	padding-top: 0.125rem;
	padding-bottom: 0.125rem;
}

.alert-close-btn {
	position: relative;
	flex-shrink: 0;
	padding: 0.375rem;
	border-radius: 9999px;
	color: var(--color-warning-close-icon);
	background-color: var(--color-warning-close-bg);
	box-shadow: var(--shadow-sm);
	margin-top: 0.25rem;
	align-self: flex-start;
	overflow: hidden;
	transition: all 0.2s ease-in-out;
}

.alert-close-btn:hover {
	background-color: var(--color-warning-close-hover-bg);
	color: var(--color-warning-close-hover-icon);
	transform: rotate(90deg);
}

.alert-close-ripple {
	position: absolute;
	inset: 0;
	border-radius: 50%;
	background-color: var(--color-warning-close-ripple);
	transform-origin: center;
	transform: scale(0);
	opacity: 0;
}

.alert-close-btn:hover .alert-close-ripple {
	animation: ripple 0.6s ease-out forwards;
}

@keyframes ripple {
	0% {
		transform: scale(0);
		opacity: 1;
	}
	100% {
		transform: scale(3);
		opacity: 0;
	}
}

.alert-close-btn svg {
	width: 0.875rem;
	height: 0.875rem;
	filter: drop-shadow(0 1px 1px var(--color-warning-shadow));
}

.text-size-sm {
	font-size: 0.875rem;
	line-height: 1.25rem;
}
</style>
