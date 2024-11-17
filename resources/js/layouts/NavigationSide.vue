<script setup>
const { toastSuccess, toastError, toastInfo, toastWarning, toastDefault } = useToaster();
import ExternalToolDialog from '@/layouts/Components/ExternalToolDialog.vue';
import NavCloseButton from '@/pages/Shared/NavCloseButton.vue';
import QuickActions from '@/layouts/Components/QuickActions.vue';
import SheetHelp from '@/layouts/Components/SheetHelp.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { ref, onMounted, onUnmounted, inject, watch } from 'vue';
import { useExternalLinksStore } from '@/stores/externalLinksStore';
import { useFavoritesStore } from '@/stores/favorites';
import { usePanelStore } from '../stores/panelStore'; // Import the Pinia store
import { useRouter } from 'vue-router'; // Import the useRoute from Vue Router
import { useSheetStore } from '@/stores/sheetActions';
import { useToaster } from '@/composables/useToaster'; // Import the composable

const userid = inject('userid');
const router = useRouter();

const favoritesStore = useFavoritesStore();
const externalLinksStore = useExternalLinksStore();
const externalLinks = ref([]); // This will store the links for this component

// Use localStorage to persist the state of the collapsibles
const sideNavExtLinksIsOpen = ref(JSON.parse(localStorage.getItem('sideNavExtLinksIsOpen')) ?? true);
const sideNavFavLinksIsOpen = ref(JSON.parse(localStorage.getItem('sideNavFavLinksIsOpen')) ?? true);

watch(sideNavExtLinksIsOpen, newVal => {
  localStorage.setItem('sideNavExtLinksIsOpen', JSON.stringify(newVal));
});

watch(sideNavFavLinksIsOpen, newVal => {
  localStorage.setItem('sideNavFavLinksIsOpen', JSON.stringify(newVal));
});

const panelStore = usePanelStore(); // Access the panel store
const panelElement = ref(null);
const sheetStore = useSheetStore();
const { openSheet, closeSheet, isSheetOpen } = sheetStore;
const externalLinksDialogKey = ref(0);

defineProps({});

// Media query for a certain breakpoint (e.g., max-width: 768px for mobile devices)
const mobileQuery = window.matchMedia('(max-width: 768px)');

// Function to handle panel state based on screen size
function handleBreakpointChange() {
  if (mobileQuery.matches) {
    // If the screen width is less than or equal to 768px, close the panel
    panelStore.panelRef?.collapse();
  } else {
    // If the screen width is greater than 768px, expand the panel (optional)
    panelStore.panelRef?.expand();
  }
}

onMounted(() => {
  panelStore.panelRef = panelElement; // Set the panelElement ref globally via Pinia

  // Add event listener for window resize
  mobileQuery.addEventListener('change', handleBreakpointChange);

  // Call it initially to set the correct state on load
  handleBreakpointChange();

  loadLinksFromStoreOrDb();
});

const navToSettingsUpgrade = () => {
  router.push({ name: 'settings-upgrade' });
};

onUnmounted(() => {
  // Clean up the event listener when the component is unmounted
  mobileQuery.removeEventListener('change', handleBreakpointChange);
});

function closeExtDialog() {
  externalLinksDialogKey.value += 1;
  loadLinksFromStoreOrDb();
}

function loadLinksFromStoreOrDb() {
  // Check if the store already has links

  if (externalLinksStore.links.length > 0) {
    // Use the links from the store
    externalLinks.value = externalLinksStore.links;
  } else {
    axios
      .get(`/api/users/get-external-links/${userid}`)
      .then(response => {
        // Store the fetched links in Pinia for future use
        externalLinksStore.setLinks(response.data);

        // Assign the links to the local reference
        externalLinks.value = response.data;
      })
      .catch(error => {
        console.error('Error fetching external links:', error);
      });
  }
}

const removeExternalLink = async name => {
  try {
    // Make API request to delete the link by name
    await axios
      .post(`/api/users/remove-external-link`, { name: encodeURIComponent(name) })
      .then(response => {
        // Store the fetched links in Pinia for future use
        externalLinksStore.setLinks(response.data);

        // Assign the links to the local reference
        externalLinks.value = response.data;

        // // Update the local reference to reflect changes
        loadLinksFromStoreOrDb();
        console.log('Link removed successfully');
        toastSuccess('External Link', 'Link removed successfully');
      })
      .catch(error => {
        console.error('Error fetching external links:', error);
        toastError('External Link', 'Error removing link');
      });
  } catch (error) {
    console.error('Error removing link:', error);
    toastError('External Link', 'Error removing link');
  }
};

function closeNav() {
  panelElement?.value.isCollapsed ? panelElement?.value.expand() : panelElement?.value.collapse();
}
</script>

