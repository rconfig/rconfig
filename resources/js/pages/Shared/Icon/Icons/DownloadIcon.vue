<script setup>
import { computed } from "vue";
import { Download } from "lucide-vue-next";

const props = defineProps({
	width: {
		type: Number,
		default: 24,
	},
	height: {
		type: Number,
		default: 24,
	},
	class: {
		type: String,
		default: " ",
	},
});

const combinedClass = computed(() => {
	return `download-icon ${props.class}`;
});
</script>

<template>
	<Download :width="width" :height="height" :class="combinedClass" />
</template>
