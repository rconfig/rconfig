<script setup>
import { computed } from 'vue';

const props = defineProps({
  width: {
    type: Number,
    default: 16
  },
  height: {
    type: Number,
    default: 16
  },
  class: {
    type: String,
    default: ''
  },
  animate: {
    type: Boolean,
    default: false
  }
});

const combinedClass = computed(() => {
  return `star-icon ${props.animate ? 'star-animated' : ''} ${props.class}`;
});
</script>

<style scoped>
@keyframes star-pulse {
  0%, 100% {
    transform: scale(1);
    filter: drop-shadow(0 0 0px rgba(238, 212, 159, 0));
  }
  50% {
    transform: scale(1.1);
    filter: drop-shadow(0 0 3px rgba(238, 212, 159, 0.7));
  }
}

@keyframes star-rotate {
  0% {
    transform: rotate(0deg);
  }
  25% {
    transform: rotate(3deg);
  }
  75% {
    transform: rotate(-3deg);
  }
  100% {
    transform: rotate(0deg);
  }
}

.star-icon {
  display: inline-block;
  transition: transform 0.3s ease;
}

.star-animated {
  animation: star-pulse 2s infinite ease-in-out;
}

.star-animated:hover {
  animation: star-rotate 0.5s ease-in-out;
}

.star-points {
  fill: #eed49f;
}

.star-glow {
  fill: #eed49f;
  opacity: 0.3;
}
</style>

<template>
  <svg 
    xmlns="http://www.w3.org/2000/svg" 
    :width="width" 
    :height="height" 
    viewBox="0 0 24 24" 
    :class="combinedClass"
  >
    <!-- Base star shape with fill color -->
    <path 
      class="star-points" 
      d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"
    />
    
    <!-- Overlay for glow effect -->
    <path 
      class="star-glow" 
      d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"
    />
  </svg>
</template>
