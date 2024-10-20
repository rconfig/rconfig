import VueHighlightJS from 'vue3-highlightjs';
import { createPinia } from 'pinia';
import router from '@/router';

export function setupPlugins(app) {
  app.use(router);
  app.use(VueHighlightJS);
  const pinia = createPinia();
  app.use(pinia);
}
