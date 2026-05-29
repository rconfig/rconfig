import axios from "axios";
import { ref } from "vue";
import { useToaster } from "@/composables/useToaster";

// Module-scoped state so the top-bar pill and the Update settings page
// share a single fetched result.
const currentVersion = ref(null);
const latestVersion = ref(null);
const updateAvailable = ref(false);
const latestUrl = ref(null);
const reachable = ref(true);
const checked = ref(false);
const lastCheckedAt = ref(null);
const consecutiveFailures = ref(0);
const lastError = ref(null);
const loading = ref(false);
const hasFetched = ref(false);

export function useVersionCheck() {
	const { toastError, toastSuccess } = useToaster();

	async function fetchVersionStatus(clearCache = false) {
		loading.value = true;
		try {
			const response = await axios.get("/api/version-check" + (clearCache ? "?clearCache=true" : ""));
			const data = response.data.data;
			currentVersion.value = data.current_version;
			latestVersion.value = data.latest_version;
			updateAvailable.value = data.update_available;
			latestUrl.value = data.latest_url;
			reachable.value = data.reachable;
			checked.value = data.checked;
			lastCheckedAt.value = data.last_checked_at;
			consecutiveFailures.value = data.consecutive_failures;
			lastError.value = data.last_error;
			hasFetched.value = true;

			// Only surface a toast on an explicit recheck, stay silent on the
			// background pill load.
			if (clearCache) {
				if (!data.reachable) {
					toastError("Version check", "Couldn't reach GitHub to check for updates.");
				} else if (data.update_available) {
					toastSuccess("Update available", `Version ${data.latest_version} is available.`);
				} else {
					toastSuccess("Up to date", "You are running the latest version.");
				}
			}
		} catch (error) {
			console.error("Error fetching version status:", error);
			reachable.value = false;
			if (clearCache) {
				toastError("Version check", "Failed to check for updates.");
			}
		} finally {
			loading.value = false;
		}
	}

	return {
		currentVersion,
		latestVersion,
		updateAvailable,
		latestUrl,
		reachable,
		checked,
		lastCheckedAt,
		consecutiveFailures,
		lastError,
		loading,
		hasFetched,
		fetchVersionStatus,
	};
}
