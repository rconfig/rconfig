<template>
	<TableCell class="text-start">
		<span :class="badgeColor || defaultBadgeClass" class="w-fit flex items-center text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border">
			{{ categoryName }}
		</span>

		<TooltipProvider :delay-duration="delayDuration" :skip-delay-duration="skipDelayDuration" v-if="shouldShowTooltip">
			<Tooltip>
				<TooltipTrigger as-child>
					<span class="text-xs text-gray-500 line-clamp-1 mt-1 ml-1">
						{{ categoryDescription }}
					</span>
				</TooltipTrigger>
				<TooltipContent side="bottom">
					<p class="max-w-xs">{{ categoryDescription }}</p>
				</TooltipContent>
			</Tooltip>
		</TooltipProvider>

		<span v-else class="text-xs text-gray-500">
			{{ categoryDescription }}
		</span>
	</TableCell>
</template>

<script setup>
import { computed, ref, onMounted } from "vue";
import { TableCell } from "@/components/ui/table";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";

const props = defineProps({
	categoryName: {
		type: String,
		required: true,
	},
	categoryDescription: {
		type: String,
		required: true,
	},
	badgeColor: {
		type: String,
		default: "",
	},
	defaultBadgeClass: {
		type: String,
		default: "bg-gray-600 text-gray-200 border-gray-500",
	},
	wordLimit: {
		type: Number,
		default: 10,
	},
	delayDuration: {
		type: Number,
		default: 800,
	},
	skipDelayDuration: {
		type: Number,
		default: 500,
	},
});

const shouldShowTooltip = computed(() => {
	return props.categoryDescription && props.categoryDescription.split(" ").length > props.wordLimit;
});
</script>
