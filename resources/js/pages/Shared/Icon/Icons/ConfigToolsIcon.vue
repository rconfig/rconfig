<script setup>
import { SquareTerminal } from "lucide-vue-next";

const props = defineProps({
	height: {
		type: Number,
		default: 16,
	},
	width: {
		type: Number,
		default: 16,
	},
	colorClass: {
		type: String,
		default: "text-blue-300", // closest Tailwind to #8aadf4
	},
});
</script>

<template>
	<SquareTerminal :width="width" :height="height" :class="colorClass" />
</template>
