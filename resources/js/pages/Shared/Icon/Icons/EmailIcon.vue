<script setup>
import { computed } from "vue";
import { Mail } from "lucide-vue-next";

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

// Combine default class with incoming class
const combinedClass = computed(() => {
  return `text-green-300 ${props.class}`;
});
</script>

<template>
	<Mail :width="width" :height="height" :class="combinedClass" />
</template>
