import '../css/global.css';
import './appimports/bootstrap';
import 'highlight.js/styles/base16/tomorrow-night.css';
import * as Icons from './appimports/iconImports';
import * as UIComponents from './appimports/uiComponentImports';
import * as AuthComponents from './appimports/authComponentImports';
import { NavigationSide, NavigationTop } from './appimports/navComponentImports';
import { applyStoredTheme } from './appimports/themeHandler';
import { createApp } from 'vue/dist/vue.esm-bundler.js';
import { registerComponents } from './appimports/registerComponents';
import { setupGlobalProperties } from './appimports/globalProperties';
import { setupPlugins } from './appimports/setupPlugins';
import { useFormatters } from './composables/useFormatters'; // Import the composable
import { handleLoginLoadingState, handleMainContentLoadingState } from './appimports/loadingHandlers'; // Import the loading handlers

const app = createApp({});

registerComponents(app, AuthComponents);
registerComponents(app, UIComponents);
registerComponents(app, Icons);
app.component('navigation-side', NavigationSide);
app.component('navigation-top', NavigationTop);

// Apply plugins
setupPlugins(app);

// Apply theme settings
applyStoredTheme();

// Setup global properties
setupGlobalProperties(app);

// Use the formatters composable and make it globally accessible
const formatters = useFormatters();
app.config.globalProperties.$formatters = formatters;

app.provide('formatters', formatters);
app.provide('useremail', app.config.globalProperties.$userEmail);
app.provide('userid', app.config.globalProperties.$userId);
app.provide('timezone', app.config.globalProperties.$timezone);

const vm = app.mount('#app');

// Attach loading handlers
document.addEventListener('DOMContentLoaded', () => {
  handleLoginLoadingState(); // Existing login spinner logic
  handleMainContentLoadingState(); // New main content spinner logic
});
