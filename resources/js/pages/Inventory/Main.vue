<script setup>
import Devices from '@/pages/Inventory/Devices/Main.vue';
import CommandGroups from '@/pages/Inventory/CommandGroups/Main.vue';
import Tags from '@/pages/Inventory/Tags/Main.vue';
import Vendors from '@/pages/Inventory/Vendors/Main.vue';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuPortal, DropdownMenuSeparator, DropdownMenuShortcut, DropdownMenuSub, DropdownMenuSubContent, DropdownMenuSubTrigger, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { ref, onMounted } from 'vue';
import { useFavoritesStore } from '@/stores/favorites';
import { useRoute, useRouter } from 'vue-router'; // Import the useRoute from Vue Router

defineProps({});

const title = 'Dashboard';
const favoritesStore = useFavoritesStore();
const currentView = ref(localStorage.getItem('inventorySelectedView') || 'devices');
const route = useRoute();
const router = useRouter();

const viewItems = [
  { id: 'devices', label: 'Devices', icon: 'fluent-color:org-16', isFavorite: ref(false), route: 'devices' },
  { id: 'commandgroups', label: 'Command Groups', icon: 'fluent-color:search-visual-24', isFavorite: ref(false), route: 'commandgroups' },
  { id: 'commands', label: 'Commands', icon: 'fluent-color:text-edit-style-16', isFavorite: ref(false), route: 'commands' },
  { id: 'templates', label: 'Templates', icon: 'fluent-color:code-block-32', isFavorite: ref(false), route: 'templates' },
  { id: 'vendors', label: 'Vendors', icon: 'fluent-color:apps-16', isFavorite: ref(false), route: 'vendors' },
  { id: 'tags', label: 'Tags', icon: 'fluent-emoji:keycap-hashtag', isFavorite: ref(false), route: 'tags' }
];

onMounted(() => {
  // Set currentView if path is not Inventory
  if (!route.path.includes('inventory')) {
    changeView(route.name);
  }

  viewItems.forEach(item => {
    item.isFavorite.value = favoritesStore.isFavorite(item.id);
  });
});

function changeView(view) {
  localStorage.setItem('inventorySelectedView', view);
  currentView.value = view;
  router.push({ name: view });
}

function toggleFavorite(viewId) {
  const viewItem = viewItems.find(item => item.id === viewId);
  if (viewItem) {
    viewItem.isFavorite.value = !viewItem.isFavorite.value;
    favoritesStore.toggleFavorite(viewItem);
  }
}
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
          <Icon :icon="viewItems.find(item => item.id === currentView)?.icon || ''" />
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

    <Devices v-if="currentView === 'devices'"></Devices>

    <CommandGroups v-if="currentView === 'commandgroups'"></CommandGroups>

    <div
      v-else-if="currentView === 'commands'"
      class="flex items-center justify-center flex-1 border border-dashed rounded-lg shadow-sm">
      <div class="flex flex-col items-center gap-1 text-center">
        <h3 class="text-2xl font-bold tracking-tight">Commands View</h3>
        <p class="text-sm text-muted-foreground">Details for commands will be shown here.</p>
      </div>
    </div>

    <div
      v-else-if="currentView === 'templates'"
      class="flex items-center justify-center flex-1 border border-dashed rounded-lg shadow-sm">
      <div class="flex flex-col items-center gap-1 text-center">
        <h3 class="text-2xl font-bold tracking-tight">Templates View</h3>
        <p class="text-sm text-muted-foreground">Details for templates will be shown here.</p>
      </div>
    </div>

    <Vendors v-if="currentView === 'vendors'"></Vendors>

    <Tags v-if="currentView === 'tags'"></Tags>
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
