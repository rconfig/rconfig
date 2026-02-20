<script setup>
import { computed, inject } from "vue";

const props = defineProps({
	category: {
		type: Object,
		default: () => null,
	},
	placeholder: {
		type: String,
		default: "Uncategorized",
	},
});

const colorMode = inject("colorMode");
const isDarkMode = computed(() => colorMode.value === "dark");

const badgeClasses = computed(() => {
	if (props.category?.badgeColor) {
		return props.category.badgeColor;
	}

	// Default fallback colors based on theme
	return isDarkMode.value ? "bg-gray-800 text-gray-300 border-gray-700" : "bg-gray-100 text-gray-700 border-gray-300";
});
</script>
<template>
	<div class="flex justify-start w-full">
		<span :class="badgeClasses" class="inline-flex items-center text-xs font-medium px-2.5 py-0.5 rounded-xl border transition-colors duration-200">
			<slot name="icon"></slot>
			{{ category?.categoryName || placeholder }}
		</span>
	</div>
</template>
