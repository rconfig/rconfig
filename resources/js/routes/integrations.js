export default [
	{
		path: "/settings/integrations",
		name: "integrations",
		component: () => import("@/pages/Settings/Integrations/Main.vue"),
		props: true,
		meta: {
			rbacViewName: "IntegrationOption",
			pageTitleKey: "Integrations",
			breadcrumb: [
				{ label: "Home", link: "/" },
				{ label: "Integrations", link: "/settings/integrations" },
			],
		},
	},
];
