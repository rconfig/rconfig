<script setup lang="ts">
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import CommandGroupAddEditDialog from "@/pages/Inventory/CommandGroups/CommandGroupAddEditDialog.vue";
import axios from "axios";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { ScrollArea } from "@/components/ui/scroll-area";
import { X, CloudAlert } from "lucide-vue-next";
import { onMounted, ref, watch, computed } from "vue";
import { useCommandGroups } from "@/pages/Inventory/CommandGroups/useCommandGroups";
import { useMultiSelect } from "./useMultiSelect.js";

const emit = defineEmits(["update:modelValue"]);

const { newCommandGroupsModalKey, handleSave, viewEditDialog } = useCommandGroups();

const categories = ref([]);
const isLoading = ref(true);
const isExpanded = ref(false);

const props = defineProps({
	modelValue: {
		type: [Array, Object],
		required: true,
	},
	singleSelect: {
		type: Boolean,
		default: false,
	},
	placeholder: {
		type: String,
		default: "Select command groups",
	},
});

const { selectedItems: selectedCats, open, searchTerm, filteredItems: filteredCategories, selectItem, deleteItem: deleteItemById } = useMultiSelect({
	items: categories,
	modelValue: props.modelValue,
	singleSelect: props.singleSelect,
	displayField: "categoryName",
	searchFields: ["categoryName"],
	emit,
});

// Computed property to group filtered categories
const groupedCategories = computed(() => {
	const withCommands = filteredCategories.value.filter((cat) => cat.command.length > 0);
	const withoutCommands = filteredCategories.value.filter((cat) => cat.command.length === 0);

	return {
		withCommands,
		withoutCommands,
	};
});

// Toggle expansion of selected categories
function toggleExpanded() {
	isExpanded.value = !isExpanded.value;
}

onMounted(() => {
	fetchCategories();
});

watch(newCommandGroupsModalKey, () => {
	// if the add new category dialog is closed, fetch categories again
	fetchCategories();
});

// Watch selected categories and reset expanded state if we have 3 or fewer categories
watch(
	selectedCats,
	(newSelectedCats) => {
		if (newSelectedCats.length <= 3) {
			isExpanded.value = false;
		}
	},
	{ deep: true }
);

function fetchCategories() {
	isLoading.value = true;
	axios.get("/api/categories/list").then((response) => {
		categories.value = response.data.data;
		isLoading.value = false;
	});
}
</script>

