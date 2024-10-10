// stores/externalLinksStore.js
import { defineStore } from 'pinia';

export const useExternalLinksStore = defineStore('externalLinks', {
  state: () => ({
    links: []
  }),
  actions: {
    setLinks(links) {
      this.links = links;
    }
  }
});
