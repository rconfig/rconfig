<script setup>
import { FileLock } from "lucide-vue-next";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";

const props = defineProps({
	id: {
		type: [String, Number],
		required: true,
	},
	isLocked: {
		type: Boolean,
		required: true,
	},
	tooltipMessage: {
		type: String,
		required: true,
	},
	delayDuration: {
		type: Number,
		default: 800,
	},
	skipDelayDuration: {
		type: Number,
		default: 500,
	},
	lockedTextClass: {
		type: String,
		default: "text-amber-500",
	},
	unlockedTextClass: {
		type: String,
		default: "",
	},
	iconClass: {
		type: String,
		default: "ml-1 text-amber-500",
	},
	iconSize: {
		type: [String, Number],
		default: "10",
	},
});
</script>
<template>
	<div class="flex items-center">
		<TooltipProvider :delay-duration="delayDuration" :skip-delay-duration="skipDelayDuration" v-if="isLocked">
			<Tooltip>
				<TooltipTrigger as-child>
					<div class="flex items-center">
						<span :class="lockedTextClass">{{ id }}</span>
						<FileLock :class="iconClass" :size="iconSize" />
					</div>
				</TooltipTrigger>
				<TooltipContent>
					<p>{{ tooltipMessage }}</p>
				</TooltipContent>
			</Tooltip>
		</TooltipProvider>
		<span v-else :class="unlockedTextClass">{{ id }}</span>
	</div>
</template>
