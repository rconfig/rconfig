import { createWebHistory, createRouter } from 'vue-router';

const routes = [
  { path: '/', name: 'Home', component: () => import('./views/Dashboard.vue') },
  { path: '/dashboard', name: 'dashboard', component: () => import('./views/Dashboard.vue') },
  { path: '/inventory', name: 'inventory', component: () => import('./views/Inventory.vue') },
  { path: '/devices', name: 'devices', component: () => import('./views/Inventory/Devices.vue') },
  { path: '/devices/status/:id', name: 'devices-status', component: () => import('./views/Inventory.vue') },
  { path: '/devices/tag/:id', name: 'devices-tag', component: () => import('./views/Inventory.vue') },
  { path: '/devices/category/:id', name: 'devices-category', component: () => import('./views/Inventory.vue') },
  { path: '/device/view/:id', component: () => import('./views/DevicesViews/DeviceView.vue'), props: true },
  { path: '/device/view/configs/:id', component: () => import('./views/DevicesViews/DeviceConfigs.vue'), props: true },
  { path: '/device/view/configs/view-config/:id', component: () => import('./views/DevicesViews/DeviceViewConfig.vue') },
  { path: '/device/view/eventlog/:id', component: () => import('./views/DevicesViews/DeviceEventLog.vue'), props: true },

  { path: '/scheduled-tasks', name: 'scheduled-tasks', component: () => import('./views/Tasks.vue') },
  { path: '/templates', name: 'templates', component: () => import('./views/Templates.vue') },
  { path: '/commandgroups', name: 'commandgroups', component: () => import('./views/Inventory/CommandGroups.vue') },
  { path: '/commands', name: 'commands', component: () => import('./views/Commands.vue') },
  { path: '/tags', name: 'tags', component: () => import('./views/Inventory/Tags.vue') },
  { path: '/tags/:id', name: 'tags-id', component: () => import('./views/Tags.vue') },
  { path: '/vendors', name: 'vendors', component: () => import('./views/Vendors.vue') },
  { path: '/config-search', name: 'config-search', component: () => import('./views/ConfigSearch.vue') },
  { path: '/config-reports', name: 'config-reports', component: () => import('./views/ConfigReports.vue') },

  // Settings
  {
    path: '/settings/',
    name: 'settings',
    component: () => import('./views/Settings.vue'),
    beforeEnter: guardMyroute,
    children: [
      { path: '/settings/overview', name: 'overview', component: () => import('./views/SettingsTabs/0Overview.vue') },
      { path: '/settings/system', name: 'system', component: () => import('./views/SettingsTabs/1System.vue') },
      { path: '/settings/security', name: 'security', component: () => import('./views/SettingsTabs/4Security.vue') },
      { path: '/settings/about', name: 'about', component: () => import('./views/SettingsTabs/10About.vue') }
    ]
  },

  { path: '/settings/users/:userId?', name: 'users', component: () => import('./views/Users.vue'), beforeEnter: guardMyroute },
  { path: '/settings/activitylog', name: 'activitylog', component: () => import('./views/ActivityLog.vue'), beforeEnter: guardMyroute },

  /* PAGENOTFOUND */
  { path: '/:catchAll(.*)', component: () => import('./views/PageNotFound.vue') }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  linkActiveClass: 'linkActiveClass-active-link',
  linkExactActiveClass: 'linkExactActiveClass-exact-active pf-m-current'
});

export default router;

//https://medium.com/js-dojo/how-to-implement-route-guard-in-vue-js-9929c93a13db
function guardMyroute(to, from, next) {
  let userRole = document.querySelector("meta[name='user-role']").getAttribute('content');
  var isAuthenticated = false;
  if (userRole === 'Admin') isAuthenticated = true;
  else isAuthenticated = false;
  if (isAuthenticated) {
    next(); // allow to enter route
  } else {
    next('/dashboard'); // go to '/login';
  }
}
