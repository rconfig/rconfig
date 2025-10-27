<script setup>
import { computed } from "vue";

import { Waypoints } from "lucide-vue-next";

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
  return `text-emerald-300 ${props.class}`;
});
</script>

<template>
	<Waypoints :width="width" :height="height" :class="combinedClass" />
</template>
