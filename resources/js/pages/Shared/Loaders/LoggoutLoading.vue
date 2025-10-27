<script setup>
import { ref } from "vue";

defineProps({});
</script>

<template>
	<div class="flex items-center justify-center text-sm text-muted-foreground">
		<span>Logging out</span>
		<RcIcon name="three-dots-loading" class="ml-2" />
	</div>
</template>
