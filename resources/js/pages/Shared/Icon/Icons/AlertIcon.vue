<script setup>
import { Bell } from "lucide-vue-next";

const props = defineProps({
	width: {
		type: Number,
		default: 16,
	},
	height: {
		type: Number,
		default: 16,
	},
	class: {
		type: String,
		default: "",
	},
});
</script>

<template>
	<Bell :width="width" :height="height" :class="class" />
</template>
