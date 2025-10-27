<script setup>
import ConfigCompareFilterCard from '@/pages/Configs/ConfigCompare/ConfigCompareFilterCard.vue';
import ConfigCompareFilterConfigResults from '@/pages/Configs/ConfigCompare/ConfigCompareFilterConfigResults.vue';
import ConfigCompareResults from '@/pages/Configs/ConfigCompare/ConfigCompareResults.vue';
import NavCloseButton from '@/pages/Shared/Buttons/NavCloseButton.vue';
import NavOpenButton from '@/pages/Shared/Buttons/NavOpenButton.vue';
import { ScrollArea } from '@/components/ui/scroll-area';
import { useConfigCompare } from './useConfigCompare';

const { close, closeNav, leftConfigData, leftConfigFilterKey, leftConfigResultsKey, leftSelectedId, loadComparison, navClosed, reset, openNav, panelElement3, rightConfigData, rightConfigFilterKey, rightConfigResultsKey, rightSelectedId, sendConfigCompare, updateConfigFilterData } = useConfigCompare();
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
        ref="panelElement3"
        class="min-h-[86vh]">
        <div class="flex items-center justify-between p-2 mb-2 border-b">
          <h1 class="ml-4 text-sm font-semibold">Search Options</h1>
          <NavCloseButton
            class="mr-2"
            @close="closeNav()" />
        </div>

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
            <div class="flex items-center justify-between p-2 mb-2 border-b">
              <NavOpenButton
                class="ml-2"
                @openNav="openNav()"
                :navPanelBtnState="navClosed" />
              <h1
                class="w-full text-sm font-semibold"
                :class="navClosed === false ? 'ml-2 ' : ''">
                Filter Results
              </h1>
            </div>

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

            <div v-if="loadComparison">
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
