<script setup>
// Props definition
const props = defineProps({
  width: {
    type: [Number, String],
    default: 24
  },
  height: {
    type: [Number, String],
    default: 24
  },
  color: {
    type: String,
    default: "white"
  }
});
</script>

<template>
  <svg 
    xmlns="http://www.w3.org/2000/svg" 
    :width="width" 
    :height="height" 
    viewBox="0 0 24 24" 
    fill="none" 
    :stroke="color" 
    stroke-width="1" 
    stroke-linecap="round" 
    stroke-linejoin="round"
    class="animated-eye"
  >
    <!-- Eye outline with subtle pulsing animation -->
    <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0">
      <animate 
        attributeName="d" 
        values="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0;
                M2.362 12.248a1 1 0 0 1 0-.496 10.25 10.25 0 0 1 19.276 0 1 1 0 0 1 0 .496 10.25 10.25 0 0 1-19.276 0;
                M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0" 
        dur="4s" 
        repeatCount="indefinite" 
        calcMode="spline"
        keySplines="0.42 0 0.58 1; 0.42 0 0.58 1" />
    </path>
    
    <!-- Pupil with subtle movement -->
    <circle cx="12" cy="12" r="3" fill="#404040" class="pupil">
      <animate 
        attributeName="cx" 
        values="12;12.3;12" 
        dur="3s" 
        repeatCount="indefinite" 
        calcMode="spline"
        keySplines="0.42 0 0.58 1; 0.42 0 0.58 1" />
      
      <!-- Pupil dilation animation -->
      <animate 
        attributeName="r" 
        values="3;2.7;3" 
        dur="4s" 
        repeatCount="indefinite" 
        calcMode="spline"
        keySplines="0.42 0 0.58 1; 0.42 0 0.58 1" />
    </circle>
  </svg>
</template>

<style scoped>
.animated-eye {
  display: inline-block;
  transition: transform 0.3s ease;
}

.animated-eye:hover {
  transform: scale(1.05);
}

.animated-eye .pupil {
  transition: fill 0.5s ease;
}

.animated-eye:hover circle {
  animation: hover-look 1.5s ease-in-out infinite alternate;
  transform: scale(1.05);
  transition: transform 0.3s ease;
  fill: #2563eb;
}

@keyframes hover-look {
  0% { cx: 11.5; }
  25% { cx: 12.5; }
  50% { cx: 12; }
  75% { cx: 11; }
  100% { cx: 12.5; }
}
</style>