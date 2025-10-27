<script setup>
 
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
</script>

<template>
    <svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 16 16" :class="class">
        <!-- Multi-tool/Swiss Army knife style icon representing versatility -->
        <!-- Main body -->
        <rect 
            x="6" y="3" width="4" height="10" rx="0.5" 
            fill="none" 
            stroke="#f97316" 
            stroke-width="1"
        />
        
        <!-- Tool 1: Screwdriver -->
        <path 
            d="M5 6 L3 6 M3 6 L2.5 5.5 M3 6 L2.5 6.5" 
            stroke="#f97316" 
            stroke-width="1" 
            stroke-linecap="round"
        />
        
        <!-- Tool 2: Knife blade -->
        <path 
            d="M11 7 L13 5.5 L13.5 6 L11.5 7.5 Z" 
            fill="#f97316" 
            stroke="#f97316" 
            stroke-width="0.5"
        />
        
        <!-- Tool 3: Wrench -->
        <path 
            d="M5 9.5 L3.5 9.5 M3.5 9.5 C3.2 9.5 3 9.3 3 9 C3 8.7 3.2 8.5 3.5 8.5 L4 8.5" 
            stroke="#f97316" 
            stroke-width="1" 
            stroke-linecap="round"
        />
        
        <!-- Tool 4: Small scissors -->
        <path 
            d="M11 10 L12.5 9 M11 10 L12.5 11 M12.5 9 L13 8.5 M12.5 11 L13 11.5" 
            stroke="#f97316" 
            stroke-width="1" 
            stroke-linecap="round"
        />
        
        <!-- Center pivot/hinge -->
        <circle 
            cx="8" cy="8" r="1" 
            fill="none" 
            stroke="#f97316" 
            stroke-width="1"
        />
    </svg>
</template>
