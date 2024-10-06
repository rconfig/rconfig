<script setup>
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbSeparator } from '@/components/ui/breadcrumb';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import { Icon } from '@iconify/vue';
import { ref } from 'vue';
import { useColorMode } from '@vueuse/core';
import { usePanelStore } from '@/stores/panelStore'; // Import the Pinia store

const svgIshoveringOpen = ref(false);
const mode = useColorMode();
const panelStore = usePanelStore(); // Access the panel store

defineProps({
  panelRef: {
    type: Object,
    default: null
  }
});

function setHoveringOpen(state) {
  svgIshoveringOpen.value = state;
}

function collapsePanel() {
  panelStore.panelRef?.isCollapsed ? panelStore.panelRef?.expand() : panelStore.panelRef?.collapse();
  svgIshoveringOpen.value = false;
}
</script>

<style scoped>
.top-nav-div {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: flex-start;
  gap: 8px;
}
/* open nav icon */
.dbUOim[data-hovering='false'] {
  transform: translateX(-6px) scale(0.9);
  opacity: 0;
}

.dbUOim {
  transition:
    transform 200ms ease 0s,
    opacity 120ms ease 0s;
}

.ekwKMA[data-hovering='false'] {
  transform: translateX(-6px);
}

.ekwKMA {
  transition: all 200ms ease 0s;
}

.hxMVRj[data-hovering='false'] {
  transform: translateX(-4px);
  opacity: 0;
}

.hxMVRj {
  transition:
    transform 200ms ease 0s,
    opacity 120ms ease 0s;
}
</style>

