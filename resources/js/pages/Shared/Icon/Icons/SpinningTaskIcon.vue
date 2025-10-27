<script setup>
import { RotateCw } from "lucide-vue-next";

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
    <div 
        class="spinning-icon-wrapper"
    >
        <RotateCw 
            :size="props.size" 
            :color="props.color" 
            :class="[
                'transition-all duration-300 ease-in-out', 
                props.animate ? 'animate-spin-slow' : ''
            ]"
            :style="{ '--spin-duration': props.speed }"
        />
    </div>
</template>

<style scoped>
.spinning-icon-wrapper {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

@keyframes spin-slow {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

.animate-spin-slow {
    animation: spin-slow var(--spin-duration, 2s) linear infinite;
}
</style>