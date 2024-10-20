export function setupGlobalProperties(app) {
  app.config.globalProperties.$userId = document.querySelector("meta[name='user-id']").getAttribute('content');
  app.config.globalProperties.$userName = document.querySelector("meta[name='user-name']").getAttribute('content');
  app.config.globalProperties.$userEmail = document.querySelector("meta[name='user-email']").getAttribute('content');
  app.config.globalProperties.$userRole = document.querySelector("meta[name='user-role']").getAttribute('content');
}
