<script setup>
import ConfigSearchResultsTable from '@/pages/Configs/ConfigSearch/ConfigSearchResultsTable.vue';
import ConfigSearchFilterCard from '@/pages/Configs/ConfigSearch/ConfigSearchFilterCard.vue';
import { ScrollArea } from '@/components/ui/scroll-area';
import { ref } from 'vue';

const filters = ref({});

const performSearch = newFilters => {
  // Object.assign(filters.value, newFilters);
  filters.value = { ...newFilters }; // Shallow copy to break reactivity
};
</script>

<template>
  <div>
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
        <h1 class="m-2 text-sm font-semibold">Filter Options</h1>

        <ConfigSearchFilterCard @searchCompleted="performSearch" />
      </ResizablePanel>
      <ResizableHandle with-handle />
      <ResizablePanel class="min-h-[100vh]">
        <ScrollArea class="border border-none rounded-md">
          <div class="h-[90dvh]">
            <h1 class="m-2 text-sm font-semibold">Results</h1>

            <!-- Render search results here -->
            <ConfigSearchResultsTable :filters="filters" />
          </div>
        </ScrollArea>
      </ResizablePanel>
    </ResizablePanelGroup>
  </div>
</template>
