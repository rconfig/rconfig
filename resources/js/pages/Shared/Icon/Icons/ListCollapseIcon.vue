<script setup>
import { computed } from "vue";

import { ListCollapse } from "lucide-vue-next";

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
const combinedClass = computed(() => {
	return `text-sky-300 ${props.class}`;
});
</script>

<template>
	<ListCollapse :width="width" :height="height" :class="combinedClass" />
</template>
