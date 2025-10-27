<script setup>
import TagMultiSelectI18N from "@/i18n/pages/Shared/FormFields/TagMultiSelect.i18n.js";
import axios from "axios";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { ScrollArea } from "@/components/ui/scroll-area";
import { X } from "lucide-vue-next";
import { onMounted, ref } from "vue";
import { useComponentTranslations } from "@/composables/useComponentTranslations";
import { useMultiSelect } from "./useMultiSelect.js";
import { inject } from "vue";

const emit = defineEmits(["update:modelValue"]);
const severities = ref([]);
const isLoading = ref(true);
const formatters = inject("formatters");

const { t } = useComponentTranslations(TagMultiSelectI18N);

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
		default: "Select severities",
	},
});

const { selectedItems: selectedSeverity, open, searchTerm, filteredItems: filteredSeverities, selectItem, deleteItem } = useMultiSelect({
	items: severities,
	modelValue: props.modelValue,
	singleSelect: props.singleSelect,
	displayField: "name",
	searchFields: ["name"],
	emit,
});

onMounted(() => {
	fetchTags();
});

function fetchTags() {
	isLoading.value = true;
	axios.get("/api/config-changes/filters/options").then((response) => {
		// Transform array of strings to array of objects because the component expects objects - /api/config-changes/filters/options does not return objects
		severities.value = response.data.severities.map((severity, index) => ({
			id: index + 1, // Start IDs from 1
			name: severity,
			badgeColor: severity.toLowerCase() === "critical" ? "bg-red-600 text-white" : severity.toLowerCase() === "high" ? "bg-orange-500 text-white" : severity.toLowerCase() === "medium" ? "bg-yellow-400 text-black" : severity.toLowerCase() === "low" ? "bg-green-500 text-white" : "bg-gray-600 text-gray-200", // Default color for other severities
		}));
		isLoading.value = false;
	});
}
</script>

<template>
	<Popover>
		<PopoverTrigger class="col-span-3 w-full">
			<Button variant="ghost" class="flex flex-wrap items-center justify-start w-full p-1 pl-2 border h-fit gap-1 mr-1" :class="selectedSeverity.length === 0 ? 'text-rcgray-400' : ''">
				<span v-if="isLoading">{{ t("common.loading") }}</span>
				<template v-else class="text-rcgray-400">
					<RcIcon name="severity" class="mx-2" />

					<span v-if="selectedSeverity.length === 0">{{ props.placeholder }}</span>

					<!-- Display single selected item -->
					<span v-else-if="props.singleSelect && selectedSeverity.length > 0" class="flex items-center text-xs font-medium px-2.5 py-0.5 rounded-xl border" :class="selectedSeverity[0]?.badgeColor ? selectedSeverity[0]?.badgeColor : 'bg-gray-600 text-gray-200 border-gray-500'">
						{{ formatters.capitalize(selectedSeverity[0]?.name) }}
						<X size="16" class="ml-1 cursor-pointer hover:text-primary" @click.stop="deleteItem(selectedSeverity[0]?.id)" />
					</span>

					<!-- Display multiple selected items -->
					<template v-else>
						<span v-for="severity in selectedSeverity" :key="severity.id" class="relative my-1 group">
							<span class="flex items-center text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border">
								{{ formatters.capitalize(severity.name) }}

								<X size="16" class="ml-1 cursor-pointer hover:text-white" @click.stop="deleteItem(severity.id)" />
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

			<ScrollArea class="h-64">
				<div class="py-1">
					<RcIcon name="three-dots-loading" class="w-8 h-8 mx-auto my-4 text-muted-foreground" v-if="isLoading" />
					<div v-else v-for="severity in filteredSeverities" :key="severity.id" class="w-full p-1 pl-2 my-1 text-sm rounded-lg hover:bg-rcgray-600" @click="selectItem(severity)">
						<span data-size="20" class="cursor-default text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border" :class="severity.badgeColor ? severity.badgeColor : 'bg-gray-600 text-gray-200 border-gray-500'">
							<span data-size="20">
								{{ formatters.capitalize(severity.name) }}
							</span>
						</span>
					</div>
				</div>
			</ScrollArea>
		</PopoverContent>
	</Popover>
</template>
