<script setup>
import { computed } from "vue";

import { LocateFixed } from "lucide-vue-next";

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
  return `text-orange-300 ${props.class}`;
});
</script>

<template>
	<LocateFixed :width="width" :height="height" :class="combinedClass" />
</template>
