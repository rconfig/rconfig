export function handleLoginLoadingState() {
  const state = {
    isLoading: true
  };

  if (state.isLoading) {
    const loadingTime = Math.floor(Math.random() * (1500 - 300 + 1)) + 300; // Random time between 300ms and 1500ms
    setTimeout(() => {
      const authLoadingContainer = document.getElementById('auth-loading-container');
      const authMainContent = document.getElementById('auth-main-content');
      if (authLoadingContainer && authMainContent) {
        authLoadingContainer.classList.add('hidden');
        authMainContent.classList.remove('hidden');
      }
      state.isLoading = false;
    }, loadingTime);
  }
}

export function handleMainContentLoadingState() {
  const state = {
    isMainContentLoading: true
  };

  if (state.isMainContentLoading) {
    const loadingTime = Math.floor(Math.random() * (1500 - 500 + 1)) + 500; // Random time between 500ms and 1500ms
    setTimeout(() => {
      const mainLoadingContainer = document.getElementById('main-loading-container');
      const mainContent = document.getElementById('main-content');
      if (mainLoadingContainer && mainContent) {
        mainLoadingContainer.classList.add('hidden');
        mainContent.classList.remove('hidden');
      }
      state.isMainContentLoading = false;
    }, loadingTime);
  }
}
