<script setup lang="ts">
import CredentialsAddEditDialog from "@/pages/Settings/Panels/Components/CredentialsAddEditDialog.vue";
import axios from "axios";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { ScrollArea } from "@/components/ui/scroll-area";
import { onMounted, ref, watch } from "vue";
import { useCredentials } from "@/pages/Settings/Panels/Components/useCredentials";
import { X, Search, Plus } from "lucide-vue-next";
import { useMultiSelect } from "./useMultiSelect.js";

const emit = defineEmits(["update:modelValue"]);
const credentials = ref([]);
const isLoading = ref(true);

const { createCred, newCredModalKey, handleSave } = useCredentials({
	autoMount: false, // Prevent auto-mounting to avoid unnecessary API calls
});

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
		default: "Select credentials",
	},
	align: {
		type: String,
		default: "end", // Options: 'start', 'center', 'end'
	},
});

const { selectedItems: selectedCreds, open, searchTerm, filteredItems: filteredCredentials, selectItem, deleteItem: deleteItemById } = useMultiSelect({
	items: credentials,
	modelValue: props.modelValue,
	singleSelect: props.singleSelect,
	displayField: "cred_name",
	searchFields: ["cred_name"],
	emit,
});

onMounted(() => {
	fetchCredentials();
});

watch(newCredModalKey, () => {
	// if the add new category dialog is closed, fetch Tags again
	fetchCredentials();
});

function fetchCredentials() {
	isLoading.value = true;
	axios.get("/api/settings/credentials/?perPage=10000").then((response) => {
		credentials.value = response.data.data;
		isLoading.value = false;
	});
}
</script>

<template>
	<!-- DIV FOR RENDERING THE BADGE COLOR CLASSES -->
	<Popover>
		<div class="hidden text-yellow-200 text-teal-100 bg-yellow-700 bg-teal-700 border-yellow-500 border-teal-500 bg-stone-700 text-stone-200 border-stone-500 bg-lime-700 text-lime-200 border-lime-500 bg-sky-700 text-sky-100 border-sky-500 bg-violet-700 text-violet-200 border-violet-500 bg-fuchsia-700 text-fuchsia-200 border-fuchsia-500"></div>
		<PopoverTrigger class="col-span-3">
			<Button variant="ghost" class="flex items-center justify-start w-full px-2 py-1 border rounded-xl whitespace-nowrap h-fit bg-rcgray-700" :class="selectedCreds.length === 0 ? ' text-rcgray-400' : ''" :style="selectedCreds.length === 0 ? 'padding: 0.45rem' : 'padding: 0.2rem'">
				<span v-if="isLoading">Loading credentials...</span>
				<template v-else class="text-rcgray-400">
					<RcIcon name="credentials" class="mx-2" />

					<span v-if="selectedCreds.length === 0">{{ props.placeholder }}</span>

					<!-- Display single selected item -->
					<span v-else-if="props.singleSelect && selectedCreds.length > 0" class="flex items-center text-xs font-medium px-2.5 py-0.5 rounded-xl border bg-muted">
						{{ selectedCreds[0].cred_name }}
						<X size="16" class="ml-1 cursor-pointer hover:text-primary" @click.stop="deleteItemById(selectedCreds[0].id)" />
					</span>

					<!-- Display multiple selected items -->
					<template v-else>
						<span v-for="cred in selectedCreds" :key="cred.id" class="relative my-1 group">
							<span class="flex items-center text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border">
								{{ cred.cred_name }}

								<X size="16" class="ml-1 cursor-pointer hover:text-white" @click.stop="deleteItemById(cred.id)" />
							</span>
						</span>
					</template>
				</template>
			</Button>
		</PopoverTrigger>
		<PopoverContent side="bottom" :align="align" class="col-span-3 p-0">
			<div class="relative items-center w-full">
				<Input id="search" type="text" v-model="searchTerm" autocomplete="off" placeholder="Search..." class="pl-10 border-none focus:outline-none focus-visible:ring-0 text-muted-foreground font-inter" />
				<span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
					<Search class="size-6 text-muted-foreground" />
				</span>
			</div>
			<Separator />

			<ScrollArea class="h-64">
				<div class="py-1">
					<RcIcon name="three-dots-loading" class="w-8 h-8 mx-auto my-4 text-muted-foreground" v-if="isLoading" />

					<div v-else v-for="cred in filteredCredentials" :key="cred.id" class="w-full p-1 pl-2 my-1 text-sm rounded-lg hover:bg-rcgray-600" @click="selectItem(cred)">
						<span data-size="20" class="cursor-default text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border" :class="cred.badgeColor ? cred.badgeColor : 'bg-gray-600 text-gray-200 border-gray-500'">
							<span data-size="20">
								{{ cred.cred_name }}
							</span>
						</span>
					</div>
				</div>
			</ScrollArea>

			<Separator />

			<div class="p-1 border-5">
				<Button variant="ghost" @click="createCred" class="justify-start w-full p-1">
					<RcIcon name="plus" class="w-8" />
					<div class="rc-text-xs-muted ml-1">Create new record</div>
				</Button>
			</div>
		</PopoverContent>

		<CredentialsAddEditDialog @save="handleSave()" :key="newCredModalKey" :editId="0" />
	</Popover>
</template>
