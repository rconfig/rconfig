<script setup>
import ConfigCompareFilterCard from '@/pages/Configs/ConfigCompare/ConfigCompareFilterCard.vue';
import ConfigCompareFilterConfigResults from '@/pages/Configs/ConfigCompare/ConfigCompareFilterConfigResults.vue';
import ConfigCompareResults from '@/pages/Configs/ConfigCompare/ConfigCompareResults.vue';
import NavCloseButton from '@/pages/Shared/Buttons/NavCloseButton.vue';
import NavOpenButton from '@/pages/Shared/Buttons/NavOpenButton.vue';
import { ScrollArea } from '@/components/ui/scroll-area';
import { useConfigCompare } from './useConfigCompare';
import { X, CircleAlert, BookOpenCheck } from "lucide-vue-next";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";

const { close, closeNav, leftConfigData, leftConfigFilterKey, leftConfigResultsKey, leftSelectedId, loadComparison, navClosed, reset, openNav, panelElement3, rightConfigData, rightConfigFilterKey, rightConfigResultsKey, rightSelectedId, sendConfigCompare, updateConfigFilterData } = useConfigCompare();
</script>

<template>
  <div class="w-screen h-[calc(100vh-72px)] border" style="display: flex; flex-direction: column; background-color: rgb(27, 29, 33); border-radius: 16px; margin: 4px 8px 8px; max-width: calc(100% - 16px); overflow: hidden;">
		<div class="flex justify-between w-full p-2 border-b">
			<Button @click="close()" size="sm" variant="outline" class="gap-1 border-none hover:bg-rcgray-800"> <X size="16" class="text-muted-foreground hover:animate-pulse" /> </Button>
			<h2 class="items-center content-center text-muted-foreground">
                Config Compare
            </h2>
            <div class="flex items-center justify-end">
                <div v-if="leftSelectedId.length > 0 && rightSelectedId.length > 0">
                    <Badge variant="outline" class="py-1 mt-1 bg-rcgray-800">
                        <RcIcon name="status-green" class="mr-2" />
                        <span class="text-sm">Compare Config ID {{ leftSelectedId }} with Config ID {{ rightSelectedId }}</span>
                </Badge>
                </div>
                <Button v-if="leftSelectedId && rightSelectedId" @click="sendConfigCompare(leftSelectedId, rightSelectedId)" class="h-8 ml-2 text-white bg-blue-600 hover:bg-blue-700">
                Compare
                </Button>
                <Button variant="outline" v-if="leftSelectedId && rightSelectedId" @click="reset()" class="h-8 ml-2 text-white">
                Reset
                </Button>
            </div>
        </div>

        <ResizablePanelGroup direction="horizontal" class="">
            <ResizablePanel :default-size="25" :max-size="30" :min-size="10" collapsible :collapsed-size="0" ref="panelElement3" class="flex flex-col">
                <!-- Fixed header -->
                <div class="flex items-center justify-between p-2 mb-2 border-b flex-shrink-0">
                    <h1 class="ml-4 text-sm font-semibold">Search Options</h1>
                    <NavCloseButton
                        class="mr-2"
                        @close="closeNav()" />
                </div>
                <ScrollArea class="flex-1 min-h-0">
                    <div class="relative flex flex-col items-center px-2">
                        <!-- First element -->
                        <div class="w-full mb-4">
                            <ConfigCompareFilterCard :key="leftConfigFilterKey" @updateConfigFilter="(data) => updateConfigFilterData('left', data)" :comparePosition="'left'" />
                        </div>

                        <!-- Separator -->
                        <div class="w-full my-4 border-t"></div>

                        <!-- Second element -->
                        <div class="w-full mb-4">
                            <ConfigCompareFilterCard :key="rightConfigFilterKey" @updateConfigFilter="(data) => updateConfigFilterData('right', data)" :comparePosition="'right'" />
                        </div>
                        <div class="w-full my-4 border-t"></div>
                    </div>
                </ScrollArea>
            </ResizablePanel>
			<ResizableHandle with-handle />
			<ResizablePanel class="min-h-[86vh]">
                <ScrollArea class="border border-none rounded-md">
                <!-- SEARCH RESULTS -->
                <div class="h-[80dvh]">
                    <div class="flex items-center justify-between p-2 mb-2 border-b">
                        <div class="flex items-center">
                            <NavOpenButton class="ml-2" @openNav="openNav()" :navPanelBtnState="navClosed" />
                            <h1 class="w-full text-sm font-semibold" :class="navClosed === false ? 'ml-2 ' : ''">
                                Filter Results
                            </h1>
                        </div>
                        <div class="mx-2">
                            <Popover>
                                <PopoverTrigger asChild>
                                    <CircleAlert class="cursor-pointer transition-transform duration-300 hover:text-blue-500 hover:animate-pulse" size="16" />
                                </PopoverTrigger>
                                <PopoverContent class="w-64 p-4 shadow-lg border bg-card/95 backdrop-blur-xl pb-0" align="end">
                                    <div class="space-y-3">
                                        <div class="text-xs font-medium text-foreground border-b pb-2">Filter Results</div>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-muted-foreground">Filter results based on your criteria.</span>
                                                <span class="font-medium"> </span>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Footer Links -->
                                    <div class="border-t bg-muted/20 py-1 mt-2">
                                        <div class="flex items-center text-xs py-2">
                                            <a :href="$rconfigDocsUrl + '/configtools/compare/'" target="_blank" class="text-muted-foreground hover:text-foreground transition-colors flex items-center gap-1">
                                                <BookOpenCheck size="12" />
                                                View Documentation
                                            </a>
                                        </div>
                                    </div>
                                </PopoverContent>
                            </Popover>
                        </div>
                    </div>
                    <div class="relative flex flex-col items-center" v-if="!loadComparison">
                        <ConfigCompareFilterConfigResults :key="leftConfigResultsKey" :filterData="leftConfigData" @updateSelectedRows="leftSelectedId = $event" :comparePosition="'left'" />

                        <!-- Second element -->
                        <ConfigCompareFilterConfigResults :key="rightConfigResultsKey" :filterData="rightConfigData" @updateSelectedRows="rightSelectedId = $event" :comparePosition="'right'" />
                    </div>

                    <div v-if="loadComparison">
                        <ConfigCompareResults :leftSelectedId="leftSelectedId[0]" :rightSelectedId="rightSelectedId[0]" />
                    </div>
                </div>
                <!-- SEARCH RESULTS -->
                </ScrollArea>
      </ResizablePanel>
    </ResizablePanelGroup>
  </div>
</template>
