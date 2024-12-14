import VueHighlightJS from 'vue3-highlightjs';
import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';
import router from '@/router';

export function setupPlugins(app) {
  app.use(router);
  app.use(VueHighlightJS);
  const pinia = createPinia();
  pinia.use(piniaPluginPersistedstate);
  app.use(pinia);
}
