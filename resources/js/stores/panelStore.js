import { defineStore } from 'pinia';
import { ref } from 'vue';

export const usePanelStore = defineStore('panel', () => {
  const panelRef = ref(null);
  const panelRef2 = ref(null);
  // used for the side nav collapsable panel
  return {
    panelRef,
    panelRef2
  };
});
