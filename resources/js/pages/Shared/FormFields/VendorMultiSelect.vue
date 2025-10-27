<script setup lang="ts">
import VendorAddEditDialog from "@/pages/Inventory/Vendors/VendorAddEditDialog.vue";
import axios from "axios";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { ScrollArea } from "@/components/ui/scroll-area";
import { X } from "lucide-vue-next";
import { onMounted, ref, watch } from "vue";
import { useVendors } from "@/pages/Inventory/Vendors/useVendors";
import { useMultiSelect } from "./useMultiSelect.js";

const emit = defineEmits(["update:modelValue"]);
const vendors = ref([]);
const { createVendor, newVendorModalKey, handleSave } = useVendors();
const isLoading = ref(true);

const props = defineProps({
	modelValue: {
		//Must be an object or array of objects
		type: [Array, Object],
		required: true,
	},
	singleSelect: {
		type: Boolean,
		default: false,
	},
	placeholder: {
		type: String,
		default: "Select a vendor",
	},
});

const { selectedItems: selectedVendors, open, searchTerm, filteredItems: filteredVendors, selectItem, deleteItem: deleteItemById } = useMultiSelect({
	items: vendors,
	modelValue: props.modelValue,
	singleSelect: props.singleSelect,
	displayField: "vendorName",
	searchFields: ["vendorName"],
	emit,
});

onMounted(() => {
	fetchVendors();
});

watch(newVendorModalKey, () => {
	// if the add new category dialog is closed, fetch categories again
	fetchVendors();
});

function fetchVendors() {
	isLoading.value = true;
	axios.get("/api/vendors/?perPage=10000&sort=vendorName").then((response) => {
		vendors.value = response.data.data;
		isLoading.value = false;
	});
}
</script>

<template>
	<Popover>
		<PopoverTrigger class="col-span-3">
			<Button variant="ghost" class="flex flex-wrap items-start justify-start w-full pl-2 whitespace-normal border h-fit" :class="selectedVendors.length === 0 ? 'text-muted-foreground' : ' '" :style="selectedVendors.length === 0 ? 'padding: 0.45rem' : 'padding: 0.2rem'">
				<span v-if="isLoading">Loading vendors...</span>
				<!-- Padding is 0.45rem to match Inputs and adjustment when adding templates -->
				<div v-else class="flex items-center justify-start w-full">
					<RcIcon name="vendor" class="mx-2" />
					{{ selectedVendors.length === 0 ? "Selected a vendor" : "" }}

					<span v-for="template in selectedVendors" :key="template.id" class="relative my-1 group">
						<span class="flex items-center text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border">
							{{ template.vendorName }}
							<X size="10" class="ml-1 cursor-pointer text-rcgray-300 hover:text-white" @click.stop="deleteItemById(template.id)" />
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
					<div v-else v-for="vendor in filteredVendors" :key="vendor.id" class="w-full p-1 pl-2 my-1 text-sm rounded-lg hover:bg-rcgray-600" @click="selectItem(vendor)">
						<span data-size="20" class="cursor-default text-xs font-medium me-2 px-2.5 py-0.5">
							<span data-size="20">
								{{ vendor.vendorName }}
							</span>
						</span>
					</div>
				</div>
			</ScrollArea>

			<Separator />

			<div class="p-1 border-5">
				<Button variant="ghost" @click="createVendor()" class="justify-start w-full p-1">
					<RcIcon name="plus" class="w-8" />
					<div class="rc-text-xs-muted ml-1">Create new record</div>
				</Button>
			</div>
		</PopoverContent>

		<VendorAddEditDialog @save="handleSave()" :key="newVendorModalKey" :editId="0" />
	</Popover>
</template>
