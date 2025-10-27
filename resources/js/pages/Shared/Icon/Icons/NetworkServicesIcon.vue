<script setup>
import { computed } from "vue";

import { HardDrive } from "lucide-vue-next";

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
</script>

<template>
	<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" :class="class">
		<path fill="none" stroke="#9fd2ee" stroke-linecap="round" stroke-linejoin="round" d="M14.982 11.759L8 15.517l-6.982-3.758V4.24L8 .483l6.982 3.758Z" stroke-width="1" />
		<path fill="none" stroke="#9fd2ee" stroke-linecap="round" stroke-linejoin="round" d="M1.018 4.241L8 8.062l6.982-3.821M8 8.078v7.235m-2.975-4.63l.498.26m-2.56-1.349l.497.26" stroke-width="1" />
	</svg>
</template>
