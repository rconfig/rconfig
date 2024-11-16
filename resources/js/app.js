import '../css/global.css';
import '../css/login_background.css';
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

function handleLoadingState() {
  const state = {
    isLoading: true
  };

  if (state.isLoading) {
    const loadingTime = Math.floor(Math.random() * (1500 - 300 + 1)) + 300; // Random time between 300ms and 1500ms
    setTimeout(function () {
      document.getElementById('auth-loading-container').classList.add('hidden');
      document.getElementById('auth-main-content').classList.remove('hidden');
      state.isLoading = false;
    }, loadingTime);
  }
}

document.addEventListener('DOMContentLoaded', handleLoadingState);
