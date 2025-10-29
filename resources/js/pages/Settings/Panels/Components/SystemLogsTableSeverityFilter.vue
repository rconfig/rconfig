<script setup lang="ts">
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { Activity } from "lucide-vue-next";
import { ScrollArea } from "@/components/ui/scroll-area";
import { computed, ref, watch } from "vue";

const emit = defineEmits(["update:modelValue"]);
const options = ref([
	{ id: 0, name: "Critical" },
	{ id: 1, name: "Error" },
	{ id: 2, name: "Warn" },
	{ id: 3, name: "Info" },
	{ id: 4, name: "Debug" },
	{ id: 200, name: "All" },
]);
const open = ref(false);
const selectedSeverity = ref([]);

const props = defineProps({
	modelValue: {
		type: Array,
		required: true,
	},
});

// Watch for changes to the prop and update internalModel
watch(
	() => props.modelValue,
	(newValue) => {
		selectedSeverity.value = newValue;
	}
);

function selectItem(item) {
	if (item.id === 200) {
		// If 'All' is selected
		if (selectedSeverity.value.length === options.value.length) {
			// If all items including 'All' are already selected, remove all
			selectedSeverity.value = [];
		} else {
			// Select all items including 'All'
			selectedSeverity.value = [...options.value];
		}
	} else {
		const existingIndex = selectedSeverity.value.findIndex((tag) => tag.id === item.id);
		if (existingIndex !== -1) {
			// If item exists, remove it
			selectedSeverity.value.splice(existingIndex, 1);
			// If 'All' was selected and an item is deselected, remove 'All'
			const allIndex = selectedSeverity.value.findIndex((tag) => tag.id === 200);
			if (allIndex !== -1) {
				selectedSeverity.value.splice(allIndex, 1);
			}
		} else {
			// If item does not exist, add it
			selectedSeverity.value.push(item);
		}
	}
	open.value = false;
	emit("update:modelValue", selectedSeverity.value);
}
</script>

<template>
	<Popover>
		<PopoverTrigger>
			<Button variant="ghost" class="flex items-center justify-center w-full h-full px-2 py-1 border rounded-xl whitespace-nowrap bg-rcgray-700 text-rcgray-400">
				<Activity class="w-4 h-4 mr-2 text-amber-400 activity-pulse" />

				<template v-if="selectedSeverity && selectedSeverity.length === 0">Severity</template>
				<template v-else>
					<span class="text-sm font-light" v-if="selectedSeverity.length > 0">
						Severity
						<strong class="text-sm font-semibold">{{ selectedSeverity.length }} selected</strong>
					</span>
				</template>
			</Button>
		</PopoverTrigger>
		<PopoverContent side="bottom" align="start" class="p-0 w-44">
			<ScrollArea class="h-50">
				<div class="py-1">
					<div v-for="option in options" :key="option.id" class="w-full p-1 pl-2 my-1 text-sm rounded-lg hover:bg-rcgray-600" @click="selectItem(option)">
						<input type="checkbox" :checked="selectedSeverity.some((cat) => cat.id === option.id)" class="mr-2" />
						<span data-size="20" class="cursor-default text-xs font-medium me-2 px-2.5 py-0.5">
							<span data-size="20">
								{{ option.name }}
							</span>
						</span>
					</div>
				</div>
			</ScrollArea>
			<!-- <Separator />

      <div class="flex justify-between gap-4 p-2 border-5">
        <Button
          variant="ghost"
          class="h-6 py-1 text-sm">
          <span>Cancel</span>
        </Button>
        <Button
          variant="ghost"
          class="h-6 px-4 text-sm bg-blue-600 hover:bg-blue-500">
          <span>Apply</span>
        </Button>
      </div> -->
		</PopoverContent>
	</Popover>
</template>

<style scoped>
.activity-pulse {
	animation: pulse 3s infinite ease-in-out;
}

@keyframes pulse {
	0% {
		opacity: 0.7;
		transform: scale(1);
	}
	50% {
		opacity: 1;
		transform: scale(1.1);
	}
	100% {
		opacity: 0.7;
		transform: scale(1);
	}
}
</style>