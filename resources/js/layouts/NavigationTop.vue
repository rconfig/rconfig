<script setup>
import NavOpenButton from '@/pages/Shared/NavOpenButton.vue'; // Import the NavOpenButton component
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbSeparator } from '@/components/ui/breadcrumb';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { ref, computed } from 'vue';
import { useColorMode } from '@vueuse/core';
import { usePanelStore } from '@/stores/panelStore'; // Import the Pinia store
import { useRoute, useRouter } from 'vue-router';

const mode = useColorMode();
const panelStore = usePanelStore(); // Access the panel store
const route = useRoute();
const router = useRouter();

defineProps({
  panelRef: {
    type: Object,
    default: null
  }
});

const breadcrumbs = computed(() => {
  return route.meta.breadcrumb || [];
});

const navPanelBtnState = computed(() => {
  return panelStore.panelRef?.isCollapsed;
});

function toggleTheme() {
  if (mode.value === 'light') {
    mode.value = 'dark';
  } else if (mode.value === 'dark') {
    mode.value = 'light';
  }
}

function navToUpgrade() {
  router.push({ name: 'settings-about' });
}

function logout() {
  console.log('logout');
  axios
    .post('/logout')
    .then(response => {
      window.location.href = '/login';
    })
    .catch(error => {
      console.log(error);
    });
}
function openNav() {
  panelStore.panelRef?.isCollapsed ? panelStore.panelRef?.expand() : panelStore.panelRef?.collapse();
}
</script>

<template>
  <nav class="dark:bg-rcgray-900">
    <div class="relative flex items-center justify-between w-full max-w-full py-1 p-x-2">
      <div class="flex items-center ml-4">
        <NavOpenButton
          @openNav="openNav()"
          :navPanelBtnState="navPanelBtnState" />

        <!-- Breadcrumb -->
        <Breadcrumb>
          <BreadcrumbList>
            <BreadcrumbItem
              v-for="(item, index) in breadcrumbs"
              :key="index">
              <BreadcrumbLink
                v-if="item.link"
                :href="item.link">
                {{ item.label }}
              </BreadcrumbLink>
              <span v-else>{{ item.label }}</span>
              <BreadcrumbSeparator v-if="index < breadcrumbs.length - 1">
                <Icon icon="radix-icons:slash" />
              </BreadcrumbSeparator>
            </BreadcrumbItem>
          </BreadcrumbList>
        </Breadcrumb>
        <!-- Breadcrumb -->
      </div>

      <div class="mt-1 top-nav-div">
        <TooltipProvider>
          <Tooltip>
            <TooltipTrigger as-child>
              <Button
                :class="mode === 'dark' ? 'hover:bg-rcgray-600' : 'hover:bg-rcgray-300'"
                @click="navToUpgrade()"
                variant="ghost">
                <Icon
                  icon="carbon:help"
                  class="absolute h-[1.2rem] w-[1.2rem]" />
              </Button>
            </TooltipTrigger>
            <TooltipContent class="text-white bg-rcgray-800">
              <p>About</p>
            </TooltipContent>
          </Tooltip>
        </TooltipProvider>

        <TooltipProvider>
          <Tooltip>
            <TooltipTrigger as-child>
              <Button
                variant="ghost"
                :class="mode === 'dark' ? 'hover:bg-rcgray-600' : 'hover:bg-rcgray-300'"
                @click="toggleTheme()">
                <Transition name="fade">
                  <Icon
                    v-if="mode === 'light'"
                    icon="radix-icons:moon"
                    class="absolute h-[1.2rem] w-[1.2rem] rotate-0 scale-100 transition-all dark:-rotate-90 dark:scale-0" />
                  <Icon
                    v-else-if="mode === 'dark'"
                    icon="radix-icons:sun"
                    class="absolute h-[1.2rem] w-[1.2rem] rotate-90 scale-0 transition-all dark:rotate-0 dark:scale-100" />
                </Transition>
              </Button>
            </TooltipTrigger>
            <TooltipContent class="text-white bg-rcgray-800">
              <p>{{ mode === 'dark' ? 'Switch to light theme' : 'Switch to dark theme' }}</p>
            </TooltipContent>
          </Tooltip>
        </TooltipProvider>

        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="ghost">
              <Icon
                icon="radix-icons:dots-vertical"
                class="" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent>
            <!-- <DropdownMenuLabel>My Account</DropdownMenuLabel> -->
            <!-- <DropdownMenuSeparator /> -->
            <DropdownMenuItem class="p-0 cursor-pointer hover:bg-gray-800">
              <router-link
                type="button"
                variant="ghost"
                :to="'/settings/users/' + $userId"
                class="flex items-center py-2 ml-2">
                <UserIcon class="mr-2" />
                My Profile
              </router-link>
            </DropdownMenuItem>
            <DropdownMenuItem class="p-0 cursor-pointer hover:bg-gray-800">
              <Button
                variant="ghost"
                class="flex justify-start w-full py-1 pl-2"
                @click.prevent="logout()">
                <LogoutIcon class="mr-2" />
                Sign out
              </Button>
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
    </div>
  </nav>
</template>
