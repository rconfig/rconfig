/**
 * Generate page title from route meta
 * @param {Object} route - Vue Router route object
 * @returns {string} - Page title
 */
export function generatePageTitle(route) {
	const apiName = import.meta.env.VITE_APP_NAME;
	const defaultTitle = apiName || "rConfigV8 Core - NCM";
	
	if (route.meta?.pageTitleKey) {
		const titles = {
			dashboard: "Dashboard",
			inventory: "Inventory",
			devices: "Devices",
			deviceView: "Device View",
			commandGroups: "Command Groups",
			tags: "Tags",
			vendors: "Vendors",
			commands: "Commands",
			templates: "Templates",
			templateView: "Template View",
			configurations: "Configurations",
			configSearch: "Config Search",
			configView: "Config View",
			scheduledTasks: "Scheduled Tasks",
			settings: "Settings",
			systemLogs: "System Logs",
			about: "About",
			upgrade: "Upgrade",
			users: "Users",
			Login: "Login",
			LoggedOut: "Logged Out",
			PasswordReset: "Password Reset",
			AccessDenied: "Access Denied",
			pageNotFound: "Page Not Found",
		};

		const translatedTitle = titles[route.meta.pageTitleKey];
			
		if (translatedTitle) {
			return `${translatedTitle} | ${defaultTitle}`;
		}
	
	}

	if (route.name) {
		const readable = route.name
			.replace(/([A-Z])/g, " $1")
			.trim()
			.replace(/^\w/, (c) => c.toUpperCase());
		return `${readable} | ${defaultTitle}`;
	}

	return defaultTitle;
}