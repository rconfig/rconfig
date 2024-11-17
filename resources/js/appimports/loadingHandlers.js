export function handleLoginLoadingState() {
  const state = {
    isLoading: true
  };

  if (state.isLoading) {
    const loadingTime = Math.floor(Math.random() * (1500 - 300 + 1)) + 300; // Random time between 300ms and 1500ms
    setTimeout(() => {
      document.getElementById('auth-loading-container').classList.add('hidden');
      document.getElementById('auth-main-content').classList.remove('hidden');
      state.isLoading = false;
    }, loadingTime);
  }
}

export function handleMainContentLoadingState() {
  const state = {
    isMainContentLoading: true
  };
  console.log('main content loading state:', state.isMainContentLoading);

  if (state.isMainContentLoading) {
    const loadingTime = Math.floor(Math.random() * (1500 - 500 + 1)) + 500; // Random time between 500ms and 1500ms
    setTimeout(() => {
      document.getElementById('main-loading-container').classList.add('hidden');
      document.getElementById('main-content').classList.remove('hidden');
      state.isMainContentLoading = false;
      console.log('main content loading state:', state.isMainContentLoading);
    }, loadingTime);
  }
}
