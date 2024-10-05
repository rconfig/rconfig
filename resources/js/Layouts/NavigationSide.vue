<script setup>
import QuickActions from '@/Layouts/Components/QuickActions.vue';
import SheetHelp from '@/components/Sheets/SheetHelp.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { ResizablePanel } from '@/components/ui/resizable';
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { usePanelStore } from '../stores/panelStore'; // Import the Pinia store
import { useSheetStore } from '@/stores/sheet';
import { useFavoritesStore } from '@/stores/favorites';
const favoritesStore = useFavoritesStore();

const isOpen1 = ref(true);
const isOpen2 = ref(false);
const svgIshoveringClose = ref(false);
const panelStore = usePanelStore(); // Access the panel store
const panelElement = ref(null);
const sheetStore = useSheetStore();
const { openSheet, closeSheet, isSheetOpen } = sheetStore;

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
});

function setHoveringClose(state) {
  svgIshoveringClose.value = state;
}

const goToRconfig = () => {
  window.open('https://rconfig.com', '_blank');
};

onUnmounted(() => {
  // Clean up the event listener when the component is unmounted
  mobileQuery.removeEventListener('change', handleBreakpointChange);
});
</script>

<style scoped>
/* close nav icon */

.nav-icon[data-hovering='true'] {
  transform: translateX(-6px) scale(0.9);
  opacity: 0;
}

.nav-icon {
  transition:
    transform 200ms ease 0s,
    opacity 120ms ease 0s;
}

.nac-icon-xform[data-hovering='true'] {
  transform: translateX(-4px);
  opacity: 0;
}

.nac-icon-xform {
  transition:
    transform 200ms ease 0s,
    opacity 120ms ease 0s;
}

.nav-icon-form-2[data-hovering='true'] {
  transform: translate(0px);
  opacity: 1;
}

.nav-icon-form-2 {
  transition: all 200ms ease 0s;
}
/* close nav icon */
</style>

