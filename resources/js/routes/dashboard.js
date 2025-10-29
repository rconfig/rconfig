const dashboardRoutes = [
	{
		path: "/dashboard",
		name: "Dashboard",
		component: () => import("@/pages/Dashboard/Main.vue"),
		meta: {
			rbacViewName: "Setting",
			pageTitleKey: "Dashboard",
		},
	},
	{
		path: "/",
		redirect: "/dashboard",
		meta: {
			rbacViewName: "Setting",
		},
	},
];

export default dashboardRoutes;
