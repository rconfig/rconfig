<script setup>
defineProps({
    size: {
      type: [Number, String],
      default: 24
    },
    className: {
      type: String,
      default: ''
    }
  });
</script>
<template>
  <svg
    viewBox="0 0 24 24"
    :width="size"
    :height="size"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    :class="className"
  >
    <!-- Horizon background -->
    <rect 
      x="2" 
      y="6" 
      width="20" 
      height="12" 
      rx="2" 
      class="fill-purple-100 dark:fill-purple-900/30" 
    />
    
    <!-- Horizon mountains -->
    <path 
      d="M22 12.5L19 8.5L16.5 12L14 8L10 14L6.5 9L2 15" 
      stroke-width="1.5" 
      stroke-linecap="round" 
      stroke-linejoin="round" 
      class="stroke-purple-600 dark:stroke-purple-400" 
    />
    
    <!-- Small sun -->
    <circle 
      cx="18" 
      cy="8" 
      r="2" 
      class="fill-amber-400 dark:fill-amber-300" 
    />
  </svg>
</template>