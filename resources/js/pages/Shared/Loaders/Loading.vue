<script setup>
const props = defineProps({
	text: {
		type: String,
		default: "Loading",
	},
});
</script>

<template>
	<div class="flex items-center justify-center text-sm text-muted-foreground">
		<span>{{ text }}</span>
		<RcIcon name="three-dots-loading" class="ml-2" />
	</div>
</template>
