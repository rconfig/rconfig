<script setup>
import DeviceModelAddDialog from "@/pages/Shared/FormFields/DeviceModelAddDialog.vue";
import DeviceModelMultiSelectI18N from "@/i18n/pages/Shared/FormFields/DeviceModelMultiSelect.i18n.js";
import axios from "axios";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { ScrollArea } from "@/components/ui/scroll-area";
import { X } from "lucide-vue-next";
import { onMounted, ref } from "vue";
import { useMultiSelect } from "./useMultiSelect.js";
import { useComponentTranslations } from "@/composables/useComponentTranslations";

const emit = defineEmits(["update:modelValue"]);
const DeviceModels = ref([]);
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
		default: "Select device model",
	},
});
const { t } = useComponentTranslations(DeviceModelMultiSelectI18N);

const { selectedItems: selectedDeviceModels, open, searchTerm, filteredItems: filteredVendors, selectItem, deleteItem: deleteItemById } = useMultiSelect({
	items: DeviceModels,
	modelValue: props.modelValue,
	singleSelect: props.singleSelect,
	displayField: "name",
	searchFields: ["name"],
	emit,
});

onMounted(() => {
	fetchDeviceModels();
});

function fetchDeviceModels() {
	isLoading.value = true;
	axios.get("/api/get-device-models/?perPage=10000").then((response) => {
		DeviceModels.value = response.data.data;
	});
	isLoading.value = false;
}

function handleSave() {
	fetchDeviceModels();
}
</script>

<template>
	<Popover>
		<PopoverTrigger class="col-span-3">
			<Button variant="ghost" class="flex items-center justify-start w-full px-2 py-1 border rounded-xl whitespace-nowrap h-fit bg-rcgray-700" :class="selectedDeviceModels.length === 0 ? ' text-rcgray-400' : ''" :style="selectedDeviceModels.length === 0 ? 'padding: 0.45rem' : 'padding: 0.2rem'">
				<span v-if="isLoading">{{ t("loadingDeviceModels") }}ascascasc</span>
				<template v-else class="text-rcgray-400">
					<RcIcon name="model" class="mx-2" />
					<span v-if="selectedDeviceModels.length === 0">{{ props.placeholder }}</span>

					<!-- Display single selected item -->
					<span v-else-if="props.singleSelect && selectedDeviceModels.length > 0" class="flex items-center text-xs font-medium px-2.5 py-0.5 rounded-xl border bg-muted">
						{{ selectedDeviceModels[0].name }}
						<X size="16" class="ml-1 cursor-pointer hover:text-primary" @click.stop="deleteItemById(selectedDeviceModels[0].id)" />
					</span>

					<!-- Display multiple selected items -->
					<template v-else>
						<span v-for="model in selectedDeviceModels" :key="model.id" class="relative my-1 group" v-if="selectedDeviceModels.length > 0">
							<span class="flex items-center text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border">
								{{ model.name }}
								<X size="16" class="ml-1 cursor-pointer hover:text-white" @click.stop="deleteItemById(model.id)" />
							</span>
						</span>
					</template>
				</template>
			</Button>
		</PopoverTrigger>

		<PopoverContent side="bottom" align="start" class="col-span-3 p-0">
			<div class="relative items-center w-full">
				<Input id="search" type="text" v-model="searchTerm" autocomplete="off" :placeholder="t('common.search')" class="pl-10 border-none focus:outline-none focus-visible:ring-0 text-muted-foreground font-inter" />
				<span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
					<RcIcon name="search" />
				</span>
			</div>
			<Separator />

			<ScrollArea class="h-48 max-h-48">
				<div class="py-1">
					<RcIcon name="three-dots-loading" class="w-8 h-8 mx-auto my-4 text-muted-foreground" v-if="isLoading" />
					<div v-else v-for="deviceModel in filteredVendors" :key="deviceModel.id" class="w-full p-1 pl-2 my-1 text-sm rounded-lg hover:bg-rcgray-600" @click="selectItem(deviceModel)">
						<span data-size="20" class="cursor-default text-xs font-medium me-2 px-2.5 py-0.5">
							<span data-size="20">
								{{ deviceModel.name }}
							</span>
						</span>
					</div>
					<div v-if="DeviceModels.length === 0" class="flex flex-col items-center justify-center text-rcgray-400 text-sm py-4">
						<span class="font-semibold">{{ t("common.noResults") || "No Results" }}</span>
						<span class="mt-1">{{ t("createNewModelPrompt") }}</span>
					</div>
				</div>
			</ScrollArea>

			<Separator />

			<div class="p-1 border-5">
				<DeviceModelAddDialog @save="handleSave()" />
			</div>
		</PopoverContent>
	</Popover>
</template>
