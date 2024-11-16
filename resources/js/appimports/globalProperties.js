export function setupGlobalProperties(app) {
  const userIdMeta = document.querySelector("meta[name='user-id']");
  const userNameMeta = document.querySelector("meta[name='user-name']");
  const userEmailMeta = document.querySelector("meta[name='user-email']");
  const userRoleMeta = document.querySelector("meta[name='user-role']");

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
}
