<script setup>
import ConfigsTable from '@/pages/Configs/ConfigsTable.vue';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { useConfigs } from '@/pages/Configs/useConfigs';

const { configsId, statusIdParam, changeView, currentView, bottomBorderStyle, toggleFavorite, viewItems } = useConfigs();
</script>

<template>
  <main class="flex flex-col flex-1 gap-2 dark:bg-rcgray-900">
    <div class="flex flex-row items-center justify-start h-12 gap-3 px-5 border-t border-b">
      <div class="relative">
        <Button
          class="border"
          @click="changeView('configs')"
          data-nav="configs"
          variant="ghost">
          <ConfigToolsIcon class="mr-2" />
          Configurations
        </Button>
        <Button
          class="ml-2 border"
          @click="changeView('configsearch')"
          data-nav="configsearch"
          variant="ghost">
          <ConfigSearchIcon class="mr-2" />
          Search
        </Button>
        <Button
          class="ml-2 border"
          @click="changeView('configcompare')"
          data-nav="configcompare"
          variant="ghost">
          <ConfigToolsIcon class="mr-2" />
          Compare
        </Button>

        <div
          v-if="currentView"
          class="absolute -bottom-1.5 h-0.5 bg-blue-500"
          :style="bottomBorderStyle"></div>
      </div>

      <TooltipProvider>
        <Tooltip
          v-for="item in viewItems"
          :key="item.id">
          <TooltipTrigger
            as-child
            v-if="currentView === item.id">
            <DropdownMenuShortcut @click.stop.prevent="toggleFavorite(item.id)">
              <StarUnselected v-if="!item.isFavorite.value" />
              <StarSelected v-if="item.isFavorite.value" />
            </DropdownMenuShortcut>
          </TooltipTrigger>
          <TooltipContent>
            <p>{{ item.isFavorite.value ? 'Remove from favorites' : 'Add ' + currentView + ' to favorites' }}</p>
          </TooltipContent>
        </Tooltip>
      </TooltipProvider>
    </div>

    <div>
      <ConfigsTable
        :configsId="configsId"
        :statusId="statusIdParam.statusId"
        v-if="currentView === 'configs'" />
      <!-- 
        // These are rendered in the resources/js/pages/Shared/ContentPanel.vue component
      <ConfigSearch v-if="currentView === 'configsearch'" />
      <ConfigCompare v-if="currentView === 'configcompare'" /> -->
    </div>
  </main>
</template>
