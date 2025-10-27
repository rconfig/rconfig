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
  return `exclusion-rules-icon ${props.class}`;
});
</script>

<style scoped>
@keyframes shield-pulse {
  0%, 100% { opacity: 0.9; }
  50% { opacity: 1; }
}

@keyframes x-glow {
  0%, 100% { stroke: #f97316; }
  50% { stroke: #fb923c; }
}

.exclusion-rules-icon {
  filter: drop-shadow(0 0 1px rgba(249, 115, 22, 0.2));
}

.shield-path {
  animation: shield-pulse 3s ease-in-out infinite;
}

.exclusion-mark {
  animation: x-glow 2.5s ease-in-out infinite;
}
</style>

<template>
    <svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 16 16" :class="combinedClass">
        <g fill="none" stroke-linecap="round" stroke-linejoin="round">
            <!-- Shield outline -->
            <path 
                class="shield-path" 
                stroke="#6b7280" 
                d="M8 2l-6 2v4c0 3.5 6 6 6 6s6-2.5 6-6V4l-6-2z" 
                stroke-width="1.2" 
                fill="none"
            />
            
            <!-- Large X mark in center -->
            <g class="exclusion-mark" stroke-width="2">
                <path 
                    d="M6 6l4 4m-4 0l4-4" 
                    stroke="#f97316"
                />
            </g>
        </g>
    </svg>
</template>
