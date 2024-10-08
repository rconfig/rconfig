<script setup>
defineProps({});
</script>

<template>
  <div class="border-t border-b topRow">
    <header class="sticky top-0 flex items-center h-12 gap-4 px-8">
      <nav class="flex-col hidden font-medium text-md md:flex md:flex-row md:items-center md:text-sm">
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
    </header>
  </div>
</template>
