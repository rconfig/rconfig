<script setup>
import ConfigViewMainPanel from '@/pages/Configs/ConfigView/ConfigViewMainPanel.vue';
import ConfigSummaryPanel from '@/pages/Configs/ConfigView/ConfigSummaryPanel.vue';
import ConfigViewPaneDropdown from '@/pages/Configs/ConfigView/ConfigViewPaneDropdown.vue';
import Loading from '@/pages/Shared/Loading.vue';
import { ScrollArea } from '@/components/ui/scroll-area';
import { useConfigViewPane } from '@/pages/Configs/ConfigView/useConfigViewPane';
import { useConfigsTable } from '@/pages/Configs/useConfigsTable';

const emit = defineEmits(['close']);
const props = defineProps({
  configId: Number
});

const { configData, isLoading, panelElement2 } = useConfigViewPane(props, emit);
const { deleteConfig } = useConfigsTable(props);

function deleteTheConfig() {
  deleteConfig(props.configId);
  emit('close');
}
</script>

<template>
  <main class="flex flex-col flex-1 gap-2 dark:bg-rcgray-900">
    <div class="flex flex-row items-center justify-between h-12 gap-3 pl-4 pr-2 border-b">
      <div
        class="flex items-center"
        v-if="configData">
        <span class="text-lg font-semibold text-blue-400 font-inter">{{ configData.config_filename }}</span>
      </div>
      <div class="flex items-center">
        <ConfigViewPaneDropdown
          :editId="configId"
          @onDelete="deleteTheConfig()" />
      </div>
    </div>

    <div class="">
      <ResizablePanelGroup
        direction="horizontal"
        class="">
        <ResizablePanel
          :default-size="25"
          :max-size="30"
          :min-size="10"
          collapsible
          :collapsed-size="0"
          ref="panelElement2"
          class="min-h-[100vh]">
          <ConfigSummaryPanel
            class="p-2 mt-2"
            :isLoading="isLoading"
            :configData="configData" />
        </ResizablePanel>
        <ResizableHandle with-handle />
        <ResizablePanel class="min-h-[100vh]">
          <ScrollArea class="h-full border border-none rounded-md">
            <div
              class="flex items-center justify-center"
              style="height: 60vh"
              v-if="isLoading">
              <Loading class="flex justify-center" />
            </div>
            <ConfigViewMainPanel
              class="p-2"
              v-if="configData"
              :configId="configId"
              style="height: 60vh" />
          </ScrollArea>
        </ResizablePanel>
      </ResizablePanelGroup>
    </div>
  </main>
</template>
