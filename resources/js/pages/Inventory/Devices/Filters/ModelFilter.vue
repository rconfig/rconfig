<script setup lang="ts">
import axios from "axios";
import { onMounted, ref, watch, computed } from "vue";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { ScrollArea } from "@/components/ui/scroll-area";

const emit = defineEmits(["update:modelValue"]);
const options = ref([]);
const open = ref(false);
const selectedDeviceModel = ref([]);
const searchTerm = ref("");
const allSelected = ref(false);

const props = defineProps({
	modelValue: {
		type: Array,
		required: true,
	},
});

onMounted(() => {
	fetchDeviceModels();
});

// Watch for changes to the prop and update internalModel
watch(
	() => props.modelValue,
	(newValue) => {
		selectedDeviceModel.value = newValue;
		allSelected.value = selectedDeviceModel.value.length === options.value.length;
	}
);

function selectItem(item) {
	if (item.id === 9999999) {
		// If 'All' is selected, toggle selection state
		if (allSelected.value) {
			// If all items are already selected, remove all
			selectedDeviceModel.value = [];
			allSelected.value = false;
		} else {
			// Select all items
			selectedDeviceModel.value = [...options.value];
			allSelected.value = true;
		}
	} else {
		const existingIndex = selectedDeviceModel.value.findIndex((model) => model.id === item.id);
		if (existingIndex !== -1) {
			// If item exists, remove it
			selectedDeviceModel.value.splice(existingIndex, 1);
			allSelected.value = false;
		} else {
			// If item does not exist, add it
			selectedDeviceModel.value.push(item);
			if (selectedDeviceModel.value.length === options.value.length) {
				allSelected.value = true;
			}
		}
	}
	open.value = false;
	emit("update:modelValue", selectedDeviceModel.value);
}

function fetchDeviceModels() {
	axios.get("/api/device-models/?perPage=10000&sort=name").then((response) => {
		options.value = response.data.data;
	});
}

const filteredDeviceModels = computed(() => {
	return options.value.filter(
		(model) => model.name.toLowerCase().includes(searchTerm.value.toLowerCase()) // Prevent displaying already selected items
	);
});
</script>

<template>
	<Popover>
		<PopoverTrigger>
			<Button variant="ghost" class="flex items-center justify-center w-full px-2 py-1 border rounded-xl whitespace-nowrap h-fit bg-rcgray-700 text-rcgray-400">
				<RcIcon name="model" class="lg:mr-2" />

				<div class="hidden lg:inline-flex">
					<template v-if="selectedDeviceModel && selectedDeviceModel.length === 0">Device Model</template>
					<template v-else>
						<span class="text-sm font-light" v-if="selectedDeviceModel.length > 0">
							Device Model
							<strong class="text-sm font-semibold">{{ selectedDeviceModel.length }} Selected</strong>
						</span>
					</template>
				</div>
			</Button>
		</PopoverTrigger>
		<PopoverContent side="bottom" align="start" class="w-64 p-0">
			<div class="relative items-center w-full">
				<Input id="search" type="text" v-model="searchTerm" placeholder="Search" class="pl-10 border-none focus:outline-none focus-visible:ring-0 text-muted-foreground font-inter" />
				<span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
					<RcIcon name="search" />
				</span>
			</div>
			<Separator />
			<ScrollArea class="h-44">
				<div class="py-1">
					<div v-for="option in filteredDeviceModels" :key="option.id" class="w-full p-1 pl-2 my-1 text-sm rounded-lg hover:bg-rcgray-600" @click="selectItem(option)">
						<input type="checkbox" :checked="selectedDeviceModel.some((model) => model.id === option.id)" class="mr-2" />
						<span data-size="20" class="cursor-default text-xs font-medium me-2 px-2.5 py-0.5">
							<span data-size="20">
								{{ option.name }}
							</span>
						</span>
					</div>
				</div>
			</ScrollArea>
			<Separator />

			<div class="p-1 border-5">
				<Button variant="ghost" class="justify-start w-full p-1" @click="selectItem({ id: 9999999 })">
					<RcIcon name="select-all" :isSelected="allSelected" size="16" class="mr-2" />

					<span>{{ allSelected ? 'Deselect All' : 'Select All' }}</span>
				</Button>
			</div>
		</PopoverContent>
	</Popover>
</template>