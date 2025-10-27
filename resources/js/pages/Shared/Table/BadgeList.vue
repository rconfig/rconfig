<script setup>
import { computed, inject } from "vue";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { HoverCard, HoverCardContent, HoverCardTrigger } from "@/components/ui/hover-card";
import RcBadge from "@/pages/Shared/Badges/RcBadge.vue";
const formatters = inject("formatters");

const props = defineProps({
	items: {
		type: Array,
		required: true,
		default: () => [],
	},
	displayField: {
		type: String,
		default: "name",
	},
	maxVisible: {
		type: Number,
		default: 3,
	},
	variant: {
		type: String,
		default: "outline",
	},
	linkField: {
		type: String,
		default: null,
	},
	linkString: {
		type: String,
		default: "View",
	},
	linkStringHasId: {
		type: Boolean,
		default: false,
	},
	showEmptyText: {
		type: Boolean,
		default: true,
	},
	emptyText: {
		type: String,
		default: "--",
	},
	popoverTitle: {
		type: String,
		default: "",
	},
	enableHoverCards: {
		type: Boolean,
		default: true,
	},
	hoverCardFields: {
		type: Array,
		default: () => ["id", "name", "description"],
	},
});

const visibleItems = computed(() => props.items.slice(0, props.maxVisible));
const hiddenItems = computed(() => props.items.slice(props.maxVisible));
const hasHiddenItems = computed(() => props.items.length > props.maxVisible);

const getItemLink = (item) => {
	if (props.linkField && item[props.linkField]) {
		return item[props.linkField];
	} else if (props.linkString) {
		return `${props.linkString.toLowerCase()}` + (props.linkStringHasId ? `/${item.id}` : "");
	}
	return "#";
};

const getItemDisplay = (item) => {
	return item[props.displayField] || item.name || item.id || "Unknown";
};

const formatHoverCardContent = (item) => {
	const content = {};
	props.hoverCardFields.forEach((field) => {
		if (item[field] && item[field] !== null && item[field] !== undefined) {
			content[field] = item[field];
		}
	});
	return content;
};

const getPopoverTitle = () => {
	if (props.popoverTitle) return props.popoverTitle;
	const fieldName = props.displayField.replace(/([A-Z])/g, " $1").toLowerCase();
	return `All ${fieldName}s`;
};
</script>

<template>
	<div class="flex flex-wrap items-center gap-2">
		<template v-if="items.length > 0">
			<template v-for="item in visibleItems" :key="item.id || item[displayField]">
				<HoverCard v-if="enableHoverCards">
					<HoverCardTrigger>
						<span v-if="'categoryName' in item" :class="item.badgeColor ? item.badgeColor : 'bg-gray-600 text-gray-200 border-gray-500'" class="my-1 cursor-pointer w-fit flex items-center text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border">
							{{ item.categoryName }}
						</span>
						<RcBadge v-else :variant="variant" class="my-1 hover:bg-rcgray-800 cursor-pointer">
							<router-link :to="getItemLink(item)" class="flex items-center">
								{{ getItemDisplay(item) }}
							</router-link>
						</RcBadge>
					</HoverCardTrigger>
					<HoverCardContent class="w-80 bg-gradient-to-r from-card/80 via-card/60 to-card/80 backdrop-blur-md shadow-sm">
						<div class="space-y-2">
							<div v-for="(value, key) in formatHoverCardContent(item)" :key="key" class="flex justify-between">
								<span class="text-sm font-medium text-muted-foreground capitalize"> {{ formatters.formatKeyLabel(key) }}: </span>
								<span class="text-sm text-right max-w-[200px] truncate" :title="String(value)">
									{{ value }}
								</span>
							</div>
						</div>
					</HoverCardContent>
				</HoverCard>
				<RcBadge v-else :variant="variant" class="my-1 hover:bg-rcgray-800">
					<router-link :to="getItemLink(item)">
						{{ getItemDisplay(item) }}
					</router-link>
				</RcBadge>
			</template>

			<Popover v-if="hasHiddenItems">
				<PopoverTrigger as-child>
					<RcBadge variant="updated" class="my-1 hover:bg-muted cursor-pointer"> +{{ hiddenItems.length }} </RcBadge>
				</PopoverTrigger>
				<PopoverContent class="w-96 max-h-80 overflow-y-auto">
					<div class="space-y-3">
						<div class="font-medium text-sm">{{ getPopoverTitle() }}</div>
						<div class="text-xs text-muted-foreground">Showing {{ hiddenItems.length }} additional items</div>
						<div class="flex flex-wrap gap-2">
							<template v-for="item in hiddenItems" :key="item.id || item[displayField]">
								<HoverCard v-if="enableHoverCards">
									<HoverCardTrigger>
										<RcBadge variant="outline" class="cursor-pointer">
											<router-link :to="getItemLink(item)" class="flex items-center"> {{ getItemDisplay(item) }} </router-link>
										</RcBadge>
									</HoverCardTrigger>
									<HoverCardContent class="w-80">
										<div class="space-y-2">
											<div v-for="(value, key) in formatHoverCardContent(item)" :key="key" class="flex justify-between">
												<span class="text-sm font-medium text-muted-foreground capitalize"> {{ formatters.formatKeyLabel(key) }}: </span>
												<span class="text-sm text-right max-w-[200px] truncate" :title="String(value)">
													{{ value }}
												</span>
											</div>
										</div>
									</HoverCardContent>
								</HoverCard>
								<RcBadge v-else :variant="variant" class="hover:bg-rcgray-800">
									<router-link :to="getItemLink(item)">
										{{ getItemDisplay(item) }}
									</router-link>
								</RcBadge>
							</template>
						</div>
					</div>
				</PopoverContent>
			</Popover>
		</template>
		<span v-if="items.length == 0 && showEmptyText" class="text-muted-foreground">{{ emptyText }}</span>
	</div>
</template>
