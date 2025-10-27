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
  return `integration-icon ${props.class}`;
});
</script>

<style scoped>
@keyframes pulse {
  0%, 100% { 
    stroke: #91d7e3;
    stroke-width: 1;
  }
  50% { 
    stroke: #c6a0f6;
    stroke-width: 1.5;
  }
}

@keyframes flash-connection {
  0%, 100% { opacity: 0.5; }
  50% { opacity: 1; }
}

.integration-icon {
  filter: drop-shadow(0 0 2px rgba(145, 215, 227, 0.3));
}

.path-main {
  animation: pulse 3s ease-in-out infinite;
}

.path-connection {
  animation: flash-connection 2s linear infinite;
  animation-delay: 0.5s;
}
</style>

<template>
	<svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 16 16" :class="combinedClass">
		<path
			class="path-main"
			fill="none"
			stroke="#91d7e3"
			stroke-linecap="round"
			stroke-linejoin="round"
			d="m14.36 9.2l-1.52 1.5c-.08.09-.14.15-.27.03c-1.5-1.52-6.4-6.4-7.32-7.31c-.09-.1-.08-.15 0-.24l1.62-1.6c.1-.1.16-.1.25 0L9.64 4.1c.07.07.13.16.25.17l2.68-2.69c.1-.1.17-.11.27 0l1.56 1.54c.14.13.13.2 0 .33l-2.52 2.5c-.17.17-.15.25.01.4l2.47 2.47c.16.15.15.23 0 .37z"
			stroke-width="1"
		/>
		<path
			class="path-connection"
			fill="none"
			stroke="#c6a0f6"
			stroke-linecap="round"
			stroke-linejoin="round"
			d="M3.44 5.28c-.11-.11-.18-.11-.29 0c-.5.52-1.02 1.03-1.53 1.53c-.15.14-.13.22 0 .35L4.1 9.64c.17.16.16.25 0 .41c-.84.82-1.66 1.66-2.5 2.49c-.12.12-.15.2 0 .33c.51.5 1.02 1.02 1.52 1.54c.12.12.19.12.3 0c.58-.59 2.4-2.4 2.75-2.71c1.36 1.37 2.27 2.27 2.7 2.72c.1.09.17.1.26 0l1.6-1.61c.1-.09.1-.15 0-.23L3.45 5.29Z"
			stroke-width="1"
		/>
	</svg>
</template>
