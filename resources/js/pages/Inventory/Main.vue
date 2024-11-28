<script setup>
import { DropdownMenu, DropdownMenuContent, DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuPortal, DropdownMenuSeparator, DropdownMenuShortcut, DropdownMenuSub, DropdownMenuSubContent, DropdownMenuSubTrigger, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import Command from '@/pages/Inventory/Commands/Main.vue';
import CommandGroups from '@/pages/Inventory/CommandGroups/Main.vue';
import Devices from '@/pages/Inventory/Devices/Main.vue';
import Tags from '@/pages/Inventory/Tags/Main.vue';
import Template from '@/pages/Inventory/Templates/Main.vue';
import Vendors from '@/pages/Inventory/Vendors/Main.vue';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { useInventory } from '@/pages/Inventory/useInventory';

const { changeView, currentView, favoritesStore, route, router, toggleFavorite, viewItems } = useInventory();
</script>

<template>
  <main class="flex flex-col flex-1 gap-2 dark:bg-rcgray-900">
    <div class="border-t border-b topRow">
      <DropdownMenu>
        <DropdownMenuTrigger
          as-child
          class="p-4 ml-2">
          <Button variant="outline">
            <span class="flex items-center gap-2">
              <Icon icon="fluent-color:pin-16" />
              Select View
            </span>
          </Button>
        </DropdownMenuTrigger>
        <div class="flex items-center gap-2 mr-4">
          <component :is="viewItems.find(item => item.id === currentView)?.icon || ''" />
          {{ viewItems.find(item => item.id === currentView)?.label || '' }}
        </div>

        <DropdownMenuContent
          class="w-56"
          align="start">
          <DropdownMenuGroup>
            <DropdownMenuItem
              v-for="item in viewItems"
              :key="item.id"
              @click="changeView(item.id)">
              <span class="flex items-center gap-2">
                <component :is="item.icon" />
                {{ item.label }}
              </span>
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger as-child>
                    <DropdownMenuShortcut @click.stop.prevent="toggleFavorite(item.id)">
                      <StarUnselected v-if="!item.isFavorite.value" />
                      <StarSelected v-if="item.isFavorite.value" />
                    </DropdownMenuShortcut>
                  </TooltipTrigger>
                  <TooltipContent>
                    <p>{{ item.isFavorite.value ? 'Remove from favorites' : 'Add to favorites' }}</p>
                  </TooltipContent>
                </Tooltip>
              </TooltipProvider>
            </DropdownMenuItem>
          </DropdownMenuGroup>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>

    <div>
      <Devices v-if="currentView === 'devices' || currentView === 'devicesview'"></Devices>
      <CommandGroups v-if="currentView === 'commandgroups'"></CommandGroups>
      <Command v-if="currentView === 'commands'"></Command>
      <Template v-if="currentView === 'templates'"></Template>
      <Vendors v-if="currentView === 'vendors'"></Vendors>
      <Tags v-if="currentView === 'tags'"></Tags>
    </div>
  </main>
</template>
