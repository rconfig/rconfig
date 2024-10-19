import axios from 'axios';
import { ref, computed, onMounted } from 'vue';
import { useToaster } from '@/composables/useToaster'; // Import the composable
import SystemSettingsPanel from '@/pages/Settings/Panels/SystemSettingsPanel.vue';
import SecurityPanel from '@/pages/Settings/Panels/SecurityPanel.vue';
import AboutPanel from '@/pages/Settings/Panels/AboutPanel.vue';
import LogsPanel from '@/pages/Settings/Panels/LogsPanel.vue';
import UpgradePanel from '@/pages/Settings/Panels/UpgradePanel.vue';

export function useSettings() {
  const activeForm = ref(null);

  const formComponents = {
    '/settings/system': SystemSettingsPanel,
    '/settings/security': SecurityPanel,
    '/settings/about': AboutPanel,
    '/settings/logs': LogsPanel,
    '/settings/upgrade': UpgradePanel
  };

  function setForm(e) {
    activeForm.value = e;

    // Store the selected form in localStorage
    localStorage.setItem('activeForm', e);
  }

  // Define the mapping between `activeForm` value and the component to render.
  const activeFormComponent = computed(() => {
    return formComponents[activeForm.value] || null;
  });

  onMounted(() => {
    // Retrieve the selected form from localStorage
    const form = localStorage.getItem('activeForm');
    if (form) {
      activeForm.value = form;
    } else {
      activeForm.value = '/settings/system';
    }
  });

  return {
    activeForm,
    setForm,
    formComponents,
    activeFormComponent
  };
}
