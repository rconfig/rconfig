import 'highlight.js/styles/base16/tomorrow-night.css';
import '../css/global.css';
import '../css/login_background.css';

import { createApp } from 'vue/dist/vue.esm-bundler.js';
import router from './router';

import { ResizableHandle, ResizablePanel, ResizablePanelGroup } from './components/ui/resizable';
import NavigationTop from './layouts/NavigationTop.vue';
import Navigationside from './layouts/NavigationSide.vue';
import VueHighlightJS from 'vue3-highlightjs';
import { createPinia } from 'pinia';

// Import all icons from iconImports.js
import * as Icons from './iconImports';

// Import all UI components from uiComponents.js
import * as UIComponents from './uiComponentImports';

const app = createApp({
  data: () => ({
    count: 0,
    notifications: {}
  }),
  methods: {}
});

const pinia = createPinia();

app.config.globalProperties.$userId = document.querySelector("meta[name='user-id']").getAttribute('content');
app.config.globalProperties.$userName = document.querySelector("meta[name='user-name']").getAttribute('content');
app.config.globalProperties.$userEmail = document.querySelector("meta[name='user-email']").getAttribute('content');
app.config.globalProperties.$userRole = document.querySelector("meta[name='user-role']").getAttribute('content');

app.component('navigation-side', Navigationside);
app.component('navigation-top', NavigationTop);
app.component('resizable-panel', ResizablePanel);
app.component('resizable-panel-group', ResizablePanelGroup);
app.component('resizable-handle', ResizableHandle);

// Register ShadCN UI components
Object.keys(UIComponents).forEach(componentName => {
  app.component(componentName, UIComponents[componentName]);
});

// Register all icons using the imported icons object
Object.keys(Icons).forEach(iconName => {
  app.component(iconName, Icons[iconName]);
});

app.provide('useremail', app.config.globalProperties.$userEmail);
app.provide('userid', app.config.globalProperties.$userId);
app.provide('timezone', app.config.globalProperties.$timezone);

app.use(router);
app.use(VueHighlightJS);
app.use(pinia);

const vm = app.mount('#app');
import './bootstrap';
import { usePanelStore } from '@/stores/panelStore';
const panelStore = usePanelStore();

var storedTheme = localStorage.getItem('theme');
if (storedTheme) {
  document.documentElement.setAttribute('data-theme', storedTheme);
}
