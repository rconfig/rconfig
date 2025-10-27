<script setup>
import { computed } from "vue";
import {ArrowUpDown} from 'lucide-vue-next';


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
		default: "",
	},
});

const combinedClass = computed(() => {
	return `sort-asc-icon ${props.class}`;
});
</script>

<template>
	 <ArrowUpDown :class="combinedClass" :width="width" :height="height" />
</template>
