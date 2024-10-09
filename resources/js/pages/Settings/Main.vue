<script setup>
import { ref, computed } from 'vue';
import { useSettings } from '@/pages/Settings/useSettings';
import SidebarNav from '@/pages/Settings/SidebarNav.vue';
import { Separator } from '@/components/ui/separator';
import SystemSettingsForm from '@/pages/Settings/Forms/SystemSettingsForm.vue';
import SecurityForm from '@/pages/Settings/Forms/SecurityForm.vue';
import AboutForm from '@/pages/Settings/Forms/AboutForm.vue';
import LogsForm from '@/pages/Settings/Forms/LogsForm.vue';
import UpgradeForm from '@/pages/Settings/Forms/UpgradeForm.vue';

const {} = useSettings();
const activeForm = ref('/settings/system');

function setForm(e) {
  activeForm.value = e;
}

const formComponents = {
  '/settings/system': SystemSettingsForm,
  '/settings/security': SecurityForm,
  '/settings/about': AboutForm,
  '/settings/logs': LogsForm,
  '/settings/upgrade': UpgradeForm
};

// Define the mapping between `activeForm` value and the component to render.
const activeFormComponent = computed(() => {
  return formComponents[activeForm.value] || null;
});
</script>

<template>
  <div class="p-10 pb-16 space-y-6 md:block">
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
      <aside class="-mx-4 lg:w-1/5">
        <SidebarNav @nav="setForm" />
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
