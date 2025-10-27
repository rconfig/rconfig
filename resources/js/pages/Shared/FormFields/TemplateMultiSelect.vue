<script setup lang="ts">
import RcConfirmAlertDialog from "@/pages/Shared/ConfirmAlertDialog/RcConfirmAlertDialog.vue";
import axios from "axios";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { X, Info } from "lucide-vue-next";
import { onMounted, ref } from "vue";
import { useConfirmCloseAlert } from "@/pages/Shared/ConfirmAlertDialog/useConfirmCloseAlert";
import { useRouter } from "vue-router";
import { useMultiSelect } from "./useMultiSelect.js";

const emit = defineEmits(["update:modelValue"]);
const templates = ref([]);
const router = useRouter();
const { showConfirmCloseAlert, showConfirmCloseDialog, cancelCloseDialog, confirmCloseDialog } = useConfirmCloseAlert();
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
		default: "Select a template",
	},
});

const { selectedItems: selectedTemplates, open, searchTerm, filteredItems: filteredVendors, selectItem, deleteItem: deleteItemById } = useMultiSelect({
	items: templates,
	modelValue: props.modelValue,
	singleSelect: props.singleSelect,
	displayField: "templateName",
	searchFields: ["templateName"],
	emit,
});

onMounted(() => {
	fetchTemplates();
});

function fetchTemplates() {
	isLoading.value = true;
	axios.get("/api/templates/?perPage=10000").then((response) => {
		templates.value = response.data.data;
		isLoading.value = false;
	});
}

function navToTemplates() {
	router.push({ path: "/templates/view/0" });
}
</script>

<template>
	<!-- DIV FOR RENDERING THE BADGE COLOR CLASSES -->
	<Popover>
		<PopoverTrigger class="col-span-3">
			<Button variant="ghost" class="flex flex-wrap items-start justify-start w-full pl-2 whitespace-normal border h-fit" :class="selectedTemplates.length === 0 ? 'text-muted-foreground' : ' '" :style="selectedTemplates.length === 0 ? 'padding: 0.45rem' : 'padding: 0.2rem'">
				<span v-if="isLoading">Loading device models...</span>
				<!-- Padding is 0.45rem to match Inputs and adjustment when adding templates -->
				<div v-else class="flex items-center justify-start w-full">
					<RcIcon name="template" class="mx-2" />
					{{ selectedTemplates.length === 0 ? "Selected templates: " : "" }}

					<span v-for="template in selectedTemplates" :key="template.id" class="relative my-1 group">
						<span class="flex items-center text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border">
							{{ template.templateName }}
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
					<div v-else v-for="template in filteredVendors" :key="template.id" class="w-full p-1 pl-2 my-1 text-sm rounded-lg hover:bg-rcgray-600" @click="selectItem(template)">
						<span data-size="20" class="cursor-default text-xs font-medium me-2 px-2.5 py-0.5">
							<span data-size="20">
								{{ template.templateName }}
							</span>
						</span>
					</div>
				</div>
			</ScrollArea>

			<Separator />

			<div class="p-1 border-5">
				<Button variant="ghost" @click="showConfirmCloseDialog()" class="justify-start w-full p-1">
					<RcIcon name="plus" class="w-8" />
					<div class="rc-text-xs-muted ml-1">Create new record</div>
					<TooltipProvider>
						<Tooltip>
							<TooltipTrigger as-child>
								<span class="ml-auto">
									<Info class="w-3 h-3 text-muted-foreground" />
								</span>
							</TooltipTrigger>
							<TooltipContent class="text-white bg-rcgray-800">
								<p>Navigate to new template page</p>
							</TooltipContent>
						</Tooltip>
					</TooltipProvider>
				</Button>
			</div>
		</PopoverContent>

		<RcConfirmAlertDialog :showConfirmCloseAlert="showConfirmCloseAlert" @handleClose="cancelCloseDialog" @handleConfirm="navToTemplates()" />
	</Popover>
</template>
