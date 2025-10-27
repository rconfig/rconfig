<script setup>
import { useSettings } from '@/pages/Settings/useSettings';
import SidebarNav from '@/pages/Settings/SidebarNav.vue';
import { Separator } from '@/components/ui/separator';
import Loading from '@/pages/Shared/Loaders/Loading.vue';

const { settingsActivePane, setForm, formComponents, settingsActivePaneComponent } = useSettings();
</script>

<template>
  <div class="pb-16 pl-10 space-y-6 md:block">
    <div class="space-y-0.5">
      <h2 class="flex items-center space-x-2 text-2xl font-bold tracking-tight">
        <SettingsIcon class="w-8 h-8" />
        <span>Settings</span>
      </h2>
      <p class="text-muted-foreground">Manage your application settings and preferences</p>
    </div>
    <Separator class="my-6" />
    <div class="flex flex-col space-y-8 lg:flex-row lg:space-x-12 lg:space-y-0">
      <aside class="lg:w-[10%]">
        <SidebarNav
          v-if="settingsActivePane"
          @nav="setForm($event)"
          :settingsActivePane="settingsActivePane" />
      </aside>

      <div class="flex-1">
        <div class="flex items-center justify-center space-y-6">
          <Loading v-if="!settingsActivePane" />

          <transition
            name="fade"
            mode="out-in">
            <component
              v-if="settingsActivePane"
              :is="settingsActivePaneComponent"
              :key="settingsActivePane" />
          </transition>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter,
.fade-leave-to {
  opacity: 0;
}
</style>
