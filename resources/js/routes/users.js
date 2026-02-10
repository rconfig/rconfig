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
	{
		path: "/settings/my-profile/:userId?",
		name: "user-profile",
		component: () => import("@/pages/Settings/UserProfile/Main.vue"),
		beforeEnter: [],
		meta: {
			pageTitleKey: "UserProfile",
			breadcrumb: [
				{ label: "Home", link: "/" },
				{ label: "User Profile", link: "/user-profile" },
			],
		},
	},
];
