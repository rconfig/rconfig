<script setup>
import { computed, inject } from "vue";

const colorMode = inject("colorMode");

const props = defineProps({
	variant: {
		type: String,
		default: "default",
		validator: (value) => ["default", "primary", "secondary", "success", "warning", "danger", "info", "outline", "new", "updated"].includes(value),
	},
	size: {
		type: String,
		default: "default",
		validator: (value) => ["small", "default", "large"].includes(value),
	},
	mode: {
		type: String,
		default: "auto",
		validator: (value) => ["auto", "light", "dark"].includes(value),
	},
	interactive: {
		type: Boolean,
		default: true,
	},
});

// Determine if we should use dark mode reactively
const isDarkMode = computed(() => {
	if (props.mode === "dark") return true;
	if (props.mode === "light") return false;
	return colorMode.value === "dark";
});

// Compute classes based on variant, size and mode
const badgeClasses = computed(() => {
	const sizeClasses = {
		small: "text-xs px-1.5 py-0",
		default: "text-xs px-2.5 py-0.5",
		large: "text-sm px-3 py-1",
	};

	// Get mode-specific variant classes
	const getVariantClasses = (isDark) => {
		const baseClasses = "inline-flex items-center rounded-md font-medium ring-1 ring-inset transition-colors duration-200";

		// Generate hover classes based on variant
		const getHoverClasses = (variant) => {
			if (!props.interactive) return "";

			if (isDark) {
				const hoverClasses = {
					default: "hover:bg-gray-700/70",
					primary: "hover:bg-blue-700/70",
					secondary: "hover:bg-purple-700/70",
					success: "hover:bg-green-700/70",
					warning: "hover:bg-amber-700/70",
					danger: "hover:bg-red-700/70",
					info: "hover:bg-cyan-700/70",
					outline: "hover:bg-gray-600/50",
					new: "hover:bg-green-700/70",
					updated: "hover:bg-blue-700/70",
				};
				return hoverClasses[variant];
			} else {
				const hoverClasses = {
					default: "hover:bg-gray-200",
					primary: "hover:bg-blue-200/80",
					secondary: "hover:bg-purple-200/80",
					success: "hover:bg-green-200/80",
					warning: "hover:bg-amber-200/80",
					danger: "hover:bg-red-200/80",
					info: "hover:bg-cyan-200/80",
					outline: "hover:bg-gray-200",
					new: "hover:bg-green-200/80",
					updated: "hover:bg-blue-200/80",
				};
				return hoverClasses[variant];
			}
		};

		if (isDark) {
			// Dark mode variants
			const variantClasses = {
				default: "bg-gray-800/30 text-gray-300 ring-gray-700",
				primary: "bg-blue-900/30 text-blue-300 ring-blue-700",
				secondary: "bg-purple-900/30 text-purple-300 ring-purple-700",
				success: "bg-green-900/30 text-green-300 ring-green-700",
				warning: "bg-amber-900/30 text-amber-300 ring-amber-700",
				danger: "bg-red-900/30 text-red-300 ring-red-700",
				info: "bg-cyan-900/30 text-cyan-300 ring-cyan-700",
				outline: "bg-transparent text-gray-300 ring-gray-600",
				new: "bg-green-900/30 text-green-300 ring-green-700",
				updated: "bg-blue-900/30 text-blue-300 ring-blue-700",
			};
			const hoverClass = getHoverClasses(props.variant);
			return `${baseClasses} ${variantClasses[props.variant]} ${hoverClass}`;
		} else {
			// Light mode variants
			const variantClasses = {
				default: "bg-gray-100 text-gray-700 ring-gray-300",
				primary: "bg-blue-50 text-blue-700 ring-blue-300",
				secondary: "bg-purple-50 text-purple-700 ring-purple-300",
				success: "bg-green-50 text-green-700 ring-green-300",
				warning: "bg-amber-50 text-amber-700 ring-amber-300",
				danger: "bg-red-50 text-red-700 ring-red-300",
				info: "bg-cyan-50 text-cyan-700 ring-cyan-300",
				outline: "bg-transparent text-gray-700 ring-gray-300",
				new: "bg-green-50 text-green-700 ring-green-300",
				updated: "bg-blue-50 text-blue-700 ring-blue-300",
			};
			const hoverClass = getHoverClasses(props.variant);
			return `${baseClasses} ${variantClasses[props.variant]} ${hoverClass}`;
		}
	};

	// Combine all classes
	return `${sizeClasses[props.size]} ${getVariantClasses(isDarkMode.value)}`;
});
</script>

<template>
	<span :class="badgeClasses" :tabindex="interactive ? 0 : undefined" :role="interactive ? 'button' : undefined">
		<slot></slot>
	</span>
</template>
