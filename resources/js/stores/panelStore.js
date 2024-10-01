import { defineStore } from 'pinia';
import { ref } from 'vue';

export const usePanelStore = defineStore('panel', () => {
  const panelRef = ref(null);

  return {
    panelRef
  };
});
