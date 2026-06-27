const restapiDocsRoutes = [
  {
    path: "",
    redirect: "/settings/restapi-docs/get-started",
  },
  {
    path: "get-started",
    name: "rest-api-docs-get-started",
    component: () =>
      import("@/pages/Settings/Panels/Components/RestApiDocs/0GetStarted.vue"),
    meta: { pageTitleKey: "RestApiDocsGetStarted", rbacViewName: "Setting" },
  },
  {
    path: "authentication",
    name: "rest-api-docs-authentication",
    component: () =>
      import("@/pages/Settings/Panels/Components/RestApiDocs/1Authentication.vue"),
    meta: {
      pageTitleKey: "RestApiDocsAuthentication",
      rbacViewName: "Setting",
    },
  },
  {
    path: "testing-the-api",
    name: "rest-api-docs-testing-the-api",
    component: () =>
      import("@/pages/Settings/Panels/Components/RestApiDocs/2TestingTheApi.vue"),
    meta: { pageTitleKey: "RestApiDocsTestingTheApi", rbacViewName: "Setting" },
  },
  {
    path: "dashboard/health-latest-v2",
    name: "rest-api-docs-dashboard-health-latest-v2",
    component: () =>
      import("@/pages/Settings/Panels/Components/RestApiDocs/22DashboardHealthLatestV2.vue"),
    meta: {
      pageTitleKey: "RestApiDocsDashboardHealthLatestV2",
      rbacViewName: "Setting",
    },
  },
  {
    path: "devices",
    name: "rest-api-docs-devices",
    component: () =>
      import("@/pages/Settings/Panels/Components/RestApiDocs/31Devices.vue"),
    meta: { pageTitleKey: "RestApiDocsDevices", rbacViewName: "Setting" },
  },
  {
    path: "devices/devices-v2",
    name: "rest-api-docs-devices-v2",
    component: () =>
      import("@/pages/Settings/Panels/Components/RestApiDocs/312Devices.vue"),
    meta: { pageTitleKey: "RestApiDocsDevicesV2", rbacViewName: "Setting" },
  },
  {
    path: "devices/device-creds",
    name: "rest-api-docs-device-creds",
    component: () =>
      import("@/pages/Settings/Panels/Components/RestApiDocs/311DevicesCreds.vue"),
    meta: { pageTitleKey: "RestApiDocsDeviceCreds", rbacViewName: "Setting" },
  },
  {
    path: "devices/templates",
    name: "rest-api-docs-templates",
    component: () =>
      import("@/pages/Settings/Panels/Components/RestApiDocs/32Templates.vue"),
    meta: { pageTitleKey: "RestApiDocsTemplates", rbacViewName: "Setting" },
  },
  {
    path: "devices/categories",
    name: "rest-api-docs-categories",
    component: () =>
      import("@/pages/Settings/Panels/Components/RestApiDocs/33Categories.vue"),
    meta: { pageTitleKey: "RestApiDocsCategories", rbacViewName: "Setting" },
  },
  {
    path: "devices/commands",
    name: "rest-api-docs-commands",
    component: () =>
      import("@/pages/Settings/Panels/Components/RestApiDocs/34Commands.vue"),
    meta: { pageTitleKey: "RestApiDocsCommands", rbacViewName: "Setting" },
  },
  {
    path: "devices/vendors",
    name: "rest-api-docs-vendors",
    component: () =>
      import("@/pages/Settings/Panels/Components/RestApiDocs/35Vendors.vue"),
    meta: { pageTitleKey: "RestApiDocsVendors", rbacViewName: "Setting" },
  },
  {
    path: "devices/tags",
    name: "rest-api-docs-tags",
    component: () =>
      import("@/pages/Settings/Panels/Components/RestApiDocs/36Tags.vue"),
    meta: { pageTitleKey: "RestApiDocsTags", rbacViewName: "Setting" },
  },
  {
    path: "configs",
    name: "rest-api-docs-configs",
    component: () =>
      import("@/pages/Settings/Panels/Components/RestApiDocs/41Configs.vue"),
    meta: { pageTitleKey: "RestApiDocsConfigs", rbacViewName: "Setting" },
  },
  {
    path: "configs-v2",
    name: "rest-api-docs-configs-v2",
    component: () =>
      import("@/pages/Settings/Panels/Components/RestApiDocs/412Configs.vue"),
    meta: { pageTitleKey: "RestApiDocsConfigsV2", rbacViewName: "Setting" },
  },
  {
    path: "configs/config-changes-v2",
    name: "rest-api-docs-config-changes-v2",
    component: () =>
      import("@/pages/Settings/Panels/Components/RestApiDocs/24ConfigChangesV2.vue"),
    meta: {
      pageTitleKey: "RestApiDocsConfigChangesV2",
      rbacViewName: "Setting",
    },
  },
  {
    path: "download-now",
    name: "rest-api-docs-download-now",
    component: () =>
      import("@/pages/Settings/Panels/Components/RestApiDocs/42DownloadNow.vue"),
    meta: { pageTitleKey: "RestApiDocsDownloadNow", rbacViewName: "Setting" },
  },
  {
    path: "tasks",
    name: "rest-api-docs-tasks",
    component: () =>
      import("@/pages/Settings/Panels/Components/RestApiDocs/51Tasks.vue"),
    meta: { pageTitleKey: "RestApiDocsTasks", rbacViewName: "Setting" },
  },
  {
    path: "users",
    name: "rest-api-docs-users",
    component: () =>
      import("@/pages/Settings/Panels/Components/RestApiDocs/61Users.vue"),
    meta: { pageTitleKey: "RestApiDocsUsers", rbacViewName: "Setting" },
  },
];

export default restapiDocsRoutes;
