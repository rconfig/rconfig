<script setup>
import { CheckSquare, Square } from "lucide-vue-next";
import { computed } from "vue";

const props = defineProps({
	isSelected: {
		type: Boolean,
		default: false,
	},
	size: {
		type: [Number, String],
		default: 16,
	},
	class: {
		type: String,
		default: "text-muted-foreground",
	},
});

const iconSize = computed(() => {
	return `h-${props.size / 4} w-${props.size / 4}`;
});

const combinedClass = computed(() => {
	return `${iconSize.value} ${props.class}`;
});
</script>

<template>
	<div class="relative">
		<CheckSquare v-if="isSelected" :class="combinedClass" />
		<Square v-else :class="combinedClass" />
	</div>
</template>
