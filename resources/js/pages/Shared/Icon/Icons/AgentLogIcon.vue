<script setup>
import { ref } from "vue";
import { Logs } from "lucide-vue-next";

const props = defineProps({
	height: {
		type: Number,
		default: 24,
	},
	width: {
		type: Number,
		default: 24,
	},
});
</script>

<template>
	<Logs :width="width" :height="height" class="text-blue-400" />
</template>
