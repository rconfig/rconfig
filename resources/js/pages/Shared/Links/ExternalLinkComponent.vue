<script setup>
import { ExternalLink, ArrowUpRight } from "lucide-vue-next";
import { computed } from "vue";
import * as LucideIcons from "lucide-vue-next";

const props = defineProps({
	/**
	 * External URL the link points to
	 */
	to: {
		type: String,
		required: true,
	},
	/**
	 * Link text content
	 */
	text: {
		type: String,
		required: true,
	},
	/**
	 * Visual style variant - "default" or "subtle"
	 */
	variant: {
		type: String,
		default: "default",
		validator: (value) => ["default", "subtle"].includes(value),
	},
	/**
	 * Whether to show animation
	 */
	active: {
		type: Boolean,
		default: false,
	},
	/**
	 * Optional Lucide icon name to prefix the text
	 */
	icon: {
		type: String,
		default: null,
	},
	/**
	 * Additional CSS classes
	 */
	class: {
		type: String,
		default: "",
	},
});

const emit = defineEmits(["click"]);

// Dynamically resolve the icon component from lucide-vue-next
const iconComponent = computed(() => {
	if (!props.icon) return null;

	try {
		// Convert kebab-case or snake_case to PascalCase for Lucide components
		const iconName = props.icon
			.split(/[-_]/)
			.map((word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
			.join("");

		// Get the icon from the lucide-vue-next exports
		const IconComponent = LucideIcons[iconName];

		if (!IconComponent) {
			console.warn(`Icon "${props.icon}" (${iconName}) not found in lucide-vue-next`);
			return null;
		}

		return IconComponent;
	} catch (error) {
		console.warn(`Error resolving icon "${props.icon}":`, error);
		return null;
	}
});

// Computed classes for the link based on variant
const linkClasses = computed(() => {
	// Base styles for all variants
	const baseClasses = "inline-flex items-center gap-1.5 text-sm transition-all duration-200";

	// Variant-specific styles
	const variantClasses = {
		default: "text-blue-400 hover:text-blue-500 hover:underline",
		subtle: "text-muted-foreground hover:text-foreground",
	};

	const activeClass = props.active ? "link-active" : "";

	// Combine all classes
	return [
		baseClasses,
		variantClasses[props.variant],
		props.class, // Add the external class
		activeClass,
	].join(" ");
});

// Icon animation class
const iconClasses = computed(() => {
	const baseClass = props.active ? "icon-pulse" : "group-hover:scale-110 transition-transform";
	return baseClass;
});
</script>

<template>
	<!-- External link with icon based on variant -->
	<a :href="to" target="_blank" rel="noopener noreferrer" :class="linkClasses" class="group" @click="emit('click', $event)">
		<!-- Optional prefix icon -->
		<component v-if="iconComponent" :is="iconComponent" :size="14" :class="iconClasses" />
		<span>{{ text }}</span>
		<ExternalLink v-if="variant === 'default'" :size="14" :class="iconClasses" />
		<ArrowUpRight v-else-if="variant === 'subtle'" :size="14" :class="iconClasses" />
	</a>
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
