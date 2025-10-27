<script setup>
import axios from "axios";
import { onMounted, ref } from "vue";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { ScrollArea } from "@/components/ui/scroll-area";
import { X } from "lucide-vue-next";
import { useMultiSelect } from "./useMultiSelect.js";

const emit = defineEmits(["update:modelValue"]);
const commands = ref([]);
const isLoading = ref(true);

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
		default: "Select commands",
	},
});

const { selectedItems: selectedCommands, open, searchTerm, filteredItems: filteredCommands, selectItem, deleteItem: deleteItemById } = useMultiSelect({
	items: commands,
	modelValue: props.modelValue,
	singleSelect: props.singleSelect,
	displayField: "command",
	searchFields: ["command"],
	emit,
});

// Custom deleteItem function that uses command instead of ID
function deleteItem(commandName) {
	const item = selectedCommands.value.find((cmd) => cmd.command === commandName);
	if (item) {
		deleteItemById(item.id);
	}
}

onMounted(() => {
	fetchCommands();
});

function fetchCommands() {
	isLoading.value = true;
	axios.get("/api/commands/?perPage=10000").then((response) => {
		commands.value = response.data.data;
		isLoading.value = false;
	});
}
</script>

<template>
	<Popover>
		<PopoverTrigger class="w-full">
			<Button variant="ghost" class="flex flex-wrap items-center justify-start w-full p-1 pl-2 border h-fit gap-1" :class="selectedCommands.length === 0 ? 'text-muted-foreground' : ''">
				<RcIcon name="commands" class="flex-shrink-0 mx-2" />
				<span v-if="selectedCommands && selectedCommands.length === 0" class="flex-1 text-left mr-2">
					Select commands
				</span>
				<div v-else class="flex flex-wrap gap-1 flex-1">
					<span v-for="cmd in selectedCommands" :key="cmd.id" class="relative group">
						<span class="flex items-center text-xs font-medium px-2.5 py-0.5 rounded-xl border whitespace-nowrap">
							{{ cmd.command }}
							<X size="10" class="ml-1 cursor-pointer text-rcgray-300 hover:text-white flex-shrink-0" @click.stop="deleteItem(cmd.command)" />
						</span>
					</span>
				</div>
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

					<div v-else v-for="cmd in filteredCommands" :key="cmd.id" class="w-full p-1 pl-2 my-1 text-sm rounded-lg hover:bg-rcgray-600" @click="selectItem(cmd)">
						<span data-size="20" class="cursor-default text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border">
							<span data-size="20">
								{{ cmd.command }}
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
