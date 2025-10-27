<script setup lang="ts">
import axios from "axios";
import Spinner from "@/pages/Shared/Icon/Spinner.vue";
import { onMounted, ref } from "vue";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { ScrollArea } from "@/components/ui/scroll-area";
import { X, Search } from "lucide-vue-next";
import { useMultiSelect } from "./useMultiSelect.js";

const emit = defineEmits(["update:modelValue"]);
const agents = ref([]);
const isLoading = ref(false);

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
		default: "Select agents",
	},
});

const { selectedItems: selectedAgents, open, searchTerm, filteredItems: filteredAgents, selectItem, deleteItem } = useMultiSelect({
	items: agents,
	modelValue: props.modelValue,
	singleSelect: props.singleSelect,
	displayField: "name",
	searchFields: ["name", "ip_address"],
	emit,
});

onMounted(() => {
	fetchAgents();
});

function fetchAgents() {
	isLoading.value = true;
	axios
		.get("/api/agents", {
			params: {
				perPage: 100, // Limiting to a reasonable number
				"filter[is_admin_enabled]": 1, // Only fetch enabled agents
			},
		})
		.then((response) => {
			// Simplified agent data - just id and name
			agents.value = response.data.data.map((agent) => ({
				id: agent.id,
				name: agent.name || `Agent #${agent.id}`,
				ip_address: agent.ip_address,
			}));
		})
		.catch((error) => {
			console.error("Failed to fetch agents:", error);
		})
		.finally(() => {
			isLoading.value = false;
		});
}
</script>

<template>
	<Popover v-model:open="open">
		<PopoverTrigger class="col-span-3">
			<Button variant="ghost" class="flex items-center justify-start w-full px-2 mr-2 py-1 border rounded-xl whitespace-nowrap h-fit bg-rcgray-700" :class="selectedAgents.length === 0 ? ' text-rcgray-400' : ''" :style="selectedAgents.length === 0 ? 'padding: 0.45rem' : 'padding: 0.2rem'">
				<span v-if="isLoading">Loading agents...</span>
				<template v-else class="text-rcgray-400">
					<RcIcon name="vector-agent" class="mx-2" />

					<span v-if="selectedAgents.length === 0">{{ props.placeholder }}</span>

					<!-- Display single selected item -->
					<span v-else-if="props.singleSelect && selectedAgents.length > 0" class="flex items-center text-xs font-medium px-2.5 py-0.5 rounded-xl border bg-muted">
						{{ selectedAgents[0].id }} - {{ selectedAgents[0].name }}
						<X size="16" class="ml-1 cursor-pointer hover:text-primary" @click.stop="deleteItem(selectedAgents[0].id)" />
					</span>

					<!-- Display multiple selected items -->
					<template v-else>
						<span v-for="agent in selectedAgents" :key="agent.id" class="relative my-1 group">
							<span class="flex items-center text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border bg-muted">
								{{ agent.id }} - {{ agent.name }}
								<X size="16" class="ml-1 cursor-pointer hover:text-primary" @click.stop="deleteItem(agent.id)" />
							</span>
						</span>
					</template>
				</template>
			</Button>
		</PopoverTrigger>

		<PopoverContent side="bottom" align="start" class="col-span-3 p-0 w-72">
			<div class="relative items-center w-full">
				<Input id="search" type="text" v-model="searchTerm" autocomplete="off" placeholder="Search agents..." class="pl-10 border-none focus:outline-none focus-visible:ring-0 text-muted-foreground font-inter" />
				<span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
					<Search class="size-6 text-muted-foreground" />
				</span>
			</div>
			<Separator />

			<ScrollArea class="h-64">
				<div v-if="isLoading" class="flex items-center justify-center p-4">
					<Spinner class="animate-spin w-5 h-5 mr-2" />
					Loading agents...
				</div>
				<div v-else-if="filteredAgents.length === 0 && searchTerm" class="p-4 text-center text-sm text-muted-foreground">No agents found matching "{{ searchTerm }}"</div>
				<div v-else-if="filteredAgents.length === 0" class="p-4 text-center text-sm text-muted-foreground">
					No agents available
				</div>
				<div v-else class="py-1">
					<div v-for="agent in filteredAgents" :key="agent.id" class="w-full p-2 my-1 text-sm rounded-lg hover:bg-rcgray-600 cursor-pointer" @click="selectItem(agent)">
						<div class="flex items-center">
							<span class="font-medium">{{ agent.id }} - {{ agent.name }}</span>

							<span class="ml-2 text-xs text-muted-foreground truncate">
								{{ agent.ip_address }}
							</span>
						</div>
					</div>
				</div>
			</ScrollArea>
		</PopoverContent>
	</Popover>
</template>
