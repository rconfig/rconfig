<script setup>
 
const props = defineProps({
	height: {
		type: Number,
		default: 24,
	},
	width: {
		type: Number,
		default: 24,
	},
});
</script>

<template>
	<svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 16 16"><path fill="none" stroke="#f5a97f" stroke-linecap="round" stroke-linejoin="round" d="m2.5 8.51l-2 .01m1.5 2h1.5m-2-4h1m6.73-2.19a1.5 1.5 0 1 0-2.16.58m2.16-.58l2.78-.74l.75 2.78m.57 2.16l.75 2.79l-7.73 2.07l-2.07-7.73l2.78-.75m6.28 3.63a1.5 1.5 0 1 0-.58-2.17" stroke-width="1" /></svg>
</template>
