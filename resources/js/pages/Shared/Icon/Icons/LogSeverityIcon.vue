<script setup>
// -----------------------------------------------------------------------------
// LogSeverityIcon Component
// -----------------------------------------------------------------------------
// Purpose:  A standardized icon system for displaying log severity levels
//           throughout the application with consistent colors and styling.
//
// Usage:    <LogSeverityIcon
//             severity="info"        // Required: Severity level
//             size="md"              // Optional: Size (sm, md, lg)
//             showLabel={true}       // Optional: Show/hide text label
//             variant="default"      // Optional: Icon style variant
//           />
//
// Severities: - info: Information messages (blue)
//             - warn: Warning messages (amber/yellow)
//             - error: Error messages (red)
//             - debug: Debug messages (purple)
//             - critical: Critical errors (bright red)
//             - success: Success messages (green)
//
// -----------------------------------------------------------------------------

import { computed } from "vue";
import { InfoIcon, AlertTriangle, XOctagon, Bug, AlertCircle, CheckCircle } from "lucide-vue-next";

const props = defineProps({
	severity: {
		type: String,
		required: true,
		validator: (value) => ["info", "warn", "warning", "error", "debug", "critical", "success", "crit"].includes(value),
	},
	size: {
		type: String,
		default: "md",
		validator: (value) => ["sm", "md", "lg"].includes(value),
	},
	showLabel: {
		type: Boolean,
		default: false,
	},
	variant: {
		type: String,
		default: "default",
		validator: (value) => ["default", "outline", "filled"].includes(value),
	},
});

// Map severity to icon component
const iconComponent = computed(() => {
	const iconMap = {
		info: InfoIcon,
		warn: AlertTriangle,
		error: XOctagon,
		debug: Bug,
		critical: AlertCircle,
		success: CheckCircle,
	};

	return iconMap[props.severity] || InfoIcon;
});

// Map severity to color
const iconColor = computed(() => {
	const colorMap = {
		info: "text-blue-400",
		warn: "text-orange-300",
		warning: "text-orange-300",
		error: "text-rose-400",
		debug: "text-purple-400",
		critical: "text-rose-600",
		crit: "text-rose-600",
		success: "text-green-400",
	};

	return colorMap[props.severity] || "text-gray-500";
});

// Determine icon size based on prop
const iconSize = computed(() => {
	const sizeMap = {
		sm: "h-4 w-4",
		md: "h-5 w-5",
		lg: "h-6 w-6",
	};

	return sizeMap[props.size] || "h-5 w-5";
});

// Generate variant classes
const variantClasses = computed(() => {
	if (props.variant === "outline") {
		return "p-1 border rounded-full";
	} else if (props.variant === "filled") {
		const bgColorMap = {
			info: "bg-blue-100",
			warn: "bg-amber-100",
			error: "bg-red-100",
			debug: "bg-purple-100",
			critical: "bg-rose-100",
			crit: "bg-rose-100",
			success: "bg-green-100",
		};
		return `p-1 ${bgColorMap[props.severity] || "bg-gray-100"} rounded-full`;
	}
	return "";
});

// Format severity label for display
const formattedLabel = computed(() => {
	return props.severity.charAt(0).toUpperCase() + props.severity.slice(1);
});
</script>

<template>
	<div class="flex items-center gap-1.5 mr-2">
		<div :class="[iconColor, variantClasses]">
			<component :is="iconComponent" :class="iconSize" />
		</div>
		<span v-if="showLabel" :class="iconColor" class="font-medium">
			{{ formattedLabel }}
		</span>
	</div>
</template>
