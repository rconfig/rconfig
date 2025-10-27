<script setup>
import { Search } from "lucide-vue-next";
import { computed } from "vue";

const props = defineProps({
	height: {
		type: [Number, String],
		default: 8,
	},
	width: {
		type: [Number, String],
		default: 8,
	},
	class: {
		type: String,
		default: "",
	},
});

const combinedClass = computed(() => {
	return `size-4 text-muted-foreground ${props.class}`;
});
</script>

<template>
	<Search :width="width" :height="height" :class="combinedClass" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
</template>
