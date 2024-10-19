import axios from 'axios';
import { ref, computed, onMounted } from 'vue';
import { useToaster } from '@/composables/useToaster'; // Import the composable
import SystemSettingsForm from '@/pages/Settings/Forms/SystemSettingsForm.vue';
import SecurityForm from '@/pages/Settings/Forms/SecurityForm.vue';
import AboutForm from '@/pages/Settings/Forms/AboutForm.vue';
import LogsForm from '@/pages/Settings/Forms/LogsForm.vue';
import UpgradeForm from '@/pages/Settings/Forms/UpgradeForm.vue';

export function useSettings() {
  const activeForm = ref(null);

  const formComponents = {
    '/settings/system': SystemSettingsForm,
    '/settings/security': SecurityForm,
    '/settings/about': AboutForm,
    '/settings/logs': LogsForm,
    '/settings/upgrade': UpgradeForm
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
