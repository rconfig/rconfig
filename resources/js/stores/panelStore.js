import { defineStore } from 'pinia';
import { ref } from 'vue';

export const usePanelStore = defineStore('panel', () => {
  const panelRef = ref(null);
  // used for the side nav collapsable panel
  return {
    panelRef
  };
});
