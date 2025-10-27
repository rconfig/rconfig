<script setup>
import { CornerDownLeft } from "lucide-vue-next";

defineProps({
	width: {
		type: [String, Number],
		default: 16,
	},
	height: {
		type: [String, Number],
		default: 16,
	},
	color: {
		type: String,
		default: "currentColor",
	},
});
</script>

<template>
	<CornerDownLeft :width="width" :height="height" :color="color" />
</template>
