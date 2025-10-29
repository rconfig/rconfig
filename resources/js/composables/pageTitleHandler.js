/**
 * Generate page title from route meta
 * @param {Object} route - Vue Router route object
 * @returns {string} - Page title
 */
export function generatePageTitle(route) {
	const pageTitleKey = route.meta?.pageTitleKey;
	
	// Simple title mapping without i18n
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
		configCompare: "Config Compare",
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

	const title = titles[pageTitleKey] || "rConfig";
	return `${title} | rConfig`;
}