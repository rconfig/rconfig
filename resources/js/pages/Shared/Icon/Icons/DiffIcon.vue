<script setup>
import { computed } from "vue";

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

const combinedClass = computed(() => {
    return `diff-icon ${props.class}`;
});
</script>

<template>
    <svg 
        xmlns="http://www.w3.org/2000/svg" 
        :class="combinedClass" 
        :width="width" 
        :height="height" 
        viewBox="0 0 24 24" 
        fill="none" 
        stroke="currentColor" 
        stroke-width="2" 
        stroke-linecap="round" 
        stroke-linejoin="round"
    >
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h4" />
        <path d="M14 2v4a2 2 0 0 0 2 2h4" />
        <path d="M20 8v10a2 2 0 0 1-2 2h-4" />
        
        <path d="M8 12h4" stroke="#ef4444" stroke-width="2" />
        <path d="M8 16h4" stroke="#ef4444" stroke-width="2" />
        
        <path d="M12 12h4" stroke="#22c55e" stroke-width="2" />
        <path d="M12 16h4" stroke="#22c55e" stroke-width="2" />
        
        <path d="M10 10 L12 8 L14 10" opacity="0.7" />
    </svg>
</template>

<style scoped>
.diff-icon {
    @apply text-current;
}

/* Dark mode friendly colors */
.dark .diff-icon path[stroke="#ef4444"] {
    stroke: #fca5a5;
}

.dark .diff-icon path[stroke="#22c55e"] {
    stroke: #86efac;
}
</style>