<template>
  <nav class="dark:bg-rcgray-900">
    <div class="relative flex items-center justify-between w-full max-w-full p-2">
      <div class="flex items-center ml-4">
        <button
          aria-label="Collapse sidebar"
          data-state="closed"
          :class="{ 'mr-4': panelStore.panelRef?.isCollapsed }"
          v-if="panelStore.panelRef?.isCollapsed"
          @click="collapsePanel()">
          <svg
            width="18"
            height="18"
            viewBox="0 0 18 18"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            @mouseover="setHoveringOpen(true)"
            @mouseleave="setHoveringOpen(false)">
            <path
              fill-rule="evenodd"
              clip-rule="evenodd"
              d="M6.27374 1.90015L11.7262 1.90015C12.5441 1.90014 13.1945 1.90014 13.7192 1.94301C14.2566 1.98692 14.7148 2.07875 15.1344 2.29252C15.8117 2.63767 16.3625 3.1884 16.7076 3.86578C16.9214 4.28533 17.0132 4.74353 17.0571 5.28089C17.1 5.80565 17.1 6.45607 17.1 7.27391V10.7264C17.1 11.5442 17.1 12.1946 17.0571 12.7194C17.0132 13.2568 16.9214 13.715 16.7076 14.1345C16.3625 14.8119 15.8117 15.3626 15.1344 15.7078C14.7148 15.9215 14.2566 16.0134 13.7192 16.0573C13.1945 16.1002 12.5441 16.1002 11.7262 16.1001H6.27376C5.45592 16.1002 4.8055 16.1002 4.28074 16.0573C3.74338 16.0134 3.28518 15.9215 2.86563 15.7078C2.18824 15.3626 1.63751 14.8119 1.29237 14.1345C1.0786 13.715 0.986763 13.2568 0.942859 12.7194C0.899985 12.1946 0.899989 11.5442 0.899994 10.7264L0.899994 7.27389C0.899989 6.45606 0.899985 5.80565 0.942859 5.28089C0.986763 4.74353 1.0786 4.28533 1.29237 3.86578C1.63751 3.1884 2.18824 2.63767 2.86563 2.29252C3.28518 2.07875 3.74338 1.98692 4.28074 1.94301C4.80549 1.90014 5.45591 1.90014 6.27374 1.90015ZM4.37846 3.13903C3.91531 3.17687 3.6326 3.24852 3.41042 3.36173C2.95883 3.59183 2.59167 3.95898 2.36158 4.41057C2.24837 4.63276 2.17672 4.91546 2.13887 5.37861C2.10046 5.84877 2.09999 6.45017 2.09999 7.30015L2.09999 10.7001C2.09999 11.5501 2.10046 12.1515 2.13887 12.6217C2.17672 13.0848 2.24837 13.3675 2.36158 13.5897C2.59167 14.0413 2.95883 14.4085 3.41042 14.6386C3.6326 14.7518 3.91531 14.8234 4.37846 14.8613C4.84862 14.8997 5.45001 14.9001 6.29999 14.9001L11.7 14.9001C12.55 14.9001 13.1514 14.8997 13.6215 14.8613C14.0847 14.8234 14.3674 14.7518 14.5896 14.6386C15.0412 14.4085 15.4083 14.0413 15.6384 13.5897C15.7516 13.3675 15.8233 13.0848 15.8611 12.6217C15.8995 12.1515 15.9 11.5501 15.9 10.7001L15.9 7.30015C15.9 6.45017 15.8995 5.84877 15.8611 5.37861C15.8233 4.91546 15.7516 4.63276 15.6384 4.41057C15.4083 3.95898 15.0412 3.59183 14.5896 3.36173C14.3674 3.24852 14.0847 3.17687 13.6215 3.13903C13.1514 3.10061 12.55 3.10015 11.7 3.10015L6.29999 3.10015C5.45002 3.10015 4.84862 3.10061 4.37846 3.13903Z"
              fill="#86888D"></path>
            <path
              fill-rule="evenodd"
              clip-rule="evenodd"
              d="M7.2 15.2252L7.2 2.72522H8.4L8.4 15.2252H7.2Z"
              fill="#86888D"
              :data-hovering="svgIshoveringOpen"
              class="sc-jFGlJG dbUOim"></path>
            <path
              fill-rule="evenodd"
              clip-rule="evenodd"
              d="M12.1757 6.55086C12.41 6.31654 12.7899 6.31654 13.0243 6.55086L14.8243 8.35085C14.9368 8.46338 15 8.61599 15 8.77512C15 8.93425 14.9368 9.08686 14.8243 9.19938L13.0243 10.9994C12.7899 11.2337 12.41 11.2337 12.1757 10.9994C11.9414 10.7651 11.9414 10.3852 12.1757 10.1509L12.9515 9.37512H9.89999C9.56862 9.37512 9.29999 9.10649 9.29999 8.77512C9.29999 8.44375 9.56862 8.17512 9.89999 8.17512H12.9515L12.1757 7.39939C11.9414 7.16507 11.9414 6.78517 12.1757 6.55086Z"
              fill="#86888D"
              :data-hovering="svgIshoveringOpen"
              class="sc-kZbWFF ekwKMA"></path>
            <path
              fill-rule="evenodd"
              clip-rule="evenodd"
              d="M3.375 5.42544C3.375 5.09407 3.64363 4.82544 3.975 4.82544H5.325C5.65637 4.82544 5.925 5.09407 5.925 5.42544C5.925 5.75681 5.65637 6.02544 5.325 6.02544H3.975C3.64363 6.02544 3.375 5.75681 3.375 5.42544Z"
              fill="#86888D"
              :data-hovering="svgIshoveringOpen"
              class="sc-egvMOQ hxMVRj"></path>
            <path
              fill-rule="evenodd"
              clip-rule="evenodd"
              d="M3.375 7.67434C3.375 7.34297 3.64363 7.07434 3.975 7.07434H5.325C5.65637 7.07434 5.925 7.34297 5.925 7.67434C5.925 8.00571 5.65637 8.27434 5.325 8.27434H3.975C3.64363 8.27434 3.375 8.00571 3.375 7.67434Z"
              fill="#86888D"
              :data-hovering="svgIshoveringOpen"
              class="sc-egvMOQ hxMVRj"></path>
          </svg>
        </button>

        <Breadcrumb>
          <BreadcrumbList>
            <BreadcrumbItem>
              <BreadcrumbLink href="/">Home</BreadcrumbLink>
            </BreadcrumbItem>
            <BreadcrumbSeparator>
              <Icon icon="radix-icons:slash" />
            </BreadcrumbSeparator>
            <BreadcrumbItem>
              <BreadcrumbLink href="/inventory">Inventory</BreadcrumbLink>
            </BreadcrumbItem>
          </BreadcrumbList>
        </Breadcrumb>
      </div>

      <div class="mt-1 top-nav-div">
        <Button variant="ghost">
          <Icon
            icon="carbon:help"
            class="absolute h-[1.2rem] w-[1.2rem]" />
        </Button>
        <Button variant="ghost">
          <Icon
            icon="carbon:search"
            class="absolute h-[1.2rem] w-[1.2rem]" />
        </Button>

        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="ghost">
              <Icon
                icon="radix-icons:moon"
                class="h-[1.2rem] w-[1.2rem] rotate-0 scale-100 transition-all dark:-rotate-90 dark:scale-0" />
              <Icon
                icon="radix-icons:sun"
                class="absolute h-[1.2rem] w-[1.2rem] rotate-90 scale-0 transition-all dark:rotate-0 dark:scale-100" />
              <span class="sr-only">Toggle theme</span>
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end">
            <DropdownMenuItem @click="mode = 'light'">Light</DropdownMenuItem>
            <DropdownMenuItem @click="mode = 'dark'">Dark</DropdownMenuItem>
            <DropdownMenuItem @click="mode = 'auto'">System</DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>

        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="ghost">
              <Icon
                icon="radix-icons:dots-vertical"
                class="" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent>
            <DropdownMenuLabel>My Account</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem>Profile</DropdownMenuItem>
            <DropdownMenuItem>Billing</DropdownMenuItem>
            <DropdownMenuItem>Team</DropdownMenuItem>
            <DropdownMenuItem class="cursor-pointer hover:bg-gray-800">
              <router-link
                href="/logout"
                method="post">
                Sign out
              </router-link>
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
    </div>
  </nav>
</template>
