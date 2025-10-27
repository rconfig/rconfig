<script setup>
import { Clock } from "lucide-vue-next";

const props = defineProps({
    size: {
        type: [Number, String],
        default: 16,
    },
    color: {
        type: String,
        default: "currentColor",
    },
    animate: {
        type: Boolean,
        default: true,
    },
    speed: {
        type: String,
        default: "2s",
    }
});
</script>

<template>
    <div class="pulsing-icon-wrapper">
        <Clock 
            :size="props.size" 
            :color="props.color" 
            :class="[
                'transition-all duration-300 ease-in-out', 
                props.animate ? 'animate-pulse-slow' : ''
            ]" 
            :style="{ '--pulse-duration': props.speed }"
        />
    </div>
</template>

<style scoped>
.pulsing-icon-wrapper {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

@keyframes pulse-slow {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.4;
    }
}

.animate-pulse-slow {
    animation: pulse-slow var(--pulse-duration, 2s) ease-in-out infinite;
}
</style>