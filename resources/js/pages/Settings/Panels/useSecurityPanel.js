import { ref } from 'vue';

export function useSecurityPanel() {
	// Keeping these as empty refs in case you want to add functionality later
	// or to prevent breaking changes if other components reference them
	const isLoading = ref(false);
	const isSsoLoading = ref(false);
	const fileStatus = ref(false);
	const ssoEnabled = ref(false);

	return {
		isLoading,
		isSsoLoading,
		fileStatus,
		ssoEnabled,
	};
}