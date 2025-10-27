<script setup>
import { HoverCard, HoverCardTrigger, HoverCardContent } from "@/components/ui/hover-card";
import { Info } from "lucide-vue-next";

const props = defineProps({
	iconClass: {
		type: String,
		default: "w-4 h-4 text-sky-600 hover:text-sky-500 cursor-help",
	},
	width: {
		type: String,
		default: "w-72",
	},
});
</script>

<template>
	<HoverCard>
		<HoverCardTrigger>
			<Info :class="iconClass" />
		</HoverCardTrigger>
		<HoverCardContent :class="`${width} border bg-card/80 backdrop-blur-2xl`">
			<slot />
		</HoverCardContent>
	</HoverCard>
</template>