<template>
  <resizable-panel
    id="nav-panel-1"
    :default-size="17"
    :max-size="30"
    :min-size="10"
    collapsible
    :collapsed-size="0"
    ref="panelElement"
    class="dark:bg-rcgray-800">
    <div class="grid min-h-[calc(100vh-2px)]">
      <div class="bg-gray-100 bg-muted/40 md:block">
        <div class="flex flex-col h-full max-h-screen gap-2">
          <div class="flex justify-between w-full max-w-full p-2 border-b">
            <div class="flex items-center my-2">
              <img
                alt="rConfig"
                class="h-6"
                src="https://assets.attio.com/cdn-cgi/image/dpr=2,fit=cover,width=26,height=26/https://assets.attio.com/logos/68bac79b-c492-4aff-878b-72b3d19247a2?etag=1715675750052" />
              <div class="hidden ml-2 font-semibold text-md lg:block">rConfig</div>
              <!-- Hidden on small screens -->
            </div>
            <div class="flex items-center justify-center h-full">
              <NavCloseButton
                class="mr-2"
                @close="closeNav()" />
            </div>
          </div>

          <div class="flex-1">
            <QuickActions />

            <div class="mx-2 hover:transition-all">
              <nav class="grid items-start px-2 text-sm font-medium lg:px-4">
                <router-link
                  to="/notifications"
                  class="transition ease-in-out delay-50 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1"
                  :class="{ 'font-semibold text-sm bg-rcgray-600': $route.name === 'notifications' }">
                  <NotificationIcon />
                  <div class="p-1 ml-2 text-left text-gray-200"><div>Notifications</div></div>
                </router-link>
                <router-link
                  to="/"
                  class="transition ease-in-out delay-50 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1"
                  :class="{ 'font-semibold text-sm bg-rcgray-600': $route.name === 'Home' }">
                  <DashboardIcon />
                  <div class="p-1 ml-2 text-left text-gray-200"><div>Dashboard</div></div>
                </router-link>
                <router-link
                  to="/inventory"
                  class="transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1"
                  :class="{ 'font-semibold text-sm bg-rcgray-600': $route.name === 'inventory' }">
                  <InventoryIcon />
                  <div class="p-1 ml-2 text-left text-gray-200"><div>Inventory</div></div>
                </router-link>
                <router-link
                  to="/tasks"
                  class="transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1"
                  :class="{ 'font-semibold text-sm bg-rcgray-600': $route.name === 'tasks' }">
                  <TasksIcon />
                  <div class="p-1 ml-2 text-left text-gray-200"><div>Tasks</div></div>
                </router-link>
                <router-link
                  to="/settings/users"
                  class="transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1"
                  :class="{ 'font-semibold text-sm bg-rcgray-600': $route.name === 'users' }">
                  <UserIcon />
                  <div class="p-1 ml-2 text-left text-gray-200"><div>Users</div></div>
                </router-link>
                <router-link
                  to="/device/view/configs/0"
                  class="transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1"
                  :class="{ 'font-semibold text-sm bg-rcgray-600': $route.name === 'configtools' }">
                  <ConfigToolsIcon />
                  <div class="p-1 ml-2 text-left text-gray-200"><div>Config Tools</div></div>
                </router-link>
                <router-link
                  to="/settings"
                  class="transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1"
                  :class="{ 'font-semibold text-sm bg-rcgray-600': $route.name === 'settings' }">
                  <SettingsIcon />

                  <div class="p-1 ml-2 text-left text-gray-200"><div>Settings</div></div>
                </router-link>

                <Collapsible
                  v-model:open="sideNavExtLinksIsOpen"
                  class="w-full mt-4">
                  <div class="flex items-center justify-between">
                    <CollapsibleTrigger as-child>
                      <div class="flex items-center w-full">
                        <Button
                          variant="ghost"
                          size="sm"
                          class="w-full pl-0 pr-4">
                          <div
                            class="flex items-center w-full cursor-pointer"
                            type="button"
                            aria-expanded="true"
                            data-state="open">
                            <Icon
                              icon="fluent:chevron-right-28-filled"
                              v-if="!sideNavExtLinksIsOpen" />
                            <Icon
                              icon="fluent:chevron-down-48-filled"
                              v-if="sideNavExtLinksIsOpen" />
                            <div
                              class="ml-2 text-left"
                              data-truncate="false"
                              data-numeric="false"
                              data-uppercase="false"
                              style="color: rgb(134, 136, 141)">
                              <span>External Tools</span>
                            </div>
                          </div>
                        </Button>
                      </div>
                    </CollapsibleTrigger>
                    <ExternalToolDialog
                      @close="closeExtDialog"
                      :key="externalLinksDialogKey" />
                  </div>
                  <CollapsibleContent>
                    <div>
                      <router-link
                        to="/log-viewer"
                        target="_blank"
                        class="transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1">
                        <SysLogViewerIcon />
                        <div class="flex items-center justify-between w-full p-1 ml-2 text-left text-gray-200">
                          <div>System Log Viewer</div>
                          <Icon
                            icon="iconamoon:link-external-duotone"
                            class="text-rcgray-400" />
                        </div>
                      </router-link>
                      <router-link
                        to="/horizon/dashboard"
                        target="_blank"
                        class="transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1">
                        <SysQueueManagerIcon />
                        <div class="flex items-center justify-between w-full p-1 ml-2 text-left text-gray-200">
                          <div>System Queue Manager</div>
                          <Icon
                            icon="iconamoon:link-external-duotone"
                            class="text-rcgray-400" />
                        </div>
                      </router-link>
                      <div class="transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1">
                        <ExternalLinkIcon />
                        <a
                          href="https://www.rconfig.com"
                          target="_blank"
                          class="flex items-center justify-between w-full p-1 ml-2 text-left text-gray-200">
                          <div>rConfig.com</div>
                          <Icon
                            icon="iconamoon:link-external-duotone"
                            class="text-rcgray-400" />
                        </a>
                      </div>
                      <div
                        v-if="externalLinks.length > 0"
                        v-for="link in externalLinks"
                        class="transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1">
                        <Icon
                          :icon="link.icon"
                          class="text-rcgray-400" />
                        <a
                          :href="link.url"
                          target="_blank"
                          class="flex items-center justify-between w-full p-1 ml-2 text-left text-gray-200">
                          <div>{{ link.name }}</div>
                        </a>
                        <Icon
                          icon="ic:outline-remove"
                          class="ml-2 mr-2 cursor-pointer text-muted-foreground hover:text-white"
                          @click="removeExternalLink(link.name)" />
                      </div>
                    </div>
                  </CollapsibleContent>
                </Collapsible>

                <Collapsible
                  v-model:open="sideNavFavLinksIsOpen"
                  class="w-full mb-4">
                  <div class="flex items-center justify-between">
                    <CollapsibleTrigger as-child>
                      <div class="flex items-center w-full">
                        <Button
                          variant="ghost"
                          size="sm"
                          class="w-full pl-0 pr-4">
                          <div
                            class="flex items-center w-full cursor-pointer"
                            type="button"
                            aria-expanded="true"
                            data-state="open">
                            <Icon
                              icon="fluent:chevron-right-28-filled"
                              v-if="!sideNavFavLinksIsOpen" />
                            <Icon
                              icon="fluent:chevron-down-48-filled"
                              v-if="sideNavFavLinksIsOpen" />
                            <div
                              class="ml-2 text-left"
                              data-truncate="false"
                              data-numeric="false"
                              data-uppercase="false"
                              style="color: rgb(134, 136, 141)">
                              Favorites
                            </div>
                          </div>
                        </Button>
                      </div>
                    </CollapsibleTrigger>
                  </div>
                  <CollapsibleContent>
                    <div
                      v-if="favoritesStore.favorites.size == 0"
                      class="ml-6 text-xs text-muted-foreground">
                      No favorites set
                    </div>
                    <div v-else>
                      <router-link
                        v-for="item in favoritesStore.favorites"
                        :key="item.id"
                        :to="item.route"
                        class="transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1"
                        :class="{ 'font-semibold text-sm bg-rcgray-600': $route.name === item.route }">
                        <component :is="item.icon" />
                        <div class="p-1 ml-2 text-left text-gray-200">
                          <div>{{ item.label }}</div>
                        </div>
                      </router-link>
                    </div>
                  </CollapsibleContent>
                </Collapsible>
              </nav>
            </div>
          </div>
          <div class="p-4 mt-auto">
            <Card class="dark:bg-rcgray-900">
              <CardHeader class="p-2 pt-0 md:p-4">
                <CardTitle>Upgrade to Pro</CardTitle>
                <CardDescription>Unlock all features and get unlimited access to our support team.</CardDescription>
              </CardHeader>
              <CardContent class="p-2 pt-0 md:p-4 md:pt-0">
                <Button
                  size="sm"
                  class="w-full py-2 hover:bg-rcgray-300 hover:animate-pulse"
                  @click="navToSettingsUpgrade()">
                  Upgrade
                </Button>
              </CardContent>
            </Card>
          </div>
          <div class="p-4 mt-auto border-t">
            <a
              @click.prevent="openSheet('SheetHelp')"
              class="transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1"
              :class="{ 'font-semibold text-sm bg-rcgray-600': $route.name === 'scheduled-tasks' }">
              <Icon
                icon="carbon:lifesaver"
                class="text-rcgray-400" />
              <div class="p-1 ml-2 text-left text-gray-200"><div>Getting started & Help</div></div>
            </a>

            <SheetHelp />
          </div>
        </div>
      </div>
    </div>
  </resizable-panel>
</template>
