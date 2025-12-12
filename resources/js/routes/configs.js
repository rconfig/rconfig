export default [
	{
		path: "/configs",
		name: "configs",
		component: () => import("@/pages/Configs/Main.vue"),
		props: true,
		meta: {
			rbacViewName: "Config",
			pageTitleKey: "Configs",
			breadcrumb: [
				{ label: "Home", link: "/" },
				{ label: "Configs", link: "/configs" },
			],
		},
	},
	{
		path: "/configs/view/:id",
		name: "config-view",
		component: () => import("@/pages/Shared/Panels/ContentPanel.vue"),
		props: true,
		meta: {
			rbacViewName: "Config",
			pageTitleKey: "ConfigDetails",
		},
	},
	{
		path: "/config-search",
		name: "configsearch",
		component: () => import("@/pages/Configs/Main.vue"),
		props: true,
		meta: {
			rbacViewName: "Config",
			pageTitleKey: "ConfigSearch",
			breadcrumb: [
				{ label: "Home", link: "/" },
				{ label: "Configs", link: "/configs" },
				{ label: "Config Search", link: "/config-search" },
			],
		},
	},
	{
		path: "/config-report",
		name: "configreport",
		component: () => import("@/pages/Configs/Main.vue"),
		props: true,
		meta: {
			rbacViewName: "Config",
			pageTitleKey: "ConfigReport",
			breadcrumb: [
				{ label: "Home", link: "/" },
				{ label: "Configs", link: "/configs" },
				{ label: "Config Report", link: "/config-report" },
			],
		},
	},
];
