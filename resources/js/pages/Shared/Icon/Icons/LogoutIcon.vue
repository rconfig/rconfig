<script setup>
import { computed, ref } from "vue";
 
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

// Combine default class with incoming class
const combinedClass = computed(() => {
  return `text-blue-300 logout-icon ${props.class}`;
});

const isHovering = ref(false);

const onMouseEnter = () => {
  isHovering.value = true;
};

const onMouseLeave = () => {
  isHovering.value = false;
};
</script>

<style scoped>
@keyframes slide-out {
  0% {
    transform: translateX(0);
  }
  50% {
    transform: translateX(3px);
    color: #ed8796; /* Change to a warning/exit color */
  }
  100% {
    transform: translateX(0);
  }
}

@keyframes arrow-exit {
  0% {
    transform: translateX(0);
    stroke: currentColor;
  }
  50% {
    transform: translateX(2px);
    stroke: #ed8796;
  }
  100% {
    transform: translateX(0);
    stroke: currentColor;
  }
}

@keyframes door-pulse {
  0%, 100% {
    stroke-width: 2;
  }
  50% {
    stroke-width: 2.5;
  }
}

.logout-icon {
  transition: all 0.3s ease;
}

.logout-icon:hover {
  color: #ed8796;
}

.logout-animation .door-frame {
  animation: door-pulse 1.5s ease-in-out infinite;
}

.logout-animation .arrow-head,
.logout-animation .arrow-body {
  animation: arrow-exit 1s ease-in-out infinite;
}

/* Slight delay between animations */
.logout-animation .arrow-head {
  animation-delay: 0.1s;
}
</style>

<template>
	<div 
    @mouseenter="onMouseEnter"
    @mouseleave="onMouseLeave"
    class="inline-flex"
  >
  <svg 
    xmlns="http://www.w3.org/2000/svg" 
    :width="width" 
    :height="height" 
    viewBox="0 0 24 24" 
    fill="none" 
    stroke="currentColor" 
    stroke-width="2" 
    stroke-linecap="round" 
    stroke-linejoin="round"
    :class="[combinedClass, { 'logout-animation': isHovering }]"
  >
    <!-- Door frame path -->
    <path class="door-frame" d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
    <!-- Arrow path with separate classes for animation -->
    <polyline class="arrow-head" points="16 17 21 12 16 7"/>
    <line class="arrow-body" x1="21" x2="9" y1="12" y2="12"/>
  </svg>
	</div>
</template>
