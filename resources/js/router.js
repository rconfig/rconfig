import configsRoutes from "@/routes/configs";
import dashboardRoutes from "@/routes/dashboard";
import integrationsRoutes from "@/routes/integrations";
import inventoryRoutes from "@/routes/inventory";
import settingsRoutes from "@/routes/settings";
import tasksRoutes from "@/routes/tasks";
import usersRoutes from "@/routes/users";
import { createWebHistory, createRouter } from "vue-router";
import { generatePageTitle } from "@/composables/pageTitleHandler";

const routes = [
	...configsRoutes,
	...dashboardRoutes,
	...integrationsRoutes,
	...inventoryRoutes,
	...settingsRoutes,
	...tasksRoutes,
	...usersRoutes,

	// Auth routes
	{
		path: "/login",
		name: "login",
		meta: { pageTitleKey: "Login" },
	},
	{
		path: "/logged-out",
		name: "logged-out",
		meta: { pageTitleKey: "LoggedOut" },
	},
	{
		path: "/password/reset",
		name: "password-reset",
		meta: { pageTitleKey: "PasswordReset" },
	},

	/* PAGENOTFOUND - keep at the end */
	{
		path: "/:catchAll(.*)",
		name: "PageNotFound",
		component: () => import("@/pages/Shared/PageNotFound.vue"),
		meta: { pageTitleKey: "pageNotFound" },
	},
];

// Create router
const router = createRouter({
	history: createWebHistory(),
	routes,
	linkActiveClass: "linkActiveClass-active-link",
	linkExactActiveClass: "linkExactActiveClass-exact-active pf-m-current",
});

// Navigation guard to set page title
router.beforeEach((to, from, next) => {
	document.title = generatePageTitle(to);
	next();
});

// Set default title on router ready
router.isReady().then(() => {
	if (!document.title || document.title === "") {
		document.title = "rConfig";
	}
});

export default router;