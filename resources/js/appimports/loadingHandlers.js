/**
 * Simplified loading handlers - first visit shows AuthLoading, subsequent visits skip it
 */

const AUTH_LOADER_LS_KEY = "rconfig.authLoaderSeen.v1";

function hasSeenAuthLoader() {
	try {
		return localStorage.getItem(AUTH_LOADER_LS_KEY) === "1";
	} catch {
		return false;
	}
}

function markAuthLoaderSeen() {
	try {
		localStorage.setItem(AUTH_LOADER_LS_KEY, "1");
	} catch {
		// Ignore localStorage errors
	}
}

/**
 * Initialize loading handlers when DOM is ready
 */
export function initializeLoadingHandlers() {
	// Check for logout page first
	const logoutLoadingContainer = document.getElementById("logout-loading-container");
	const logoutMainContent = document.getElementById("logout-main-content");

	if (logoutLoadingContainer && logoutMainContent) {
		// Logout page - always show loading briefly
		// console.log("Logout page - showing logout loading");
		setTimeout(() => {
			logoutLoadingContainer.classList.add("hidden");
			logoutMainContent.classList.remove("hidden");
			window.dispatchEvent(new CustomEvent("auth-loading-complete"));
		}, 2000); // Show logout loading for 2 seconds
		return; // Exit early for logout pages
	}

	// Check for login page
	const authLoadingContainer = document.getElementById("auth-loading-container");
	const authMainContent = document.getElementById("auth-main-content");

	if (authLoadingContainer && authMainContent) {
		// Login page - check first visit logic
		if (hasSeenAuthLoader()) {
			// Not first visit - skip loading, show login immediately
			// console.log("Subsequent visit - showing login immediately");
			authLoadingContainer.classList.add("hidden");
			authMainContent.classList.remove("hidden");
		} else {
			// First visit - show loading, then hide after animation
			// console.log("First visit - showing branded loading");
			setTimeout(() => {
				authLoadingContainer.classList.add("hidden");
				authMainContent.classList.remove("hidden");
				markAuthLoaderSeen();
				window.dispatchEvent(new CustomEvent("auth-loading-complete"));
			}, 3000); // Show loading for 3 seconds
		}
		return; // Exit early for login pages
	}

	// Handle main app loading (post-login)
	const mainLoadingContainer = document.getElementById("main-loading-container");
	const mainContent = document.getElementById("main-content");

	if (mainLoadingContainer && mainContent) {
		// Listen for app ready event to hide main loading
		window.addEventListener("app-ready", () => {
			setTimeout(() => {
				mainLoadingContainer.classList.add("hidden");
				mainContent.classList.remove("hidden");
			}, 500);
		});
	}
}

// Legacy exports for compatibility (simplified)
export function showAuthLoading() {
	/* Not needed in simplified version */
}
export function hideAuthLoading() {
	/* Not needed in simplified version */
}
export function showMainLoading() {
	/* Not needed in simplified version */
}
export function hideMainLoading() {
	/* Not needed in simplified version */
}
export function handleLoginLoadingState() {
	/* Not needed in simplified version */
}
export function handleMainContentLoadingState() {
	/* Not needed in simplified version */
}

export function isFirstVisit() {
	return !hasSeenAuthLoader();
}
