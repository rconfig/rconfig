<script setup>
import { onMounted } from 'vue';
import { useSettings } from '@/pages/Settings/useSettings';
import SidebarNav from '@/pages/Settings/SidebarNav.vue';
import { Separator } from '@/components/ui/separator';

const { activeForm, setForm, formComponents, activeFormComponent } = useSettings();

onMounted(() => {
  // Retrieve the selected form from localStorage
  const form = localStorage.getItem('activeForm');
  if (form) {
    activeForm.value = form;
  }
});
</script>

<template>
  <div class="pb-16 pl-10 space-y-6 md:block">
    <div class="space-y-0.5">
      <h2 class="flex items-center space-x-2 text-2xl font-bold tracking-tight">
        <Icon
          icon="catppuccin:env"
          class="w-6 h-6" />
        <span>Settings</span>
      </h2>
      <p class="text-muted-foreground">Manage your account settings and set e-mail preferences.</p>
    </div>
    <Separator class="my-6" />
    <div class="flex flex-col space-y-8 lg:flex-row lg:space-x-12 lg:space-y-0">
      <aside class="-mx-4 lg:w-[10%]">
        <SidebarNav
          @nav="setForm($event)"
          :activeForm="activeForm" />
      </aside>
      <div class="flex-1 lg:max-w-2xl">
        <div class="space-y-6">
          <transition
            name="fade"
            mode="out-in">
            <component
              :is="activeFormComponent"
              :key="activeForm" />
          </transition>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter,
.fade-leave-to {
  opacity: 0;
}
</style>
