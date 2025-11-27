<script setup>
import ConfigSummaryPanel from "@/pages/Configs/ConfigView/ConfigSummaryPanel.vue";
import ConfigViewMainPanel from "@/pages/Configs/ConfigView/ConfigViewMainPanel.vue";
import ConfigViewPaneDropdown from "@/pages/Configs/ConfigView/ConfigViewPaneDropdown.vue";
import DetailsViewLeftNav from "@/pages/Inventory/Shared/Navs/DetailsViewLeftNav.vue";
import Loading from "@/pages/Shared/Loaders/Loading.vue";
import { ResizablePanelGroup, ResizablePanel, ResizableHandle } from "@/components/ui/resizable";
import { ScrollArea } from "@/components/ui/scroll-area";
import { ref } from "vue";
import { useConfigViewPane } from "@/pages/Configs/ConfigView/useConfigViewPane";
import { useConfigsTable } from "@/pages/Configs/useConfigsTable";
import { X } from "lucide-vue-next";

const emit = defineEmits(["close"]);
const props = defineProps({
	configId: [String, Number],
});

const { configData, isLoading, panelElement2, leftNavSelected, closeNav, selectLeftNavView } = useConfigViewPane(props, emit);
const { deleteConfig } = useConfigsTable(props);

const viewConfigId = ref(props.configId);
const showChangesPanel = ref(false);
const changesConfigId = ref(null);
const selectedConfigVersion = ref(null);

function deleteTheConfig() {
	deleteConfig(props.configId);
	emit("close");
}

function handleViewConfig(configProps) {
	viewConfigId.value = configProps.id;
	selectedConfigVersion.value = configProps.version;
	showChangesPanel.value = false;
}

function handleViewConfigChanges(configId) {
	changesConfigId.value = configId;
	showChangesPanel.value = true;
}

function handleBackToConfig() {
	showChangesPanel.value = false;
}

function close() {
	emit("close");
}
</script>

<template>
	<main class="flex flex-col flex-1 dark:bg-rcgray-900">
		<div class="flex flex-row items-center justify-between h-12 gap-3 pl-4 pr-2">
			<div class="flex items-center" v-if="configData">
				<Button @click="close()" size="sm" variant="outline" class="gap-1 border-none hover:bg-rcgray-600"> <X size="16" class="hover:animate-pulse" /> </Button>
				<span class="text-lg font-semibold rc-text-heading-gradient font-inter ml-2">{{ configData.config_filename }}</span>
			</div>
			<div class="flex items-center">
				<ConfigViewPaneDropdown :editId="configId" @onDelete="deleteTheConfig()" />
			</div>
		</div>

		<div class="">
			<ResizablePanelGroup direction="horizontal" class="">
				<ResizablePanel :default-size="25" :max-size="30" :min-size="10" collapsible :collapsed-size="0" ref="panelElement2" class="min-h-[100vh] h-full flex flex-col overflow-y-auto">
					<ScrollArea class="max-h-[83vh] w-full rounded-md border smooth-scroll overflow-y-auto">
						<DetailsViewLeftNav @closeNav="closeNav" @selectLeftNavView="selectLeftNavView" :selectedNav="leftNavSelected" :deviceId="configId" context="config" />
						<ConfigSummaryPanel class="p-2" v-if="leftNavSelected === 'details'" :isLoading="isLoading" :configData="configData" />
					</ScrollArea>
				</ResizablePanel>
				<ResizableHandle with-handle />
				<ResizablePanel class="min-h-[100vh] border-t">
					<ScrollArea class="h-full border border-none rounded-md">
						<div class="flex items-center justify-center" style="height: 60vh;" v-if="isLoading">
							<Loading class="flex justify-center" />
						</div>
						<ConfigViewMainPanel class="p-2" v-if="!isLoading && configData && !showChangesPanel" :deviceId="configData.device_id" :deviceName="configData.device_name" :configId="viewConfigId" :selectedConfigVersion="selectedConfigVersion" :key="viewConfigId" style="height: 60vh;" />
					</ScrollArea>
				</ResizablePanel>
			</ResizablePanelGroup>
		</div>
	</main>
</template>