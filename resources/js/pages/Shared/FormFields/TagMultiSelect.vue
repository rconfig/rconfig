<script setup>
import axios from "axios";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { ScrollArea } from "@/components/ui/scroll-area";
import { X } from "lucide-vue-next";
import { onMounted, ref, watch } from "vue";
import { useMultiSelect } from "./useMultiSelect.js";

const emit = defineEmits(["update:modelValue"]);
const tags = ref([]);
const isLoading = ref(true);
const isExpanded = ref(false);

const props = defineProps({
	modelValue: {
		type: [Array, String],
		required: true,
	},
	singleSelect: {
		type: Boolean,
		default: false,
	},
	placeholder: {
		type: String,
		default: "Select tags",
	},
});

const { selectedItems: selectedTags, open, searchTerm, filteredItems: filteredTags, selectItem, deleteItem } = useMultiSelect({
	items: tags,
	modelValue: props.modelValue,
	singleSelect: props.singleSelect,
	displayField: "tagname",
	searchFields: ["tagname"],
	emit,
});

onMounted(() => {
	fetchTags();
});

// Toggle expansion of selected tags
function toggleExpanded() {
	isExpanded.value = !isExpanded.value;
}

// Watch selected tags and reset expanded state if we have 3 or fewer tags
watch(
	selectedTags,
	(newSelectedTags) => {
		if (newSelectedTags.length <= 3) {
			isExpanded.value = false;
		}
	},
	{ deep: true }
);

function fetchTags() {
	isLoading.value = true;
	axios.get("/api/tags/?perPage=10000").then((response) => {
		tags.value = response.data.data;
		isLoading.value = false;
	});
}
</script>

<template>
	<Popover>
		<PopoverTrigger class="col-span-3">
			<Button variant="ghost" class="flex items-start justify-start w-full px-2 py-1 border rounded-xl gap-1 bg-background" :class="[selectedTags.length === 0 ? 'text-muted-foreground h-fit whitespace-nowrap' : 'h-auto min-h-fit']" :style="selectedTags.length === 0 ? 'padding: 0.45rem' : 'padding: 0.2rem'">
				<span v-if="isLoading">Loading tags...</span>
				<template v-else class="text-muted-foreground">
					<RcIcon name="tag" class="mx-2" />

					<span v-if="selectedTags.length === 0">{{ props.placeholder }}</span>

					<!-- Display single selected item -->
					<span v-else-if="props.singleSelect && selectedTags.length > 0" class="flex items-center text-xs font-medium px-2.5 py-0.5 rounded-xl border bg-muted">
						{{ selectedTags[0].tagname }}
						<X :size="16" class="ml-1 cursor-pointer hover:text-primary" @click.stop="deleteItem(selectedTags[0].id)" />
					</span>

					<!-- Display multiple selected items -->
					<div v-if="!props.singleSelect && selectedTags.length > 0" class="flex flex-wrap gap-1 flex-1 overflow-hidden">
						<!-- Show first 3 tags or all tags based on expanded state -->
						<span v-for="tag in isExpanded ? selectedTags : selectedTags.slice(0, 3)" :key="tag.id" class="relative group flex-shrink-0">
							<span class="flex items-center text-xs font-medium px-2.5 py-0.5 rounded-xl border whitespace-nowrap max-w-[150px]" :class="tag.badgeColor ? tag.badgeColor : 'bg-secondary text-secondary-foreground border-border'">
								<span class="truncate">{{ tag.tagname }}</span>
								<X :size="16" class="ml-1 cursor-pointer hover:text-primary flex-shrink-0" @click.stop="deleteItem(tag.id)" />
							</span>
						</span>
						<!-- Show expandable button if there are more than 3 selected and not expanded -->
						<button v-if="selectedTags.length > 3 && !isExpanded" @click.stop="toggleExpanded()" class="flex items-center text-xs font-medium px-2.5 py-0.5 rounded-xl border bg-muted text-muted-foreground flex-shrink-0 hover:bg-muted/80 transition-colors cursor-pointer rc-btn-shadow">+{{ selectedTags.length - 3 }} more</button>
						<!-- Show collapse button when expanded and there are more than 3 items -->
						<button v-if="selectedTags.length > 3 && isExpanded" @click.stop="toggleExpanded()" class="flex items-center text-xs font-medium px-2.5 py-0.5 rounded-xl border bg-muted text-muted-foreground flex-shrink-0 hover:bg-muted/80 transition-colors cursor-pointer rc-btn-shadow">
							Show less
						</button>
					</div>
				</template>
			</Button>
		</PopoverTrigger>

		<PopoverContent side="bottom" align="start" class="col-span-3 p-0">
			<div class="relative items-center w-full">
				<Input id="search" type="text" v-model="searchTerm" autocomplete="off" placeholder="Search" class="pl-10 border-none focus:outline-none focus-visible:ring-0 text-muted-foreground font-inter" />
				<span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
					<RcIcon name="search" />
				</span>
			</div>
			<Separator />

			<ScrollArea class="h-64">
				<div class="py-1">
					<RcIcon name="three-dots-loading" class="w-8 h-8 mx-auto my-4 text-muted-foreground" v-if="isLoading" />
					<div v-else v-for="tag in filteredTags" :key="tag.id" class="w-full p-1 pl-2 my-1 text-sm rounded-lg hover:bg-accent hover:text-accent-foreground" @click="selectItem(tag)">
						<span data-size="20" class="cursor-default text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border" :class="tag.badgeColor ? tag.badgeColor : 'bg-secondary text-secondary-foreground border-border'">
							<span data-size="20">
								{{ tag.tagname }}
							</span>
						</span>
					</div>
				</div>
			</ScrollArea>

			<Separator />

			<div class="p-1 border-5">
				<Button variant="ghost" class="justify-start w-full p-1">
					<RcIcon name="plus" class="w-8" />
					<div class="rc-text-xs-muted ml-1">Create new record</div>
				</Button>
			</div>
		</PopoverContent>
	</Popover>
</template>
