export function setupGlobalProperties(app) {
	const userIdMeta = document.querySelector("meta[name='user-id']");
	const userNameMeta = document.querySelector("meta[name='user-name']");
	const userEmailMeta = document.querySelector("meta[name='user-email']");
	const userRoleMeta = document.querySelector("meta[name='user-role']");
	const userLocaleMeta = document.querySelector("meta[name='user-locale']");
	const prismServerEnabledMeta = document.querySelector("meta[name='prism-server-enabled']");
	const serverDisplayNameMeta = document.querySelector("meta[name='server-display-name']");
	const serverDisplayColorMeta = document.querySelector("meta[name='server-display-color']");
	const serverDisplaySizeMeta = document.querySelector("meta[name='server-display-size']");

  if (userIdMeta) {
    app.config.globalProperties.$userId = userIdMeta.getAttribute('content');
  }
  if (userNameMeta) {
    app.config.globalProperties.$userName = userNameMeta.getAttribute('content');
  }
  if (userEmailMeta) {
    app.config.globalProperties.$userEmail = userEmailMeta.getAttribute('content');
  }
  if (userRoleMeta) {
    app.config.globalProperties.$userRole = userRoleMeta.getAttribute('content');
  }
  if (userLocaleMeta) {
		app.config.globalProperties.$userLocale = userLocaleMeta.getAttribute("content");
	}
	if (prismServerEnabledMeta) {
		app.config.globalProperties.$prismServerEnabled = prismServerEnabledMeta.getAttribute("content") == 1 ? true : false;
	}
	if (serverDisplayNameMeta) {
		app.config.globalProperties.$serverDisplayName = serverDisplayNameMeta.getAttribute("content");
	}
	if (serverDisplayColorMeta) {
		app.config.globalProperties.$serverDisplayColor = serverDisplayColorMeta.getAttribute("content");
	}
	if (serverDisplaySizeMeta) {
		app.config.globalProperties.$serverDisplaySize = serverDisplaySizeMeta.getAttribute("content");
	}
  const configEl = document.getElementById("app-config");
	if (configEl) {
		try {
			app.config.globalProperties.$config = JSON.parse(configEl.textContent);
		} catch (e) {
			console.error("Failed to parse app-config JSON", e);
			app.config.globalProperties.$config = {};
		}
	}
}
