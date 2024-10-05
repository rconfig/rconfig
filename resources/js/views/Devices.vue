<script setup>
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Icon } from '@iconify/vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuPortal, DropdownMenuSeparator, DropdownMenuShortcut, DropdownMenuSub, DropdownMenuSubContent, DropdownMenuSubTrigger, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';

defineProps({});

const title = 'Dashboard';
const currentView = ref('devices'); // Reactive view state
const viewItems = [
  { id: 'devices', label: 'Devices', icon: 'fluent-color:org-16', isFavorite: ref(false) },
  { id: 'commandGroups', label: 'Command Groups', icon: 'fluent-color:search-visual-24', isFavorite: ref(false) },
  { id: 'commands', label: 'Commands', icon: 'fluent-color:text-edit-style-16', isFavorite: ref(false) },
  { id: 'templates', label: 'Templates', icon: 'fluent-color:code-block-32', isFavorite: ref(false) },
  { id: 'vendors', label: 'Vendors', icon: 'fluent-color:apps-16', isFavorite: ref(false) },
  { id: 'tags', label: 'Tags', icon: 'fluent-emoji:keycap-hashtag', isFavorite: ref(false) }
];

function changeView(view) {
  currentView.value = view; // Update the view reactively
}

function toggleFavorite(viewId) {
  const viewItem = viewItems.find(item => item.id === viewId);
  if (viewItem) {
    viewItem.isFavorite.value = !viewItem.isFavorite.value;
  }
}
</script>

<template>
  <main class="flex flex-col flex-1 gap-4 dark:bg-rcgray-900">
    <div class="border-t border-b topRow">
      <DropdownMenu>
        <DropdownMenuTrigger
          as-child
          class="p-2">
          <Button variant="outline">
            <span class="flex items-center gap-2">
              <Icon icon="fluent-color:pin-16" />
              View Options
            </span>
          </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent
          class="w-56"
          align="start">
          <DropdownMenuGroup>
            <DropdownMenuItem
              v-for="item in viewItems"
              :key="item.id"
              @click="changeView(item.id)">
              <span class="flex items-center gap-2">
                <Icon :icon="item.icon" />
                {{ item.label }}
              </span>
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger as-child>
                    <DropdownMenuShortcut @click.stop.prevent="toggleFavorite(item.id)">
                      <Icon :icon="item.isFavorite.value ? 'fluent-emoji:star' : 'ph:star-bold'" />
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

    <div class="flex items-center">
      <h1 class="text-lg font-semibold md:text-2xl">
        {{ currentView === 'devices' ? 'Devices' : currentView.charAt(0).toUpperCase() + currentView.slice(1) }}
      </h1>
    </div>

    <div
      v-if="currentView === 'devices'"
      class="flex items-center justify-center flex-1 border border-dashed rounded-lg shadow-sm">
      <div class="flex flex-col items-center gap-1 text-center">
        <h3 class="text-2xl font-bold tracking-tight">You have no products</h3>
        <p class="text-sm text-muted-foreground">You can add a device.</p>
        <Button class="mt-4">Add Device</Button>
      </div>
    </div>

    <div
      v-else-if="currentView === 'commandGroups'"
      class="flex items-center justify-center flex-1 border border-dashed rounded-lg shadow-sm">
      <div class="flex flex-col items-center gap-1 text-center">
        <h3 class="text-2xl font-bold tracking-tight">Command Groups View</h3>
        <p class="text-sm text-muted-foreground">Details for command groups will be shown here.</p>
      </div>
    </div>

    <div
      v-else-if="currentView === 'commands'"
      class="flex items-center justify-center flex-1 border border-dashed rounded-lg shadow-sm">
      <div class="flex flex-col items-center gap-1 text-center">
        <h3 class="text-2xl font-bold tracking-tight">Commands View</h3>
        <p class="text-sm text-muted-foreground">Details for commands will be shown here.</p>
      </div>
    </div>

    <!-- Add similar blocks for other views (templates, vendors, tags) -->
  </main>
</template>

<style scoped>
.topRow {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding-left: 20px;
  padding-right: 20px;
  height: 48px;
}
</style>
