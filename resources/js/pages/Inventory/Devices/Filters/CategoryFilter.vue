<script setup lang="ts">
import axios from "axios";
import { onMounted, ref, watch, computed } from "vue";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Separator } from "@/components/ui/separator";

const emit = defineEmits(["update:modelValue"]);
const options = ref([]);
const open = ref(false);
const selectedCats = ref([]);
const searchTerm = ref("");
const allSelected = ref(false);

const props = defineProps({
	modelValue: {
		type: Array,
		required: true,
	},
});

onMounted(() => {
	fetchCategories();
});

// Watch for changes to the prop and update internalModel
watch(
	() => props.modelValue,
	(newValue) => {
		selectedCats.value = newValue;
		allSelected.value = selectedCats.value.length === options.value.length;
	}
);

function selectItem(item) {
	if (item.id === 9999999) {
		// If 'All' is selected, toggle selection state
		if (allSelected.value) {
			// If all items are already selected, remove all
			selectedCats.value = [];
			allSelected.value = false;
		} else {
			// Select all items
			selectedCats.value = [...options.value];
			allSelected.value = true;
		}
	} else {
		const existingIndex = selectedCats.value.findIndex((tag) => tag.id === item.id);
		if (existingIndex !== -1) {
			// If item exists, remove it
			selectedCats.value.splice(existingIndex, 1);
			allSelected.value = false;
		} else {
			// If item does not exist, add it
			selectedCats.value.push(item);
			if (selectedCats.value.length === options.value.length) {
				allSelected.value = true;
			}
		}
	}
	open.value = false;
	emit("update:modelValue", selectedCats.value);
}

function fetchCategories() {
	axios.get("/api/categories/?perPage=10000&sort=categoryName").then((response) => {
		options.value = response.data.data;
	});
}

const filteredCategories = computed(() => {
	return options.value.filter(
		(cat) => cat.categoryName.toLowerCase().includes(searchTerm.value.toLowerCase()) // Prevent displaying already selected items
	);
});
</script>

<template>
	<Popover>
		<PopoverTrigger>
			<Button
				variant="ghost"
				class="flex items-center justify-center w-full px-2 py-1 border rounded-xl whitespace-nowrap h-fit bg-rcgray-700 text-rcgray-400"
			>
				<RcIcon
					name="command-group"
					class="lg:mr-2"
				/>

				<div class="hidden lg:inline-flex">
					<template v-if="selectedCats && selectedCats.length === 0">
						Command Group
					</template>
					<template v-else>
						<span
							v-if="selectedCats.length > 0"
							class="text-sm font-light"
						>
							Command Group
							<strong class="text-sm font-semibold">{{ selectedCats.length }} Selected</strong>
						</span>
					</template>
				</div>
			</Button>
		</PopoverTrigger>
		<PopoverContent
			side="bottom"
			align="start"
			class="w-64 p-0"
		>
			<div class="relative items-center w-full">
				<Input
					id="search"
					v-model="searchTerm"
					type="text"
					placeholder="Search"
					class="pl-10 border-none focus:outline-none focus-visible:ring-0 text-muted-foreground font-inter"
				/>
				<span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
					<RcIcon name="search" />
				</span>
			</div>
			<Separator />
			<ScrollArea class="h-44">
				<div class="py-1">
					<div
						v-for="option in filteredCategories"
						:key="option.id"
						class="w-full p-1 pl-2 my-1 text-sm rounded-lg hover:bg-rcgray-600"
						@click="selectItem(option)"
					>
						<input
							type="checkbox"
							:checked="selectedCats.some((cat) => cat.id === option.id)"
							class="mr-2"
						/>
						<span
							data-size="20"
							class="cursor-default text-xs font-medium me-2 px-2.5 py-0.5"
						>
							<span data-size="20">
								{{ option.categoryName }}
							</span>
						</span>
					</div>
				</div>
			</ScrollArea>
			<Separator />

			<div class="p-1 border-5">
				<Button
					variant="ghost"
					class="justify-start w-full p-1"
					@click="selectItem({ id: 9999999 })"
				>
					<RcIcon
						name="select-all"
						:is-selected="allSelected"
						size="16"
						class="mr-2"
					/>

					<span>{{ allSelected ? 'Deselect All' : 'Select All' }}</span>
				</Button>
			</div>
		</PopoverContent>
	</Popover>
</template>