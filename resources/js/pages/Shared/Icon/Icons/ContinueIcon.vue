<script setup>
// filepath: /var/www/html/rconfig7/resources/js/pages/Shared/Icon/Icons/ContinueIcon.vue
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
  return `continue-icon ${props.class}`;
});
</script>

<style scoped>
@keyframes arrow-slide {
  0%, 100% { transform: translateX(0); opacity: 0.7; }
  50% { transform: translateX(2px); opacity: 1; }
}

@keyframes circle-pulse {
  0%, 100% { stroke-width: 1.2; opacity: 0.8; }
  50% { stroke-width: 1.5; opacity: 1; }
}

@keyframes trail-flow {
  0% { opacity: 0.3; transform: translateX(-2px); }
  50% { opacity: 0.6; transform: translateX(0px); }
  100% { opacity: 0.3; transform: translateX(1px); }
}

.continue-icon {
  filter: drop-shadow(0 0 2px rgba(59, 130, 246, 0.3));
}

.arrow-main {
  animation: arrow-slide 2s ease-in-out infinite;
}

.circle-border {
  animation: circle-pulse 2.5s ease-in-out infinite;
}

.trail-dots {
  animation: trail-flow 2s ease-in-out infinite;
}

.trail-dots:nth-child(2) {
  animation-delay: 0.3s;
}

.trail-dots:nth-child(3) {
  animation-delay: 0.6s;
}
</style>

<template>
    <svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 16 16" :class="combinedClass">
        <g fill="none" stroke-linecap="round" stroke-linejoin="round">
            <!-- Circular border -->
            <circle 
                class="circle-border" 
                cx="8" 
                cy="8" 
                r="6.5" 
                stroke="#3b82f6" 
                stroke-width="1.2" 
                fill="none"
            />
            
            <!-- Trail dots (leading the arrow) -->
            <g class="trail-dots" stroke="#60a5fa" stroke-width="1">
                <circle cx="4" cy="8" r="0.5" fill="#60a5fa" opacity="0.4"/>
                <circle cx="5.5" cy="8" r="0.3" fill="#60a5fa" opacity="0.6"/>
            </g>
            
            <!-- Main arrow -->
            <g class="arrow-main" stroke="#3b82f6" stroke-width="1.5">
                <!-- Arrow shaft -->
                <path d="M6.5 8h4" />
                <!-- Arrow head -->
                <path d="M9 6.5L10.5 8L9 9.5" />
            </g>
            
            <!-- Secondary arrow (subtle) -->
            <g stroke="#93c5fd" stroke-width="1" opacity="0.5">
                <path d="M10.5 8h1" />
                <path d="M11 7.5l0.5 0.5l-0.5 0.5" />
            </g>
        </g>
    </svg>
</template>