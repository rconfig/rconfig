<script setup>
// filepath: /var/www/html/rconfig7/resources/js/pages/Shared/Icon/Icons/HistoryIcon.vue
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
  return `history-icon ${props.class}`;
});
</script>

<style scoped>
@keyframes clock-tick {
  0%, 100% { transform: rotate(0deg); }
  25% { transform: rotate(90deg); }
  50% { transform: rotate(180deg); }
  75% { transform: rotate(270deg); }
}

@keyframes history-pulse {
  0%, 100% { opacity: 0.7; stroke-width: 1.2; }
  50% { opacity: 1; stroke-width: 1.5; }
}

@keyframes data-flow {
  0% { opacity: 0.3; transform: translateY(2px); }
  50% { opacity: 0.8; transform: translateY(0px); }
  100% { opacity: 0.3; transform: translateY(-1px); }
}

@keyframes timeline-glow {
  0%, 100% { stroke: #8b5cf6; opacity: 0.6; }
  50% { stroke: #a78bfa; opacity: 1; }
}

.history-icon {
  filter: drop-shadow(0 0 2px rgba(139, 92, 246, 0.3));
}

.clock-hand {
  animation: clock-tick 4s ease-in-out infinite;
  transform-origin: 8px 8px;
}

.clock-border {
  animation: history-pulse 3s ease-in-out infinite;
}

.timeline-line {
  animation: timeline-glow 2.5s ease-in-out infinite;
}

.data-point {
  animation: data-flow 2s ease-in-out infinite;
}

.data-point:nth-child(2) {
  animation-delay: 0.4s;
}

.data-point:nth-child(3) {
  animation-delay: 0.8s;
}

.data-point:nth-child(4) {
  animation-delay: 1.2s;
}
</style>

<template>
    <svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 16 16" :class="combinedClass">
        <g fill="none" stroke-linecap="round" stroke-linejoin="round">
            <!-- Clock face background -->
            <circle 
                class="clock-border" 
                cx="8" 
                cy="8" 
                r="6" 
                stroke="#8b5cf6" 
                stroke-width="1.2" 
                fill="none"
            />
            
            <!-- Clock hour markers -->
            <g stroke="#a78bfa" stroke-width="1" opacity="0.6">
                <path d="M8 2v1" />
                <path d="M14 8h-1" />
                <path d="M8 14v-1" />
                <path d="M2 8h1" />
            </g>
            
            <!-- Clock hands -->
            <g class="clock-hand" stroke="#8b5cf6" stroke-width="1.5">
                <!-- Hour hand (short) -->
                <path d="M8 8L8 5.5" />
                <!-- Minute hand (long) -->
                <path d="M8 8L10.5 8" />
            </g>
            
            <!-- Center dot -->
            <circle cx="8" cy="8" r="0.8" fill="#8b5cf6" />
            
            <!-- Timeline elements (representing history) -->
            <g class="timeline-line" stroke="#a78bfa" stroke-width="1" opacity="0.7">
                <!-- Vertical timeline -->
                <path d="M1 3v10" />
            </g>
            
            <!-- Historical data points -->
            <g class="data-point" fill="#c4b5fd" opacity="0.8">
                <circle cx="1" cy="4" r="0.8" />
            </g>
            <g class="data-point" fill="#c4b5fd" opacity="0.8">
                <circle cx="1" cy="7" r="0.8" />
            </g>
            <g class="data-point" fill="#c4b5fd" opacity="0.8">
                <circle cx="1" cy="10" r="0.8" />
            </g>
            <g class="data-point" fill="#c4b5fd" opacity="0.8">
                <circle cx="1" cy="13" r="0.8" />
            </g>
        </g>
    </svg>
</template>