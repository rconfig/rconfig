<script setup lang="ts">
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";

// Props for customization
const props = defineProps({
	content: {
		type: String,
		default: "Tooltip content",
	},
	side: {
		type: String,
		default: "top",
		validator: (value: string) => ["top", "bottom", "left", "right"].includes(value),
	},
	align: {
		type: String,
		default: "center",
		validator: (value: string) => ["start", "center", "end"].includes(value),
	},
	delayDuration: {
		type: Number,
		default: 400,
	},
	sideOffset: {
		type: Number,
		default: 4,
	},
	disabled: {
		type: Boolean,
		default: false,
	},
});
</script>

<template>
	<TooltipProvider :delayDuration="delayDuration">
		<Tooltip :disabled="disabled">
			<TooltipTrigger asChild>
				<slot name="trigger">
					<span class="cursor-help">Hover me</span>
				</slot>
			</TooltipTrigger>
			<TooltipContent :side="side" :align="align" :sideOffset="sideOffset" class="bg-rcgray-900 text-rcgray-900 shadow-[rgb(47,48,51)_0px_0px_0px_1px_inset,_rgb(0,0,0)_0px_0px_2px_0px,_rgba(0,0,0,0.08)_0px_1px_3px_0px] dark:bg-rcgray-800 dark:text-rcgray-200 dark:shadow-[rgb(47,48,51)_0px_0px_0px_1px_inset,_rgb(0,0,0)_0px_0px_2px_0px,_rgba(0,0,0,0.08)_0px_1px_3px_0px]">
				<slot name="content">
					<p>{{ content }}</p>
				</slot>
			</TooltipContent>
		</Tooltip>
	</TooltipProvider>
</template>
