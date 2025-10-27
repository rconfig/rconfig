<script setup>
import { computed } from "vue";

const props = defineProps({
	height: {
		type: Number,
		default: 16,
	},
	width: {
		type: Number,
		default: 16,
	},
	class: {
		type: String,
		default: "",
	},
});

// Combine default class with incoming class
const combinedClass = computed(() => {
  return `inventory-icon ${props.class}`;
});
</script>

<style scoped>
@keyframes drawer-slide {
  0%, 100% { transform: translateX(0); }
  20%, 80% { transform: translateX(0.5px); }
}

@keyframes item-highlight {
  0%, 40%, 100% { stroke: #eed49f; }
  50%, 90% { stroke: #f5a97f; }
}

.inventory-icon {
  filter: drop-shadow(0 0 1px rgba(238, 212, 159, 0.2));
}

.case-path {
  animation: none;
}

.drawer-path {
  animation: drawer-slide 3s ease-in-out infinite;
  animation-delay: 0.5s;
}

.item-path {
  animation: item-highlight 4s ease-in-out infinite;
}
</style>

<template>
	<svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 16 16" :class="combinedClass">
		<g fill="none" stroke-linecap="round" stroke-linejoin="round">
			<path 
				class="case-path" 
				stroke="#cad3f5" 
				d="m1.875 8l.686-2.743a1 1 0 0 1 .97-.757h10.938a1 1 0 0 1 .97 1.243l-.315 1.26M6 13.5H2.004A1.5 1.5 0 0 1 .5 12V3.5a1 1 0 0 1 1-1h5a1 1 0 0 1 1 1v1" 
				stroke-width="1" 
			/>
			<path 
				class="drawer-path" 
				stroke="#eed49f" 
				d="M8.5 9.5h7v2h-7zm-2 4h7v2h-7z" 
				stroke-width="1" 
			/>
			<path 
				class="item-path" 
				stroke="#eed49f" 
				d="M10.5 9.5v2m0 4v2" 
				stroke-width="1" 
			/>
		</g>
	</svg>
</template>
