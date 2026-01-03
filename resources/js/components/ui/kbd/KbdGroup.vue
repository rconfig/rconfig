<script setup>
import { cn } from "@/lib/utils";

const props = defineProps({
	class: { type: null, required: false },
});
</script>

<template>
	<kbd id="kbd-group" data-slot="kbd-group" :class="cn('inline-flex items-center gap-1', props.class)">
		<slot />
	</kbd>
</template>
