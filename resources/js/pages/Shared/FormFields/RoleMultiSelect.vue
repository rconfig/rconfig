<script setup lang="ts">
import axios from "axios";
import Spinner from "@/pages/Shared/Icon/Spinner.vue";
import { onMounted, ref } from "vue";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { ScrollArea } from "@/components/ui/scroll-area";
import { X, Search } from "lucide-vue-next";
import { useMultiSelect } from "./useMultiSelect.js";

const emit = defineEmits(["update:modelValue"]);
const roles = ref([]);
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
		default: "Select roles",
	},
});

const { selectedItems: selectedRoles, open, searchTerm, filteredItems: filteredRoles, selectItem, deleteItem } = useMultiSelect({
	items: roles,
	modelValue: props.modelValue,
	singleSelect: props.singleSelect,
	displayField: "name",
	searchFields: ["name", "ip_address"],
	emit,
});

onMounted(() => {
	fetchRoles();
});

function fetchRoles() {
	isLoading.value = true;
	axios
		.get("/api/roles", {
			params: {
				perPage: 100, // Limiting to a reasonable number
			},
		})
		.then((response) => {
			// Simplified role data - just id and name
			roles.value = response.data.data.map((role) => ({
				id: role.id,
				name: role.name || `Role #${role.id}`,
				ip_address: role.ip_address,
			}));
		})
		.catch((error) => {
			console.error("Failed to fetch roles:", error);
		})
		.finally(() => {
			isLoading.value = false;
		});
}
</script>

<template>
	<Popover v-model:open="open">
		<PopoverTrigger class="col-span-3">
			<Button variant="ghost" class="flex items-center justify-start w-full px-2 py-1 border rounded-xl whitespace-nowrap h-fit bg-rcgray-700" :class="selectedRoles.length === 0 ? ' text-rcgray-400' : ''" :style="selectedRoles.length === 0 ? 'padding: 0.45rem' : 'padding: 0.2rem'">
				<span v-if="isLoading">Loading roles...</span>
				<template v-else class="text-rcgray-400">
					<RcIcon name="rbac" class="mx-2" />

					<span v-if="selectedRoles.length === 0">{{ props.placeholder }}</span>

					<!-- Display single selected item -->
					<span v-else-if="props.singleSelect && selectedRoles.length > 0" class="flex items-center text-xs font-medium px-2.5 py-0.5 rounded-xl border bg-muted">
						{{ selectedRoles[0].id }} - {{ selectedRoles[0].name }}
						<X size="16" class="ml-1 cursor-pointer hover:text-primary" @click.stop="deleteItem(selectedRoles[0].id)" />
					</span>

					<!-- Display multiple selected items -->
					<template v-else>
						<span v-for="role in selectedRoles" :key="role.id" class="relative my-1 group">
							<span class="flex items-center text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border bg-muted">
								{{ role.name }}
								<X size="16" class="ml-1 cursor-pointer hover:text-primary" @click.stop="deleteItem(role.id)" />
							</span>
						</span>
					</template>
				</template>
			</Button>
		</PopoverTrigger>

		<PopoverContent side="bottom" align="start" class="col-span-3 p-0 w-72">
			<div class="relative items-center w-full">
				<Input id="search" type="text" v-model="searchTerm" autocomplete="off" placeholder="Search roles..." class="pl-10 border-none focus:outline-none focus-visible:ring-0 text-muted-foreground font-inter" />
				<span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
					<Search class="size-6 text-muted-foreground" />
				</span>
			</div>
			<Separator />

			<ScrollArea class="h-64">
				<div v-if="isLoading" class="flex items-center justify-center p-4">
					<Spinner class="animate-spin w-5 h-5 mr-2" />
					Loading roles...
				</div>
				<div v-else-if="filteredRoles.length === 0 && searchTerm" class="p-4 text-center text-sm text-muted-foreground">No roles found matching "{{ searchTerm }}"</div>
				<div v-else-if="filteredRoles.length === 0" class="p-4 text-center text-sm text-muted-foreground">
					No roles available
				</div>
				<div v-else class="py-1">
					<div v-for="role in filteredRoles" :key="role.id" class="w-full p-2 my-1 text-sm rounded-lg hover:bg-rcgray-600 cursor-pointer" @click="selectItem(role)">
						<div class="flex items-center">
							<span class="font-medium">{{ role.id }} - {{ role.name }}</span>

							<span class="ml-2 text-xs text-muted-foreground truncate">
								{{ role.ip_address }}
							</span>
						</div>
					</div>
				</div>
			</ScrollArea>
		</PopoverContent>
	</Popover>
</template>
