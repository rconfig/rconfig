<script setup>
import CategoryMultiSelect from "@/pages/Shared/FormFields/CategoryMultiSelect.vue";
import VendorMultiSelect from "@/pages/Shared/FormFields/VendorMultiSelect.vue";
import TemplateMultiSelect from "@/pages/Shared/FormFields/TemplateMultiSelect.vue";
import DeviceModelMultiSelect from "@/pages/Shared/FormFields/DeviceModelMultiSelect.vue";
import TagMultiSelect from "@/pages/Shared/FormFields/TagMultiSelect.vue";
import Spinner from "@/pages/Shared/Icon/Spinner.vue";
import AlertSuccess from "@/pages/Shared/Alerts/AlertSuccess.vue";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Switch } from "@/components/ui/switch";
import { useDeviceBulkEditDialog } from "@/pages/Inventory/Devices/Components/useDeviceBulkEditDialog";

const props = defineProps({
	checkedRows: {
		type: Array,
		required: true,
		default: () => [],
	},
});
const emit = defineEmits(["close", "bulkUpdateSuccess"]);

const {
	// state
	append,
	deviceEnablePrompt,
	deviceMainPrompt,
	errorMessage,
	formatters,
	isUpdating,
	selectedModel,
	selectedCategory,
	selectedProperty,
	selectedPropertyName,
	selectedTemplate,
	selectedVendor,
	successMsg,
	selectedTags,
	finalPropertyOptions,

	// Methods
	clearMsgs,
	close,
	closeDialog,
	confirm,
	isDialogOpen,
	resetData,
} = useDeviceBulkEditDialog(props, emit);
</script>

<template>
	<Dialog :open="isDialogOpen('DeviceBulkEditDialog') && checkedRows.length > 0">
		<DialogContent class="max-w-2xl" @interactOutside="close()" @pointerDownOutside="close()" @escapeKeyDown="close()" @closeClicked="close()">
			<DialogHeader>
				<DialogTitle class="text-lg font-semibold">
					Bulk Edit Devices
				</DialogTitle>
				<DialogDescription> Device IDs ({{ formatters.arrayToPrettyString(checkedRows.slice(0, 10)) }}{{ checkedRows.length > 10 ? " and " + (checkedRows.length - 10) + " more" : "" }}) </DialogDescription>
			</DialogHeader>

			<div class="py-4 space-y-4">
				<div v-if="!isUpdating" class="space-y-4">
					<!-- Property Selection -->
					<div class="grid items-center grid-cols-4 gap-2">
						<Label for="property-select" class="text-right">
							Select Property
							<span class="text-red-400">*</span>
						</Label>
						<div class="col-span-3">
							<Select v-model="selectedPropertyName">
								<SelectTrigger id="property-select" class="w-full">
									<SelectValue placeholder="Choose a property to update" />
								</SelectTrigger>
								<SelectContent>
									<SelectItem v-for="property in finalPropertyOptions" :value="property.name" :key="property.id">
										{{ formatters.capitalizeFirstLetter(property.name) }}
									</SelectItem>
								</SelectContent>
							</Select>
						</div>
					</div>

					<!-- Property-Specific Fields -->
					<div v-if="selectedProperty" class="grid items-start grid-cols-4 gap-2">
						<Label for="property-field" class="text-right pt-2" v-if="selectedPropertyName">
							{{ formatters.capitalizeFirstLetter(selectedPropertyName) }}
							<span class="text-red-400">*</span>
						</Label>
						<CategoryMultiSelect v-if="selectedProperty.name === 'command_group'" v-model="selectedCategory" :singleSelect="true" class="w-full" />
						<div v-if="selectedProperty.name === 'device_enable_prompt'" class="col-span-3">
							<Input v-model="deviceEnablePrompt" id="device_enable_prompt" autocomplete="new-name" name="new-name" />
						</div>
						<div v-if="selectedProperty.name === 'device_main_prompt'" class="col-span-3">
							<Input v-model="deviceMainPrompt" id="device_main_prompt" autocomplete="new-name" name="new-name" />
						</div>
						<DeviceModelMultiSelect v-if="selectedProperty.name === 'model'" v-model="selectedModel" :singleSelect="true" class="w-full" />
						<VendorMultiSelect v-if="selectedProperty.name === 'vendor'" v-model="selectedVendor" :singleSelect="true" class="w-full" />
						<TemplateMultiSelect v-if="selectedProperty.name === 'template'" v-model="selectedTemplate" :singleSelect="true" class="w-full" />
						<TagMultiSelect v-if="selectedProperty.name === 'tag'" v-model="selectedTags" :singleSelect="false" class="w-full" />
						<span class="col-span-3 col-start-2 text-red-400 text-sm" v-if="errorMessage">
							{{ errorMessage }}
						</span>
					</div>

					<!-- Append Option for Tags -->
					<div v-if="selectedPropertyName === 'tag'" class="grid items-center grid-cols-4 gap-2">
						<Label for="append-toggle" class="text-right">
							Append
							<span class="text-red-400">*</span>
						</Label>
						<div class="flex items-center gap-2 col-span-3">
							<Switch id="append-toggle" :checked="append" @update:checked="append = !append" :disabled="isUpdating" />
							<span class="text-sm">Append to existing</span>
						</div>
						<span class="col-span-3 col-start-2 text-xs text-muted-foreground">
							<span v-if="append">Selected items will be <strong>added</strong> to existing items for selected devices.</span>
							<span v-else>Selected items will <strong>replace</strong> all existing items for selected devices.</span>
						</span>
					</div>

					<AlertSuccess small v-if="successMsg" class="mb-6" title="Success" :message="successMsg" :showClose="true" @closed="successMsg = ''" />
				</div>

				<!-- Loading State -->
				<div v-else class="flex flex-col items-center justify-center gap-4">
					<div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-300"></div>
					<p class="text-sm text-gray-200">Processing bulk update. This may take a few moments...</p>
				</div>
			</div>

			<DialogFooter>
				<Button type="close" variant="outline" class="px-2 py-1 ml-2 text-sm hover:bg-gray-700 hover:animate-pulse" @click="close" size="sm">
					Cancel
					<div class="pl-2 ml-auto">
						<kbd class="rc-kdb-class">ESC</kbd>
					</div>
				</Button>

				<Button type="submit" class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse" size="sm" @click="confirm()" variant="primary">
					<Spinner :state="isUpdating" class="items-center mr-2" :color="'white'" :fillColor="'white'" />
					Confirm
					<div class="pl-2 ml-auto">
						<kbd class="rc-kdb-class2">
							Ctrl&nbsp;
							<RcIcon name="enter" class="ml-1" />
						</kbd>
					</div>
				</Button>
			</DialogFooter>
		</DialogContent>
	</Dialog>
</template>