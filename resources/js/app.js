// import '@patternfly/patternfly/patternfly.min.css';
// import '@patternfly/patternfly/patternfly-addons.css';
import 'highlight.js/styles/base16/tomorrow-night.css';
// import '../css/app.css';
import '../css/global.css';
import '../css/login_background.css';

import { createApp } from 'vue/dist/vue.esm-bundler.js';
import router from './router';

import { ResizableHandle, ResizablePanel, ResizablePanelGroup } from './components/ui/resizable';
import NavigationTop from './components/NavigationTop.vue';
import Navigationside from './components/NavigationSide.vue';
import NotificationsDrawer from './components/NotificationsDrawer.vue';
import ToastNotification from './components/ToastNotification.vue';
import VueHighlightJS from 'vue3-highlightjs';
import useNotifications from './composables/notifications';
import { useNavState } from './composables/navstate';
import useServerTimeZone from './composables/ServerTimezone.js';

const app = createApp({
  data: () => ({
    count: 0,
    // test: 'testing text',
    notifications: {}
  }),
  methods: {
    // changeSideNavState() {
    //   // console.log(this.count++);
    // }
  }
});

const { notifications, createNotification, removeNotifications } = useNotifications(); // see example here https://github.com/zafaralam/vue-3-toast/
const { darkmode } = useNavState();
const { formatTime } = useServerTimeZone(app.config.globalProperties.$timezone);
const panelRef = app.config.globalProperties.$refs;

app.config.globalProperties.$userId = document.querySelector("meta[name='user-id']").getAttribute('content');
app.config.globalProperties.$userName = document.querySelector("meta[name='user-name']").getAttribute('content');
app.config.globalProperties.$userEmail = document.querySelector("meta[name='user-email']").getAttribute('content');
app.config.globalProperties.$userRole = document.querySelector("meta[name='user-role']").getAttribute('content');

// app.component('hello-world', HelloWorld);
app.component('navigation-side', Navigationside);
app.component('navigation-top', NavigationTop);
app.component('resizable-panel', ResizablePanel);
app.component('resizable-panel-group', ResizablePanelGroup);
app.component('resizable-handle', ResizableHandle);
app.component('notification-drawer', NotificationsDrawer);
app.component('toast-notification', ToastNotification);

app.provide('create-notification', createNotification);
app.provide('darkmode', darkmode);
app.provide('useremail', app.config.globalProperties.$userEmail);
app.provide('userid', app.config.globalProperties.$userId);
app.provide('timezone', app.config.globalProperties.$timezone);
app.provide('formatTime', formatTime);

// mount the app to the DOM
app.use(router);
app.use(VueHighlightJS);
const vm = app.mount('#app');
vm.notifications = notifications;
vm.removeNotifications = removeNotifications;
import './bootstrap';

var storedTheme = localStorage.getItem('theme');
if (storedTheme) {
  // console.log('storedThemeapp.sj', storedTheme);
  document.documentElement.setAttribute('data-theme', storedTheme);
}
