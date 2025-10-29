export default [
	{
		path: "/tasks",
		name: "tasks",
		component: () => import("@/pages/Tasks/Main.vue"),
		meta: {
			rbacViewName: "Task",
			pageTitleKey: "Tasks",
			breadcrumb: [
				{ label: "Home", link: "/" },
				{ label: "Tasks", link: "/tasks" },
			],
		},
	},
];
