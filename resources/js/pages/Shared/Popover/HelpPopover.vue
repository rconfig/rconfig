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
	<div
		class="inline-flex"
		@mouseenter="onMouseEnter"
		@mouseleave="onMouseLeave"
	>
		<Popover :open="isOpen">
			<PopoverTrigger as-child>
				<Button
					tabindex="-1"
					variant="ghost"
					class="h-0 p-0 m-0 ml-1"
				>
					<CircleHelp
						v-if="hasIcon"
						size="12"
					/>
					<slot v-else />
				</Button>
			</PopoverTrigger>
			<PopoverContent
				class="w-80"
				:side="side"
				align="start"
			>
				<div class="grid gap-4">
					<div class="space-y-2">
						<div class="flex items-center space-x-2">
							<h4 class="font-medium leading-none">
								{{ title }}
							</h4>
						</div>
						<span
							v-if="content"
							class="text-sm text-muted-foreground mt-2"
							v-html="content"
						></span>
						<slot v-else />
					</div>
				</div>
			</PopoverContent>
		</Popover>
	</div>
</template>
