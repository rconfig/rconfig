<script setup>
import ConfigCompareFilterCard from '@/pages/Configs/ConfigCompare/ConfigCompareFilterCard.vue';
import { ScrollArea } from '@/components/ui/scroll-area';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const filters = ref({});
const router = useRouter();

const performSearch = newFilters => {
  // Object.assign(filters.value, newFilters);
  filters.value = { ...newFilters }; // Shallow copy to break reactivity
};

const close = () => {
  // nav back to previous page
  router.go(-1);
};
</script>

<template>
  <div
    class="w-screen h-[calc(100vh-72px)] border"
    style="display: flex; flex-direction: column; background-color: rgb(27, 29, 33); border-radius: 16px; margin: 4px 8px 8px; max-width: calc(100% - 16px); overflow: hidden">
    <div class="flex justify-between w-full p-2 border-b">
      <Button
        @click="close()"
        size="sm"
        variant="outline"
        class="gap-1 border-none hover:bg-rcgray-800">
        <Icon
          icon="mingcute:close-line"
          class="hover:animate-pulse" />
      </Button>
      <h2 class="items-center content-center text-muted-foreground">Config Search</h2>

      <div class="flex justify-end">
        <!-- EMPTY -->
      </div>
    </div>

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
        class="min-h-[86vh]">
        <h1 class="m-2 text-sm font-semibold">Search Options</h1>

        <div class="relative flex flex-col items-center">
  <!-- First element -->
  <div class="flex-1 w-full">
    <ConfigCompareFilterCard @searchCompleted="performSearch" :comparePosition="'left'"/>
  </div>

  <!-- Separator -->
  <div class="w-full my-4 border-t "></div>

  <!-- Second element -->
  <div class="flex-1 w-full">
    <ConfigCompareFilterCard @searchCompleted="performSearch" :comparePosition="'right'"/>
  </div>
</div>
      </ResizablePanel  class="min-h-[86vh]">
      <ResizableHandle with-handle />
      <ResizablePanel class="h-16">
        <ScrollArea class="border border-none rounded-md">
          <div class="h-[90dvh]">
            <h1 class="m-2 text-sm font-semibold">Results</h1>

            CONFIG COMPARE RIGHT
          </div>
        </ScrollArea>
      </ResizablePanel>
    </ResizablePanelGroup>
  </div>
</template>
