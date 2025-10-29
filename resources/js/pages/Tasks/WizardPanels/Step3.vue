<script setup>
import CategoryMultiSelect from "@/pages/Shared/FormFields/CategoryMultiSelect.vue";
import DeviceMultiSelect from "@/pages/Shared/FormFields/DeviceMultiSelect.vue";
import TagMultiSelect from "@/pages/Shared/FormFields/TagMultiSelect.vue";
import { Label } from "@/components/ui/label";
import { Switch } from "@/components/ui/switch";
import { ref } from "vue";

defineProps({
	model: Object,
});
</script>

<template>
	<div>
		<h3 class="mb-5 text-lg font-medium text-gray-900 dark:text-white">Task Configuration</h3>

        <!-- Regular Task Configuration -->
        <div class="space-y-4">
            <!-- Single field configurations -->
            <div v-if="model.task_command === 'rconfig:download-device'" class="space-y-2">
                <Label for="device-select" class="block">Devices</Label>
                <DeviceMultiSelect v-model="model.device" id="device-select" class="block"/>
            </div>

            <div v-if="model.task_command === 'rconfig:download-category'" class="space-y-2">
                <Label for="category-select" class="block">Command Groups</Label>
                <CategoryMultiSelect v-model="model.category" id="category-select" class="block"/>
            </div>

            <div v-if="model.task_command === 'rconfig:download-tag'" class="space-y-2">
                <Label for="tag-select" class="block">Tags</Label>
                <TagMultiSelect v-model="model.tag" id="tag-select" class="block"/>
            </div>
        </div>

		<div>
			<h3 class="mt-4 mb-2 text-sm font-medium">Task Email Settings</h3>

			<div class="space-y-4">
				<div class="flex flex-row items-center justify-between p-4 space-y-2 border rounded-lg">
					<div class="space-y-0.5">
						<label for="radix-v-37-form-item" class="text-base font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
							Failure Only Report
						</label>
						<p id="radix-v-37-form-item-description" class="text-sm text-muted-foreground">
							Send an email report only when a task fails
						</p>
					</div>
					<Switch :checked="model.download_report_notify" @update:checked="model.download_report_notify = !model.download_report_notify" />
				</div>

				<div class="flex flex-row items-center justify-between p-4 space-y-2 border rounded-lg">
					<div class="space-y-0.5">
						<label for="radix-v-37-form-item" class="text-base font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
							Verbose Report
						</label>
						<p id="radix-v-37-form-item-description" class="text-sm text-muted-foreground">
							Send a detailed verbose report via email
						</p>
					</div>
					<Switch :checked="model.verbose_download_report_notify" @update:checked="model.verbose_download_report_notify = !model.verbose_download_report_notify" />
				</div>
			</div>
		</div>
	</div>
</template>