export default [
	{
		path: "/settings/users/:userId?",
		name: "users",
		component: () => import("@/pages/Settings/Users/Main.vue"),
		meta: {
			rbacViewName: "User",
			pageTitleKey: "Users",
			breadcrumb: [
				{ label: "Home", link: "/" },
				{ label: "Users", link: "/users" },
			],
		},
	},
	// {
	// 	path: "/settings/users-activity-log",
	// 	name: "users-activity-log",
	// 	component: () => import("@/pages/Settings/UserActivityLogs/Main.vue"),
	// 	beforeEnter: [guardMyroute, guardAdminRoute],
	// 	meta: {
	// 		rbacViewName: "ActivityLog",
	// 		pageTitleKey: "UsersActivityLog",
	// 		breadcrumb: [
	// 			{ label: "Home", link: "/" },
	// 			{ label: "Users Activity Log", link: "/users-activity-log" },
	// 		],
	// 	},
	// },
	// {
	// 	path: "/settings/my-profile/:userId?",
	// 	name: "user-profile",
	// 	component: () => import("@/pages/Settings/UserProfile/Main.vue"),
	// 	beforeEnter: [],
	// 	meta: {
	// 		pageTitleKey: "UserProfile",
	// 		breadcrumb: [
	// 			{ label: "Home", link: "/" },
	// 			{ label: "User Profile", link: "/user-profile" },
	// 		],
	// 	},
	// },
];
