import "../css/global.css";
import "./appimports/bootstrap";
import "highlight.js/styles/base16/tomorrow-night.css";
import * as AuthComponents from "./appimports/authComponentImports";
import * as UIComponents from "./appimports/uiComponentImports";
import { NavigationSide, NavigationTop } from "./appimports/navComponentImports";
import { applyStoredTheme } from "./appimports/themeHandler";
import { createApp } from "vue";
import { initializeLoadingHandlers } from "./appimports/loadingHandlers";
import { registerComponents } from "./appimports/registerComponents";
import { setupGlobalProperties } from "./appimports/globalProperties";
import { setupPlugins } from "./appimports/setupPlugins";
import { useFormatters } from "./composables/useFormatters";
import { RCONFIG_DOCS_URL } from "./config/constants";
import { useColorMode } from "@vueuse/core";

// Initialize app
const app = createApp({});

// Register components with error handling
try {
	registerComponents(app, AuthComponents);
	registerComponents(app, UIComponents);
} catch (error) {
	console.error("Failed to register components:", error);
}

try {
	app.component("navigation-side", NavigationSide);
	app.component("navigation-top", NavigationTop);
} catch (error) {
	console.error("Failed to register navigation components:", error);
}

// Setup and configuration
setupPlugins(app); // icons imported here
applyStoredTheme();
setupGlobalProperties(app);

// Prepare global composables
const formatters = useFormatters();
app.config.globalProperties.$formatters = formatters;

// Extract global properties once for consistent usage
const globalProperties = app.config.globalProperties;

globalProperties.$rconfigDocsUrl = RCONFIG_DOCS_URL;
app.provide("rconfigDocsUrl", RCONFIG_DOCS_URL);

// Provide shared values to the component tree
app.provide("formatters", formatters);
app.provide("useremail", globalProperties.$userEmail);
app.provide("userid", globalProperties.$userId);
app.provide("username", globalProperties.$userName);
app.provide("timezone", globalProperties.$timezone);
app.provide("userLocale", globalProperties.$userLocale);
app.provide("appDirPath", globalProperties.$config?.appDirPath);
app.provide("serverDisplayName", globalProperties.$serverDisplayName);
app.provide("serverDisplayColor", globalProperties.$serverDisplayColor);
app.provide("serverDisplaySize", globalProperties.$serverDisplaySize); // New

// provide colorMode globally so it can be injected in any component without needing to import useColorMode everywhere
const colorMode = useColorMode();
app.provide("colorMode", colorMode);

// Mount the Vue app with error handling
let vm;
try {
	vm = app.mount("#app");
} catch (error) {
	console.error("Failed to mount Vue app:", error);
}

// Handle loading states once DOM is ready
if (document.readyState === "loading") {
	document.addEventListener("DOMContentLoaded", initializeLoadingHandlers);
} else {
	initializeLoadingHandlers();
}

// Debug helper for development
window.dd = function (...args) {
	const stack = new Error().stack.split("\n")[2]?.trim();
	console.group("🐛 DD Debug");
	console.log("Data:", ...args);
	console.log("📍 Location:", stack);
	console.groupEnd();
};

window.vueApp = app;

// Emit the app-ready event when Vue has mounted
window.dispatchEvent(new Event("app-ready"));