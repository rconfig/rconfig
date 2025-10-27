<script setup>
import { ref } from "vue";
import { Plus } from "lucide-vue-next";
import { computed } from "vue";

const isHovered = ref(false);

const props = defineProps({
	size: {
		type: [Number, String],
		default: 16,
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
	<Plus :size="size" :class="combinedClass" />
</template>