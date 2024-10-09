<script setup>
import { Button } from '@/components/ui/button';
import { useRoute } from 'vue-router';

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
    title: 'About',
    href: '/settings/about'
  },
  {
    title: 'Logs',
    href: '/settings/logs'
  },
  {
    title: 'Upgrade',
    href: '/settings/upgrade'
  }
];
const emit = defineEmits(['nav']);

const route = useRoute();
const pagename = route.path;

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
      :class="[pagename === item.href ? '  bg-rcgray-600' : 'text-rcgray-300']"
      class="inline-flex items-center justify-start w-full px-4 py-2 text-sm font-medium text-left transition-colors rounded-md whitespace-nowrap focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:text-accent-foreground h-9 hover:bg-rcgray-800">
      {{ item.title }}
    </Button>
  </nav>
</template>
