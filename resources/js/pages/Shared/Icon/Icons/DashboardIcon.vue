<script setup>
import { LayoutDashboard } from "lucide-vue-next";

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
});
</script>

<template> <LayoutDashboard :width="width" :height="height" color="#c8d1f3" :class="class" /> </template>
