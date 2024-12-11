import { createWebHistory, createRouter } from 'vue-router';

const routes = [
  { path: '/', name: 'Home', component: () => import('@/pages/Dashboard/Main.vue') },
  { path: '/dashboard', name: 'dashboard', component: () => import('@/pages/Dashboard/Main.vue') },
  { path: '/inventory/:view?', name: 'inventory', component: () => import('@/pages/Inventory/Main.vue'), props: true },
  { path: '/commandgroups', name: 'commandgroups', component: () => import('@/pages/Inventory/Main.vue') },
  { path: '/devices', name: 'devices', component: () => import('@/pages/Inventory/Main.vue') },
  { path: '/device/view/:id', name: 'devicesview', component: () => import('@/pages/Shared/ContentPanel.vue'), props: true },
  {
    path: '/configs',
    name: 'configs',
    component: () => import('@/pages/Configs/Main.vue'),
    props: true,
    meta: {
      breadcrumb: [
        { label: 'Home', link: '/' },
        { label: 'Configs', link: '/configs' }
      ]
    }
  },
  {
    path: '/config-search',
    name: 'configsearch',
    component: () => import('@/pages/Configs/Main.vue'),
    props: true,
    meta: {
      breadcrumb: [
        { label: 'Home', link: '/' },
        { label: 'Configs', link: '/configs' },
        { label: 'Config Search', link: '/config-search' }
      ]
    }
  },
  {
    path: '/config-compare',
    name: 'configcompare',
    component: () => import('@/pages/Configs/Main.vue'),
    props: true,
    meta: {
      breadcrumb: [
        { label: 'Home', link: '/' },
        { label: 'Configs', link: '/configs' },
        { label: 'Config Compare', link: '/config-compare' }
      ]
    }
  },

  { path: '/configs/view/:id', name: 'configsview', component: () => import('@/pages/Shared/ContentPanel.vue'), props: true },
  { path: '/tags', name: 'tags', component: () => import('@/pages/Inventory/Main.vue') },
  { path: '/vendors', name: 'vendors', component: () => import('@/pages/Inventory/Main.vue') },
  { path: '/commands', name: 'commands', component: () => import('@/pages/Inventory/Main.vue') },
  { path: '/templates', name: 'templates', component: () => import('@/pages/Inventory/Main.vue') },
  { path: '/templates/view/:id', name: 'templatesview', component: () => import('@/pages/Shared/ContentPanel.vue'), props: true },

  {
    path: '/tasks',
    name: 'tasks',
    component: () => import('@/pages/Tasks/Main.vue'),
    meta: {
      breadcrumb: [
        { label: 'Home', link: '/' },
        { label: 'Tasks', link: '/tasks' }
      ]
    }
  },
  {
    path: '/settings',
    name: 'settings',
    component: () => import('@/pages/Settings/Main.vue'),
    beforeEnter: guardMyroute,
    meta: {
      breadcrumb: [
        { label: 'Home', link: '/' },
        { label: 'Settings', link: '/settings' }
      ]
    }
  },
  {
    path: '/settings/about',
    name: 'settings-about',
    component: () => import('@/pages/Settings/Main.vue'),
    props: { about: true },
    beforeEnter: guardMyroute
  },
  {
    path: '/settings/upgrade',
    name: 'settings-upgrade',
    component: () => import('@/pages/Settings/Main.vue'),
    props: { upgrade: true },
    beforeEnter: guardMyroute
  },
  {
    path: '/settings/users/:userId?',
    name: 'users',
    component: () => import('./pages/Users/Main.vue'),
    beforeEnter: guardMyroute,
    meta: {
      breadcrumb: [
        { label: 'Home', link: '/' },
        { label: 'Users', link: '/users' }
      ]
    }
  },

  // working above

  // See chat gpt on how to implement breadcrum with dyanmic routes using meta data
  // { path: '/devices/status/:id', name: 'devices-status', component: () => import('./views/Inventory.vue') },
  // { path: '/devices/tag/:id', name: 'devices-tag', component: () => import('./views/Inventory.vue') },
  // { path: '/devices/category/:id', name: 'devices-category', component: () => import('./views/Inventory.vue') },
  // { path: '/device/view/:id', component: () => import('./views/DevicesViews/DeviceView.vue'), props: true },
  // { path: '/device/view/configs/:id', component: () => import('./views/DevicesViews/DeviceConfigs.vue'), props: true },
  // { path: '/device/view/configs/view-config/:id', component: () => import('./views/DevicesViews/DeviceViewConfig.vue') },
  // { path: '/device/view/eventlog/:id', component: () => import('./views/DevicesViews/DeviceEventLog.vue'), props: true },

  // { path: '/scheduled-tasks', name: 'scheduled-tasks', component: () => import('./views/Tasks.vue') },

  // { path: '/tags/:id', name: 'tags-id', component: () => import('./views/Inventory.vue') },
  // { path: '/config-search', name: 'config-search', component: () => import('./views/ConfigSearch.vue') },
  // { path: '/config-reports', name: 'config-reports', component: () => import('./views/ConfigReports.vue') },

  // Settings

  // { path: '/settings/activitylog', name: 'activitylog', component: () => import('./views/ActivityLog.vue'), beforeEnter: guardMyroute },

  /* PAGENOTFOUND */
  { path: '/:catchAll(.*)', component: () => import('./pages/Shared/PageNotFound.vue') }
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
