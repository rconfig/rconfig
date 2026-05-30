<script setup>
import { ref, onMounted, watch } from "vue";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import ImportData from "@/pages/Settings/Panels/Components/ImportData.vue";
import ExportData from "@/pages/Settings/Panels/Components/ExportData.vue";

// Initialize with default, but will be updated from localStorage if available
const activeTab = ref("import");

// Load the saved tab on component mount
onMounted(() => {
	const savedTab = localStorage.getItem("importExportActiveTab");
	if (savedTab) {
		activeTab.value = savedTab;
	}
});

// Save the active tab whenever it changes
watch(activeTab, (newTab) => {
	localStorage.setItem("importExportActiveTab", newTab);
});
</script>

<template>
	<div class="container max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="flex flex-col items-center w-full gap-4">
			<div class="grid w-full max-w-full items-center gap-1.5">
				<h3 class="rc-panel-heading">Import Export Settings</h3>
				<p class="rc-panel-subheading">
					Import and export settings allow you to backup and restore your rConfig configuration. You can export various data tables to a CSV file. You may also use the import wizard to import device records from another system to rConfig. This is useful for migrating your configuration to a new server or restoring a backup.
				</p>

				<Separator class="my-6" />

				<Tabs v-model="activeTab" class="w-full">
					<TabsList class="grid w-full grid-cols-2">
						<TabsTrigger value="import">Import</TabsTrigger>
						<TabsTrigger value="export">Export</TabsTrigger>
					</TabsList>
					<TabsContent class="mt-6" value="import">
						<ImportData />
					</TabsContent>
					<TabsContent class="mt-6" value="export">
						<ExportData />
					</TabsContent>
				</Tabs>
			</div>
		</div>
	</div>
</template>
