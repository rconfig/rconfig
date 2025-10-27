<script setup>
import { ExternalLink } from "lucide-vue-next";
import { computed } from "vue";

const props = defineProps({
	/**
	 * External URL the button links to
	 */
	to: {
		type: String,
		required: true,
	},
	/**
	 * Button text content
	 */
	text: {
		type: String,
		required: true,
	},
	/**
	 * Button variant - passed to ShadcnUI Button component
	 */
	variant: {
		type: String,
		default: "outline",
		validator: (value) => ["default", "destructive", "outline", "secondary", "ghost", "link"].includes(value),
	},
	/**
	 * Button size - passed to ShadcnUI Button component
	 */
	size: {
		type: String,
		default: "sm",
		validator: (value) => ["default", "sm", "lg", "icon"].includes(value),
	},
	/**
	 * Whether to show animation
	 */
	active: {
		type: Boolean,
		default: false,
	},
	/**
	 * Additional CSS classes
	 */
	class: {
		type: String,
		default: "inline-flex items-center gap-1 text-xs",
	},
});

const emit = defineEmits(["click"]);

// Open external link in new tab
function openExtLink(event) {
	event.preventDefault();
	window.open(props.to, "_blank", "noopener,noreferrer");
	emit("click", props.to);
}

// Icon animation class
const iconClasses = computed(() => {
	const baseClasses = "w-4 h-4 ml-1";
	const animationClass = props.active ? "icon-pulse" : "group-hover:scale-110 transition-transform";
	return `${baseClasses} ${animationClass}`;
});

// Combine the `class` prop with other button classes
const buttonClasses = computed(() => {
	return `${props.class} group`; // Add other fixed classes or modifiers as needed
});
</script>

<template>
	<!-- Button style external link -->
	<Button :variant="variant" :size="size" :class="buttonClasses" @click="openExtLink">
		{{ text }}
		<ExternalLink :class="iconClasses" />
	</Button>
</template>

<style scoped>
/* Animation keyframes */
.icon-pulse {
	animation: pulse 2s infinite ease-in-out;
}

@keyframes pulse {
	0%,
	100% {
		transform: scale(1);
	}
	50% {
		transform: scale(1.1);
	}
}

/* Hover effect */
.group:hover .group-hover\:scale-110 {
	transform: scale(1.1);
	transition-duration: 200ms;
}
</style>
