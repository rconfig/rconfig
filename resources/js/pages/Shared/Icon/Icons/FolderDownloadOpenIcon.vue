<script setup>
defineProps({
	width: {
		type: [String, Number],
		default: 16,
	},
	height: {
		type: [String, Number],
		default: 16,
	},
    
});
</script>
<template>
        <svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 16 16"><g fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="#cad3f5" d="m1.875 8l.686-2.743a1 1 0 0 1 .97-.757h10.938a1 1 0 0 1 .97 1.243l-.315 1.26M6 13.5H2.004A1.5 1.5 0 0 1 .5 12V3.5a1 1 0 0 1 1-1h5a1 1 0 0 1 1 1v1" stroke-width="1"/><path stroke="#91d7e3" d="M9.5 15.5h4m-2-7v5m-2-2l2 2l2-2" stroke-width="1"/></g></svg>

</template>
