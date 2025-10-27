<script setup>
import { HoverCard, HoverCardTrigger, HoverCardContent } from "@/components/ui/hover-card";
import { MessagesSquare } from "lucide-vue-next";

const props = defineProps({
	iconClass: {
		type: String,
		default: "w-4 h-4 text-sky-500 hover:text-sky-400 cursor-help",
	},
	width: {
		type: String,
		default: "w-72",
	},
});

defineOptions({ inheritAttrs: false });
</script>

<template>
	<HoverCard>
		<HoverCardTrigger>
			<MessagesSquare :class="[iconClass, $attrs.class]" />
		</HoverCardTrigger>
		<HoverCardContent :class="`${width} border bg-card/80 backdrop-blur-2xl`">
			<slot />
		</HoverCardContent>
	</HoverCard>
</template>