<template>
	<!-- DIV FOR RENDERING THE BADGE COLOR CLASSES -->
	<Popover>
		<div class="hidden text-yellow-200 text-teal-100 bg-yellow-700 bg-teal-700 border-yellow-500 border-teal-500 bg-stone-700 text-stone-200 border-stone-500 bg-lime-700 text-lime-200 border-lime-500 bg-sky-700 text-sky-100 border-sky-500 bg-violet-700 text-violet-200 border-violet-500 bg-fuchsia-700 text-fuchsia-200 border-fuchsia-500"></div>

		<PopoverTrigger class="col-span-3">
			<Button variant="ghost" class="flex items-start justify-start w-full px-2 py-1 border rounded-xl gap-1" :class="[selectedCats.length === 0 ? 'text-rcgray-400 h-fit whitespace-nowrap' : 'h-auto min-h-fit bg-rcgray-700']" :style="selectedCats.length === 0 ? 'padding: 0.45rem' : 'padding: 0.2rem'">
				<span v-if="isLoading">Loading command groups...</span>
				<template v-else class="text-rcgray-400">
					<RcIcon name="command-group" class="mx-2" />

					<span v-if="selectedCats.length === 0">{{ props.placeholder }}</span>

					<!-- Display single selected item -->
					<span v-else-if="props.singleSelect && selectedCats.length > 0" :class="selectedCats[0].badgeColor ? selectedCats[0].badgeColor : 'bg-gray-600 text-gray-200 border-gray-500'" class="flex items-center text-xs font-medium px-2.5 py-0.5 rounded-xl border bg-muted">
						{{ selectedCats[0].categoryName }}
						<X :size="16" class="ml-1 cursor-pointer hover:text-primary" @click.stop="deleteItemById(selectedCats[0].id)" />
					</span>

					<!-- Display multiple selected items -->
					<div v-if="!props.singleSelect && selectedCats.length > 0" class="flex flex-wrap gap-1 flex-1 overflow-hidden">
						<!-- Show first 3 categories or all categories based on expanded state -->
						<span v-for="cat in isExpanded ? selectedCats : selectedCats.slice(0, 3)" :key="cat.id" class="relative group flex-shrink-0">
							<span :class="cat.badgeColor ? cat.badgeColor : 'bg-gray-600 text-gray-200 border-gray-500'" class="flex items-center text-xs font-medium px-2.5 py-0.5 rounded-xl border whitespace-nowrap max-w-[150px]">
								<span class="truncate">{{ cat.categoryName }}</span>
								<X :size="16" class="ml-1 cursor-pointer hover:text-white flex-shrink-0" @click.stop="deleteItemById(cat.id)" />
							</span>
						</span>
						<!-- Show expandable button if there are more than 3 selected and not expanded -->
						<button v-if="selectedCats.length > 3 && !isExpanded" @click.stop="toggleExpanded()" class="flex items-center text-xs font-medium px-2.5 py-0.5 rounded-xl border bg-muted text-muted-foreground flex-shrink-0 hover:bg-muted/80 transition-colors cursor-pointer rc-btn-shadow">+{{ selectedCats.length - 3 }} more</button>
						<!-- Show collapse button when expanded and there are more than 3 items -->
						<button v-if="selectedCats.length > 3 && isExpanded" @click.stop="toggleExpanded()" class="flex items-center text-xs font-medium px-2.5 py-0.5 rounded-xl border bg-muted text-muted-foreground flex-shrink-0 hover:bg-muted/80 transition-colors cursor-pointer rc-btn-shadow">
							Show less
						</button>
					</div>
				</template>
			</Button>
		</PopoverTrigger>

		<PopoverContent side="bottom" align="start" class="col-span-3 p-0">
			<div class="relative items-center w-full">
				<Input id="search" type="text" v-model="searchTerm" autocomplete="off" placeholder="Search..." class="pl-10 border-none focus:outline-none focus-visible:ring-0 text-muted-foreground font-inter" />
				<span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
					<RcIcon name="search" />
				</span>
			</div>
			<Separator />

			<ScrollArea class="h-64">
				<div class="py-1">
					<RcIcon name="three-dots-loading" class="w-8 h-8 mx-auto my-4 text-muted-foreground" v-if="isLoading" />

					<template v-else>
						<!-- Command groups with commands -->
						<div v-for="cat in groupedCategories.withCommands" :key="cat.id" class="w-full p-1 pl-2 my-1 text-sm rounded-lg hover:bg-rcgray-600" @click="selectItem(cat)">
							<div class="flex items-center justify-between">
								<span data-size="20" class="cursor-default text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border" :class="cat.badgeColor ? cat.badgeColor : 'bg-gray-600 text-gray-200 border-gray-500'">
									<span data-size="20">
										{{ cat.categoryName }}
									</span>
								</span>
							</div>
						</div>

						<!-- Separator and heading for command groups without commands -->
						<template v-if="groupedCategories.withoutCommands.length > 0">
							<Separator class="my-2" />
							<div class="px-2 py-1 text-xs text-muted-foreground font-medium">
								No commands assigned
							</div>

							<div v-for="cat in groupedCategories.withoutCommands" :key="cat.id" class="w-full p-1 pl-2 my-1 text-sm rounded-lg hover:bg-rcgray-600" @click="selectItem(cat)">
								<div class="flex items-center justify-between">
									<span data-size="20" class="cursor-default text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border" :class="cat.badgeColor ? cat.badgeColor : 'bg-gray-600 text-gray-200 border-gray-500'">
										<span data-size="20">
											{{ cat.categoryName }}
										</span>
									</span>
									<TooltipProvider>
										<Tooltip>
											<TooltipTrigger as-child>
												<CloudAlert :size="14" class="mr-4 text-yellow-300 cursor-default" />
											</TooltipTrigger>
											<TooltipContent class="text-white bg-rcgray-800 border border-blue-500/30">
												<p>This command group does not have any commands assigned to it</p>
											</TooltipContent>
										</Tooltip>
									</TooltipProvider>
								</div>
							</div>
						</template>
					</template>
				</div>
			</ScrollArea>

			<Separator />

			<div class="p-1 border-5">
				<Button variant="ghost" @click="viewEditDialog(0)" class="flex items-center justify-start w-full p-1">
					<RcIcon name="plus" class="w-8" />
					<div class="rc-text-xs-muted ml-1">Create new record</div>
				</Button>
			</div>
		</PopoverContent>

		<CommandGroupAddEditDialog @save="handleSave()" :key="newCommandGroupsModalKey" :editId="0" />
	</Popover>
</template>
