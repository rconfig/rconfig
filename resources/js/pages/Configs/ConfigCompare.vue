<script setup>
import ConfigCompareFilterCard from '@/pages/Configs/ConfigCompare/ConfigCompareFilterCard.vue';
import ConfigCompareFilterConfigResults from '@/pages/Configs/ConfigCompare/ConfigCompareFilterConfigResults.vue';
import ConfigCompareResults from '@/pages/Configs/ConfigCompare/ConfigCompareResults.vue';
import { ScrollArea } from '@/components/ui/scroll-area';
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useToaster } from '@/composables/useToaster'; // Import the composable

const leftConfigData = ref({
  selectedCommand: [],
  device: [],
  start_date: '',
  end_date: ''
});
const rightConfigData = ref({
  selectedCommand: [],
  device: [],
  start_date: '',
  end_date: ''
});
const leftConfigFilterKey = ref(100);
const leftConfigResultsKey = ref(200);
const rightConfigFilterKey = ref(300);
const rightConfigResultsKey = ref(400);
const router = useRouter();
const { toastSuccess, toastError } = useToaster(); // Using toaster for notifications
const leftSelectedId = ref([]);
const rightSelectedId = ref([]);
const loadComparison = ref(false);

const updateConfigFilterData = (position, data) => {
  if (position === 'left') {
    leftConfigData.value = data;
  } else if (position === 'right') {
    rightConfigData.value = data;
  }
  leftConfigResultsKey.value += 1;
  rightConfigResultsKey.value += 1;
};

const sendConfigCompare = () => {
  if (leftSelectedId.length > 0 || rightSelectedId.length > 0) {
    toastError('Please select configurations for comparison');
    return;
  }

  toastSuccess('Comparing configurations...', '', 2000);
  loadComparison.value = true;
};

const close = () => {
  // nav back to previous page
  router.go(-1);
};

const reset = () => {
  leftSelectedId.value = [];
  leftConfigData.value = {
    selectedCommand: [],
    device: [],
    start_date: '',
    end_date: ''
  };
  rightSelectedId.value = [];
  rightConfigData.value = {
    selectedCommand: [],
    device: [],
    start_date: '',
    end_date: ''
  };
  loadComparison.value = false;
  leftConfigResultsKey.value += 1;
  leftConfigFilterKey.value += 1;
  rightConfigResultsKey.value += 1;
  rightConfigFilterKey.value += 1;
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
      <h2 class="items-center content-center text-muted-foreground">Config Compare</h2>

      <div class="flex items-center justify-end">
        <div v-if="leftSelectedId.length > 0 && rightSelectedId.length > 0">
          <Badge
            variant="outline"
            class="py-1 mt-1 bg-rcgray-800">
            <Icon
              icon="mdi:check-circle"
              class="mr-2 text-green-500" />
            <span class="text-sm">Compare Config ID {{ leftSelectedId }} with Config ID {{ rightSelectedId }}</span>
          </Badge>
        </div>
        <Button
          v-if="leftSelectedId && rightSelectedId"
          @click="sendConfigCompare(leftSelectedId, rightSelectedId)"
          class="h-8 ml-2 text-white bg-blue-600 hover:bg-blue-700">
          Compare
        </Button>
        <Button
          variant="outline"
          v-if="leftSelectedId && rightSelectedId"
          @click="reset()"
          class="h-8 ml-2 text-white">
          Reset
        </Button>
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
        <h1 class="mt-4 ml-4 text-sm font-semibold">Search Options</h1>

        <div class="relative flex flex-col items-center">
          <!-- First element -->
          <div class="min-h-[35dvh] flex-1 w-full">
            <ConfigCompareFilterCard
              :key="leftConfigFilterKey"
              @updateConfigFilter="data => updateConfigFilterData('left', data)"
              :comparePosition="'left'" />
          </div>

          <!-- Separator -->
          <div class="w-full my-4 border-t"></div>

          <!-- Second element -->
          <div class="min-h-[35dvh] flex-1 w-full">
            <ConfigCompareFilterCard
              :key="rightConfigFilterKey"
              @updateConfigFilter="data => updateConfigFilterData('right', data)"
              :comparePosition="'right'" />
          </div>
          <div class="w-full my-4 border-t"></div>
        </div>
      </ResizablePanel>
      <ResizableHandle with-handle />
      <ResizablePanel class="min-h-[86vh]">
        <ScrollArea class="border border-none rounded-md">
          <!-- SEARCH RESULTS -->
          <div class="h-[80dvh]">
            <h1 class="w-full mt-4 ml-6 text-sm font-semibold">Filter Results</h1>

            <div
              class="relative flex flex-col items-center"
              v-if="!loadComparison">
              <ConfigCompareFilterConfigResults
                :key="leftConfigResultsKey"
                :filterData="leftConfigData"
                @updateSelectedRows="leftSelectedId = $event"
                :comparePosition="'left'" />

              <!-- Second element -->
              <ConfigCompareFilterConfigResults
                :key="rightConfigResultsKey"
                :filterData="rightConfigData"
                @updateSelectedRows="rightSelectedId = $event"
                :comparePosition="'right'" />
            </div>

            <div
              class="relative flex flex-col items-center"
              v-if="loadComparison">
              <ConfigCompareResults
                :leftSelectedId="leftSelectedId"
                :rightSelectedId="rightSelectedId" />
            </div>
          </div>
          <!-- SEARCH RESULTS -->
        </ScrollArea>
      </ResizablePanel>
    </ResizablePanelGroup>
  </div>
</template>
