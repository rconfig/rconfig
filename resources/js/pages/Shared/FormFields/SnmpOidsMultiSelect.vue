<script setup lang="ts">
import SnmpOidsMultiSelectI18N from "@/i18n/pages/Shared/FormFields/SnmpOidsMultiSelect.i18n.js";
import axios from "axios";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { ScrollArea } from "@/components/ui/scroll-area";
import { X } from "lucide-vue-next";
import { onMounted, ref, watch } from "vue";
import { useComponentTranslations } from "@/composables/useComponentTranslations";
import { useMultiSelect } from "./useMultiSelect.js";
import { useSnmpOids } from "@/pages/Inventory/SnmpProps/useSnmpOids";

const emit = defineEmits(["update:modelValue"]);
const oids = ref([]);
const { newOidModalKey } = useSnmpOids();
const isLoading = ref(true);

const { t } = useComponentTranslations(SnmpOidsMultiSelectI18N);

const props = defineProps({
	modelValue: {
		type: [Array, String],
		required: true,
	},
	singleSelect: {
		type: Boolean,
		default: false,
	},
	placeholder: {
		type: String,
		default: "Select oids",
	},
});

const { selectedItems: selectedSnmpOids, open, searchTerm, filteredItems: filteredSnmpOids, selectItem, deleteItem: deleteItemById } = useMultiSelect({
	items: oids,
	modelValue: props.modelValue,
	singleSelect: props.singleSelect,
	displayField: "attribute",
	searchFields: ["attribute"],
	emit,
});

onMounted(() => {
	fetchOids();
});

watch(newOidModalKey, () => {
	// if the add new category dialog is closed, fetch categories again
	fetchOids();
});

function fetchOids() {
	isLoading.value = true;
	axios.get("/api/snmp-oids/?perPage=10000&sort=attribute").then((response) => {
		oids.value = response.data.data;
		isLoading.value = false;
	});
}
</script>

<template>
	<Popover>
		<PopoverTrigger class="col-span-3">
			<Button variant="ghost" class="flex flex-wrap items-start justify-start w-full pl-2 whitespace-normal border h-fit" :class="selectedSnmpOids.length === 0 ? 'text-muted-foreground' : ' '" :style="selectedSnmpOids.length === 0 ? 'padding: 0.45rem' : 'padding: 0.2rem'">
				<!-- Padding is 0.45rem to match Inputs and adjustment when adding oids -->
				{{ selectedSnmpOids && selectedSnmpOids.length === 0 ? t('selectOids') : "" }}
				<span v-for="oid in selectedSnmpOids" :key="oid.id" class="relative my-1 group">
					<span class="flex items-center text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border">
						{{ oid.attribute }}

						<X size="10" class="ml-1 cursor-pointer text-rcgray-300 hover:text-white" @click.stop="deleteItemById(oid.id)" />
					</span>
				</span>
			</Button>
		</PopoverTrigger>
		<PopoverContent side="bottom" align="start" class="col-span-3 p-0 max-h-56 overflow-hidden">
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
					<div v-else v-for="oid in filteredSnmpOids" :key="oid.id" class="w-full p-1 pl-2 my-1 text-sm rounded-lg hover:bg-rcgray-600" @click="selectItem(oid)">
						<span data-size="20" class="cursor-default text-xs font-medium me-2 px-2.5 py-0.5">
							<span data-size="20">
								{{ oid.attribute }}
							</span>
						</span>
					</div>
				</div>
			</ScrollArea>

			<!-- <Separator />

			<div class="p-1 border-5">
				<Button variant="ghost" @click="createSnmpOid()" class="justify-start w-full p-1">
					<RcIcon name="plus" class="w-8" />
					<div class="rc-text-xs-muted ml-1">{{ t('createNewRecord') }}</div>
				</Button>
			</div> -->
		</PopoverContent>
	</Popover>
</template>
