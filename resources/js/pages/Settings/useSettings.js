import axios from 'axios';
import { ref, computed, onMounted } from 'vue';
import { useToaster } from '@/composables/useToaster'; // Import the composable
import SystemSettingsPanel from '@/pages/Settings/Panels/SystemSettingsPanel.vue';
import SecurityPanel from '@/pages/Settings/Panels/SecurityPanel.vue';
import AboutPanel from '@/pages/Settings/Panels/AboutPanel.vue';
import LogsPanel from '@/pages/Settings/Panels/LogsPanel.vue';
import UpgradePanel from '@/pages/Settings/Panels/UpgradePanel.vue';
import { useRoute } from 'vue-router'; // Import the useRoute from Vue Router

export function useSettings() {
  const settingsActivePane = ref(null);
  const route = useRoute();
  const path = ref(route.path);

  const formComponents = {
    '/settings/system': SystemSettingsPanel,
    '/settings/security': SecurityPanel,
    '/settings/about': AboutPanel,
    '/settings/logs': LogsPanel,
    '/settings/upgrade': UpgradePanel
  };

  function setForm(e) {
    settingsActivePane.value = e;

    // Store the selected form in localStorage
    localStorage.setItem('settingsActivePane', e);
  }

  // Define the mapping between `settingsActivePane` value and the component to render.
  const settingsActivePaneComponent = computed(() => {
    return formComponents[settingsActivePane.value] || null;
  });

  onMounted(() => {
    setComponent();
  });

  function setComponent() {
    // if the path is specific, load the component
    if (Object.keys(formComponents).includes(route.path)) {
      settingsActivePane.value = route.path;
      return;
    }

    // if the path is not specific, load the users last active pane
    const LastUserActivePane = localStorage.getItem('settingsActivePane');

    if (LastUserActivePane) {
      settingsActivePane.value = LastUserActivePane;
    } else {
      settingsActivePane.value = '/settings/system';
    }
  }

  return {
    settingsActivePane,
    setForm,
    formComponents,
    settingsActivePaneComponent
  };
}