<template>
  <resizable-panel
    id="nav-panel-1"
    :default-size="17"
    :max-size="17"
    :min-size="10"
    collapsible
    :collapsed-size="0"
    ref="panelElement"
    class="dark:bg-rcgray-800">
    <div class="grid min-h-screen">
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
              <button
                aria-label="Collapse sidebar"
                data-state="closed"
                @click="panelElement?.isCollapsed ? panelElement?.expand() : panelElement?.collapse()">
                <svg
                  width="18"
                  height="18"
                  viewBox="0 0 18 18"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                  @mouseover="setHoveringClose(true)"
                  @mouseleave="setHoveringClose(false)">
                  <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M6.27218 1.90039L11.7247 1.90039C12.5425 1.90039 13.1929 1.90038 13.7177 1.94326C14.2551 1.98716 14.7133 2.079 15.1328 2.29277C15.8102 2.63791 16.3609 3.18864 16.7061 3.86602C16.9198 4.28557 17.0117 4.74378 17.0556 5.28114C17.0984 5.80589 17.0984 6.45632 17.0984 7.27415V10.7266C17.0984 11.5445 17.0984 12.1949 17.0556 12.7196C17.0117 13.257 16.9198 13.7152 16.7061 14.1348C16.3609 14.8121 15.8102 15.3629 15.1328 15.708C14.7133 15.9218 14.2551 16.0136 13.7177 16.0575C13.1929 16.1004 12.5425 16.1004 11.7247 16.1004H6.2722C5.45436 16.1004 4.80394 16.1004 4.27918 16.0575C3.74182 16.0136 3.28362 15.9218 2.86407 15.708C2.18669 15.3629 1.63596 14.8121 1.29081 14.1348C1.07704 13.7152 0.985207 13.257 0.941303 12.7196C0.898429 12.1949 0.898433 11.5445 0.898438 10.7266L0.898438 7.27414C0.898433 6.45631 0.898429 5.80589 0.941303 5.28114C0.985207 4.74378 1.07704 4.28557 1.29081 3.86602C1.63596 3.18864 2.18669 2.63791 2.86407 2.29277C3.28362 2.079 3.74182 1.98716 4.27918 1.94326C4.80394 1.90038 5.45436 1.90039 6.27218 1.90039ZM4.3769 3.13927C3.91375 3.17711 3.63105 3.24877 3.40886 3.36198C2.95727 3.59207 2.59012 3.95922 2.36002 4.41081C2.24681 4.633 2.17516 4.9157 2.13732 5.37886C2.0989 5.84901 2.09844 6.45041 2.09844 7.30039L2.09844 10.7004C2.09844 11.5504 2.0989 12.1518 2.13732 12.6219C2.17516 13.0851 2.24681 13.3678 2.36002 13.59C2.59012 14.0416 2.95727 14.4087 3.40886 14.6388C3.63105 14.752 3.91375 14.8237 4.3769 14.8615C4.84706 14.8999 5.44846 14.9004 6.29844 14.9004L11.6984 14.9004C12.5484 14.9004 13.1498 14.8999 13.62 14.8615C14.0831 14.8237 14.3658 14.752 14.588 14.6388C15.0396 14.4087 15.4068 14.0416 15.6369 13.59C15.7501 13.3678 15.8217 13.0851 15.8596 12.6219C15.898 12.1518 15.8984 11.5504 15.8984 10.7004L15.8984 7.30039C15.8984 6.45041 15.898 5.84901 15.8596 5.37886C15.8217 4.9157 15.7501 4.633 15.6369 4.41081C15.4068 3.95922 15.0396 3.59207 14.588 3.36198C14.3658 3.24877 14.0831 3.17711 13.62 3.13927C13.1498 3.10086 12.5484 3.10039 11.6984 3.10039L6.29844 3.10039C5.44846 3.10039 4.84706 3.10086 4.3769 3.13927Z"
                    fill="#86888D"
                    :data-hovering="svgIshoveringClose"></path>
                  <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M7.19531 15.2246L7.19531 2.72461H8.39531L8.39531 15.2246H7.19531Z"
                    fill="#86888D"
                    :data-hovering="svgIshoveringClose"
                    class="nav-icon"></path>
                  <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M3.375 5.42617C3.375 5.0948 3.64363 4.82617 3.975 4.82617H5.325C5.65637 4.82617 5.925 5.0948 5.925 5.42617C5.925 5.75754 5.65637 6.02617 5.325 6.02617H3.975C3.64363 6.02617 3.375 5.75754 3.375 5.42617Z"
                    fill="#86888D"
                    :data-hovering="svgIshoveringClose"
                    class="nac-icon-xform"></path>
                  <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M3.375 7.67422C3.375 7.34285 3.64363 7.07422 3.975 7.07422H5.325C5.65637 7.07422 5.925 7.34285 5.925 7.67422C5.925 8.00559 5.65637 8.27422 5.325 8.27422H3.975C3.64363 8.27422 3.375 8.00559 3.375 7.67422Z"
                    fill="#86888D"
                    :data-hovering="svgIshoveringClose"
                    class="nac-icon-xform"></path>
                  <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M5.82739 11.2247C5.59308 11.459 5.21318 11.459 4.97886 11.2247L3.17886 9.42466C2.94455 9.19034 2.94455 8.81044 3.17886 8.57613L4.97886 6.77613C5.21317 6.54181 5.59307 6.54181 5.82739 6.77613C6.0617 7.01044 6.0617 7.39034 5.82739 7.62465L5.05165 8.40039H8.10312C8.4345 8.40039 8.70312 8.66902 8.70312 9.00039C8.70312 9.33176 8.4345 9.60039 8.10312 9.60039H5.05166L5.82739 10.3761C6.0617 10.6104 6.0617 10.9903 5.82739 11.2247Z"
                    fill="#86888D"
                    :data-hovering="svgIshoveringClose"
                    class="nav-icon-form-2"></path>
                </svg>
              </button>
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
                  <Icon
                    icon="carbon:notification"
                    class="text-rcgray-400" />
                  <div class="p-1 ml-2 text-left text-gray-200"><div>Notifications</div></div>
                </router-link>
                <router-link
                  to="/"
                  class="transition ease-in-out delay-50 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1"
                  :class="{ 'font-semibold text-sm bg-rcgray-600': $route.name === 'Home' }">
                  <Icon
                    icon="carbon:home"
                    class="text-rcgray-400" />
                  <div class="p-1 ml-2 text-left text-gray-200"><div>Dashboard</div></div>
                </router-link>

                <router-link
                  to="/inventory"
                  class="transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1"
                  :class="{ 'font-semibold text-sm bg-rcgray-600': $route.name === 'scheduled-tasks' }">
                  <Icon
                    icon="carbon:network-1"
                    class="text-rcgray-400" />
                  <div class="p-1 ml-2 text-left text-gray-200"><div>Inventory</div></div>
                </router-link>
                <router-link
                  to="/scheduled-tasks"
                  class="transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1"
                  :class="{ 'font-semibold text-sm bg-rcgray-600': $route.name === 'scheduled-tasks' }">
                  <Icon
                    icon="carbon:event-schedule"
                    class="text-rcgray-400" />
                  <div class="p-1 ml-2 text-left text-gray-200"><div>Tasks</div></div>
                </router-link>
                <router-link
                  to="/settings/users"
                  class="transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1"
                  :class="{ 'font-semibold text-sm bg-rcgray-600': $route.name === 'users' }">
                  <Icon
                    icon="carbon:user-settings"
                    class="text-rcgray-400" />
                  <div class="p-1 ml-2 text-left text-gray-200"><div>Users</div></div>
                </router-link>
                <router-link
                  to="/settings"
                  class="transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1"
                  :class="{ 'font-semibold text-sm bg-rcgray-600': $route.name === 'settings' }">
                  <Icon
                    icon="carbon:settings-edit"
                    class="text-rcgray-400" />
                  <div class="p-1 ml-2 text-left text-gray-200"><div>Settings</div></div>
                </router-link>
                <Collapsible
                  v-model:open="isOpen1"
                  class="w-full my-4">
                  <div class="flex items-center justify-between">
                    <CollapsibleTrigger as-child>
                      <Button
                        variant="ghost"
                        size="sm"
                        class="pb-2 pl-0">
                        <div
                          class="flex items-center cursor-pointer"
                          type="button"
                          aria-expanded="true"
                          data-state="open">
                          <Icon
                            icon="fluent:chevron-right-28-filled"
                            v-if="!isOpen1" />
                          <Icon
                            icon="fluent:chevron-down-48-filled"
                            v-if="isOpen1" />
                          <div
                            class="text-left"
                            data-truncate="false"
                            data-numeric="false"
                            data-uppercase="false"
                            style="color: rgb(134, 136, 141)">
                            Favorites
                          </div>
                        </div>
                      </Button>
                    </CollapsibleTrigger>
                  </div>

                  <CollapsibleContent>
                    <router-link
                      v-for="item in favoritesStore.favorites"
                      :key="item.id"
                      :to="item.route"
                      class="transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1"
                      :class="{ 'font-semibold text-sm bg-rcgray-600': $route.name === item.route }">
                      <Icon
                        :icon="item.icon"
                        class="text-rcgray-400" />
                      <div class="p-1 ml-2 text-left text-gray-200">
                        <div>{{ item.label }}</div>
                      </div>
                    </router-link>
                  </CollapsibleContent>
                </Collapsible>
              </nav>
            </div>
          </div>
          <div class="p-4 mt-auto">
            <Card class="dark:bg-rcgray-600">
              <CardHeader class="p-2 pt-0 md:p-4">
                <CardTitle>Upgrade to Pro</CardTitle>
                <CardDescription>Unlock all features and get unlimited access to our support team.</CardDescription>
              </CardHeader>
              <CardContent class="p-2 pt-0 md:p-4 md:pt-0">
                <Button
                  size="sm"
                  class="w-full py-2 hover:bg-rcgray-300 hover:animate-pulse"
                  @click="goToRconfig()">
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
