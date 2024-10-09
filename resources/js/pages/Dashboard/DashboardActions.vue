<script setup>
import { ref } from 'vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';

// State to track the visibility of the mobile menu
const isMenuOpen = ref(false);

const props = defineProps({
  licenseInfo: Object
});

// Function to toggle the mobile menu
const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;
};
</script>

<template>
  <div class="border-t border-b topRow">
    <header class="sticky top-0 flex items-center h-12 gap-4 px-8">
      <!-- Hamburger Icon Button for Small Screens -->

      <Popover>
        <PopoverTrigger as-child>
          <button
            @click="toggleMenu"
            variant="outline"
            class="md:hidden">
            <Icon
              :icon="isMenuOpen ? 'material-symbols-light:menu-open' : 'material-symbols-light:menu'"
              class="w-6 h-6" />
          </button>
        </PopoverTrigger>
        <PopoverContent
          class="p-2 w-80"
          align="start">
          <div class="grid gap-4">
            <nav class="flex-col text-sm font-medium md:flex md:flex-row md:items-center md:text-sm">
              <a
                href="#"
                class="flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800">
                <Icon
                  icon="fluent-color:add-circle-28"
                  class="" />
                <span>Create New Device</span>
              </a>
              <a
                href="#"
                class="flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800">
                <Icon
                  icon="fluent-color:apps-24"
                  class="" />
                View Devices
              </a>
              <a
                href="#"
                class="flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800">
                <Icon
                  icon="fluent-color:search-visual-16"
                  class="" />
                Search Configs
              </a>
              <a
                href="#"
                class="flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800">
                <Icon
                  icon="flat-color-icons:delete-database"
                  class="" />
                Purge Failed Configs
              </a>
            </nav>
          </div>
        </PopoverContent>
      </Popover>

      <!-- Navigation Menu - Visible by default on md screens and above -->
      <div class="flex justify-between w-full">
        <nav
          :class="{ hidden: !isMenuOpen, flex: isMenuOpen }"
          class="flex-col hidden font-medium text-md md:flex md:flex-row md:items-center md:text-sm">
          <a
            href="#"
            class="flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800">
            <Icon
              icon="fluent-color:add-circle-28"
              class="" />
            <span>Create New Device</span>
          </a>
          <a
            href="#"
            class="flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800">
            <Icon
              icon="fluent-color:apps-24"
              class="" />
            View Devices
          </a>
          <a
            href="#"
            class="flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800">
            <Icon
              icon="fluent-color:search-visual-16"
              class="" />
            Search Configs
          </a>
          <a
            href="#"
            class="flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800">
            <Icon
              icon="flat-color-icons:delete-database"
              class="" />
            Purge Failed Configs
          </a>
        </nav>
        <div class="flex items-center ml-auto">
          <Icon
            icon="ph:code-duotone"
            class="text-muted-foreground" />
          <span class="ml-2 text-muted-foreground">{{ licenseInfo.data.version }}-Core</span>
        </div>
      </div>
    </header>
  </div>
</template>

<style scoped>
/* Optional: Add some styling for transitions if needed */
nav {
  transition: all 0.3s ease-in-out;
}
</style>
