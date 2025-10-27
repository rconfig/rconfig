import VueHighlightJS from "vue3-highlightjs";
import piniaPluginPersistedstate from "pinia-plugin-persistedstate";
import router from "@/router";
import { createPinia } from "pinia";
import RcIcon from "@/pages/Shared/Icon/RcIcon.vue"; // Import RcIcon component

export function setupPlugins(app) {
	// Register core plugins
	app.use(router);
	app.use(VueHighlightJS);

	// Register Pinia store
	const pinia = createPinia();
	pinia.use(piniaPluginPersistedstate);
	app.use(pinia);

	// Register RcIcon component globally
	app.component("RcIcon", RcIcon);
}
