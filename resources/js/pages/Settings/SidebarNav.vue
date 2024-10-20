<script setup>
const props = defineProps({
  settingsActivePane: String
});

const sidebarNavItems = [
  {
    title: 'System',
    href: '/settings/system'
  },
  {
    title: 'Security',
    href: '/settings/security'
  },
  {
    title: 'Logs',
    href: '/settings/logs'
  },
  {
    title: 'About',
    href: '/settings/about'
  },
  {
    title: 'Upgrade',
    href: '/settings/upgrade'
  }
];
const emit = defineEmits(['nav']);

function navTo(href) {
  emit('nav', href);
}
</script>

<template>
  <nav class="flex space-x-2 lg:flex-col lg:space-x-0 lg:space-y-1">
    <Button
      v-for="item in sidebarNavItems"
      :key="item.title"
      @click="navTo(item.href)"
      variant="ghost"
      :class="[settingsActivePane === item.href ? '  bg-rcgray-600' : 'text-rcgray-300']"
      class="inline-flex items-center justify-start w-full px-4 py-2 text-sm font-medium text-left transition-colors rounded-md whitespace-nowrap focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:text-accent-foreground h-9 hover:bg-rcgray-800">
      {{ item.title }}
    </Button>
  </nav>
</template>
