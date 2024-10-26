<script setup>
import { DropdownMenu, DropdownMenuContent, DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuPortal, DropdownMenuSeparator, DropdownMenuShortcut, DropdownMenuSub, DropdownMenuSubContent, DropdownMenuSubTrigger, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import AddEditPane from '@/layouts/AddEditPane.vue';
import Command from '@/pages/Inventory/Commands/Main.vue';
import CommandGroups from '@/pages/Inventory/CommandGroups/Main.vue';
import Devices from '@/pages/Inventory/Devices/Main.vue';
import DeviceViewPane from '@/pages/Inventory/Devices/DeviceView/DeviceViewPane.vue';
import Tags from '@/pages/Inventory/Tags/Main.vue';
import Template from '@/pages/Inventory/Templates/Main.vue';
import TemplateAddEditPane from '@/pages/Inventory/Templates/TemplateAddEditPane.vue';
import Vendors from '@/pages/Inventory/Vendors/Main.vue';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { ref, onMounted } from 'vue';
import { useFavoritesStore } from '@/stores/favorites';
import { useRoute, useRouter } from 'vue-router'; // Import the useRoute from Vue Router

defineProps({});

const addEditPane = ref(null);
const addEditPaneEditId = ref(0);
const addEditPaneKey = ref(1);
const favoritesStore = useFavoritesStore();
const currentView = ref(localStorage.getItem('inventorySelectedView') || 'devices');
const route = useRoute();
const router = useRouter();

const viewItems = [
  { id: 'devices', label: 'Devices', icon: 'DeviceIcon', isFavorite: ref(false), route: '/devices' },
  { id: 'commandgroups', label: 'Command Groups', icon: 'CommandGroupIcon', isFavorite: ref(false), route: '/commandgroups' },
  { id: 'commands', label: 'Commands', icon: 'CommandsIcon', isFavorite: ref(false), route: '/commands' },
  { id: 'templates', label: 'Templates', icon: 'TemplateIcon', isFavorite: ref(false), route: '/templates' },
  { id: 'vendors', label: 'Vendors', icon: 'VendorIcon', isFavorite: ref(false), route: '/vendors' },
  { id: 'tags', label: 'Tags', icon: 'TagIcon', isFavorite: ref(false), route: '/tags' }
];

onMounted(() => {
  if (route.params.view) {
    changeView(route.params.view);
  } // Set currentView if path is not Inventory
  else if (!route.path.includes('inventory')) {
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

function launchAddEdit(e) {
  addEditPane.value = e.type;
  addEditPaneEditId.value = e.id;
  addEditPaneKey.value += 1;
}

function closeAddEditPane() {
  addEditPane.value = null;
  addEditPaneKey.value += 1;
}
</script>

<template>
  <main class="flex flex-col flex-1 gap-2 dark:bg-rcgray-900">
    <AddEditPane
      :key="addEditPaneKey"
      :editId="addEditPaneEditId"
      :name="addEditPane"
      v-if="addEditPane"
      @close="closeAddEditPane()">
      <template #default>
        <transition name="fade">
          <TemplateAddEditPane
            v-if="addEditPane === 'template'"
            :editId="addEditPaneEditId"
            @close="closeAddEditPane" />
        </transition>

        <transition name="fade">
          <DeviceViewPane
            v-if="addEditPane === 'device'"
            :editId="addEditPaneEditId"
            @close="closeAddEditPane" />
        </transition>
      </template>
    </AddEditPane>

    <div
      class="border-t border-b topRow"
      v-if="addEditPane === null">
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

    <div v-if="!addEditPane">
      <Devices
        v-if="currentView === 'devices'"
        @viewDeviceDetailsPane="launchAddEdit($event)"></Devices>
      <CommandGroups v-if="currentView === 'commandgroups'"></CommandGroups>
      <Command v-if="currentView === 'commands'"></Command>
      <Template
        v-if="currentView === 'templates'"
        @createTemplate="launchAddEdit($event)"
        @viewTemplateDetailsPane="launchAddEdit($event)"></Template>
      <Vendors v-if="currentView === 'vendors'"></Vendors>
      <Tags v-if="currentView === 'tags'"></Tags>
    </div>
  </main>
</template>
