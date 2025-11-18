import axios from 'axios';
import { ref, onMounted } from 'vue';

export function useSecurityPanel() {
	const isLoading = ref(false);
	const isSsoLoading = ref(false);
	const ssoEnabled = ref(false);

	function getSsoStatus() {
		isSsoLoading.value = true;
		axios
			.get("/api/settings/socialite-status")
			.then((response) => {
				setTimeout(() => {
				ssoEnabled.value = response.data.is_socialite || false;
				isSsoLoading.value = false;
				}, 2000);
			})
			.catch((error) => {
				console.log("Error fetching SSO status:", error);
				isSsoLoading.value = false;
			});
	}

	onMounted(() => {
		getSsoStatus();
	});

	return {
		isLoading,
		isSsoLoading,
		ssoEnabled,
	};
}