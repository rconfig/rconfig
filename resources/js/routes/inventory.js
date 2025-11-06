const inventoryRoutes = [
	{
		path: "/inventory/:view?",
		name: "inventory",
		component: () => import("@/pages/Inventory/Main.vue"),
		props: true,
		meta: {
			pageTitleKey: "Inventory",
		},
	},
	{
		path: "/commandgroups/:id?",
		name: "commandgroups",
		component: () => import("@/pages/Inventory/Main.vue"),
		meta: {
			rbacViewName: "Command",
			pageTitleKey: "CommandGroups",
		},
	},
	{
		path: "/devices",
		name: "devices",
		component: () => import("@/pages/Inventory/Main.vue"),
		meta: {
			rbacViewName: "Device",
			pageTitleKey: "Devices",
		},
	},
	{
		path: "/device/view/:id",
		name: "device-view",
		component: () => import("@/pages/Shared/Panels/ContentPanel.vue"),
		props: true,
		meta: {
			rbacViewName: "Device",
			pageTitleKey: "DeviceDetails",
		},
	},
	{
		path: "/tags/:id?",
		name: "tags",
		component: () => import("@/pages/Inventory/Main.vue"),
		meta: {
			rbacViewName: "Tag",
			pageTitleKey: "Tags",
		},
	},
	{
		path: "/models/:id?",
		name: "models",
		component: () => import("@/pages/Inventory/Main.vue"),
		meta: {
			rbacViewName: "Tag",
			pageTitleKey: "Models",
		},
	},
	{
		path: "/vendors/:id?",
		name: "vendors",
		component: () => import("@/pages/Inventory/Main.vue"),
		meta: {
			rbacViewName: "Vendor",
			pageTitleKey: "Vendors",
		},
	},
	{
		path: "/commands",
		name: "commands",
		component: () => import("@/pages/Inventory/Main.vue"),
		meta: {
			rbacViewName: "Command",
			pageTitleKey: "Commands",
		},
	},
	{
		path: "/command-compare-options",
		name: "command-compare-options",
		component: () => import("@/pages/Inventory/Main.vue"),
		meta: {
			rbacViewName: "Command",
			pageTitleKey: "CommandOptions",
		},
	},
	{
		path: "/templates/:id?",
		name: "templates",
		component: () => import("@/pages/Inventory/Main.vue"),
		meta: {
			rbacViewName: "Template",
			pageTitleKey: "Templates",
		},
	},
	{
		path: "/templates/view/:id",
		name: "template-view",
		component: () => import("@/pages/Shared/Panels/ContentPanel.vue"),
		props: true,
		meta: {
			rbacViewName: "Template",
			pageTitleKey: "TemplateDetails",
		},
	},
];

export default inventoryRoutes;
