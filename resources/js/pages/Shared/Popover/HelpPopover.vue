<script setup lang="ts">
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { ref } from "vue";
import { CircleHelp } from "lucide-vue-next";

const props = defineProps({
	title: {
		type: String,
		required: true,
	},
	content: {
		type: String,
		required: false,
	},
	hasIcon: {
		type: Boolean,
		default: true,
	},
	side: {
		type: String,
		default: "top",
	},
});

// Control the open state of the popover
const isOpen = ref(false);

// Functions to handle hover events
const onMouseEnter = () => {
	isOpen.value = true;
};

const onMouseLeave = () => {
	isOpen.value = false;
};
</script>

<template>
	<div @mouseenter="onMouseEnter" @mouseleave="onMouseLeave" class="inline-flex">
		<Popover :open="isOpen">
			<PopoverTrigger as-child>
				<Button tabindex="-1" variant="ghost" class="h-0 p-0 m-0 ml-1">
					<CircleHelp size="12" v-if="hasIcon" />
					<slot v-else />
				</Button>
			</PopoverTrigger>
			<PopoverContent class="w-80" :side="side" align="start">
				<div class="grid gap-4">
					<div class="space-y-2">
						<div class="flex items-center space-x-2">
							<h4 class="font-medium leading-none">{{ title }}</h4>
						</div>
						<span class="text-sm text-muted-foreground mt-2" v-if="content" v-html="content"></span>
						<slot v-else />
					</div>
				</div>
			</PopoverContent>
		</Popover>
	</div>
</template>
