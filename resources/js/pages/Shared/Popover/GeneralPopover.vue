<script setup>
import { ref } from "vue";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { ExternalLink } from "lucide-vue-next";

// Props for customization
const props = defineProps({
	triggerContent: {
		type: [String, Object],
		default: "Click me",
	},
	title: {
		type: String,
		default: "",
	},
	description: {
		type: String,
		default: "",
	},
	description2: {
		type: String,
		default: "",
	},
	href: {
		type: String,
		default: null,
	},
	linkText: {
		type: String,
		default: "View details",
	},
	width: {
		type: String,
		default: "w-64", // Default width classes
	},
	align: {
		type: String,
		default: "end", // Default alignment
	},
	hasLink: {
		type: Boolean,
		default: true, // Whether to show the link action
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
	<Popover @mouseenter="onMouseEnter" @mouseleave="onMouseLeave" class="inline-flex">
		<PopoverTrigger :open="isOpen">
			<!-- Slot for trigger content - can be any element -->
			<slot name="trigger">
				<div class="cursor-pointer">
					<component :is="typeof triggerContent === 'string' ? 'span' : triggerContent" v-if="typeof triggerContent !== 'string'" class="cursor-help" />
					<span v-else>{{ triggerContent }}</span>
				</div>
			</slot>
		</PopoverTrigger>

		<PopoverContent class="shadow-lg border bg-card/95 backdrop-blur-xl" :class="width" :align="align">
			<!-- Title slot (optional) -->
			<slot name="title">
				<h4 v-if="title" class="font-medium text-sm mb-2">{{ title }}</h4>
			</slot>

			<!-- Description slot -->
			<slot name="content">
				<p v-if="description" class="text-sm text-gray-400">{{ description }}</p>
				<p v-if="description2" class="text-sm text-gray-400">{{ description2 }}</p>
			</slot>

			<!-- Action slot -->
			<slot name="action">
				<div class="border-t bg-muted/20 mt-3" v-if="hasLink">
					<div v-if="href" class="flex mt-3 justify-start">
						<Button variant="outline" size="sm" asChild class="text-xs">
							<a :href="href" target="_blank" rel="noopener noreferrer" class="inline-flex items-center">
								{{ linkText }}
								<ExternalLink class="ml-2 h-3 w-3" />
							</a>
						</Button>
					</div>
				</div>
			</slot>
		</PopoverContent>
	</Popover>
</template>
