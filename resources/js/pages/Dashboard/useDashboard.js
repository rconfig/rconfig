import axios from "axios";
import { ref, watch } from "vue";
import { useToaster } from "@/composables/useToaster"; // Import the composable

export function useDashboard() {
	const sysinfo = ref([]);
	const configinfo = ref([]);
	const healthLatest = ref([]);
	const latestDevices = ref([]);
	const isLoadingSysinfo = ref(false);
	const isLoadingConfiginfo = ref(false);
	const isLoadingHealth = ref(false);
	const isLoadingLatestDevices = ref(false);
	const { toastSuccess, toastError } = useToaster(); // Using toaster for notifications

	async function fetchSysinfo(clearCache = false, params = {}) {
		isLoadingSysinfo.value = true;
		try {
			const response = await axios.get("/api/dashboard/sysinfo?clearcache=" + clearCache);
			sysinfo.value = response.data;
		} catch (error) {
			console.error("Error fetching sysinfo:", error);
			toastError("Error", "Failed to fetch sysinfo.");
		} finally {
			isLoadingSysinfo.value = false;
		}
	}

	async function fetchConfiginfo(params = {}) {
		isLoadingConfiginfo.value = true;
		try {
			const response = await axios.get("/api/dashboard/configinfo");
			configinfo.value = response.data;
		} catch (error) {
			console.error("Error fetching configinfo:", error);
			toastError("Error", "Failed to fetch configinfo.");
		} finally {
			isLoadingConfiginfo.value = false;
		}
	}

	async function fetchHealth(params = {}) {
		isLoadingHealth.value = true;
		try {
			const response = await axios.get("/api/dashboard/health-latest");
			healthLatest.value = response.data;
		} catch (error) {
			console.error("Error fetching healthLatest:", error);
			toastError("Error", "Failed to fetch healthLatest.");
		} finally {
			isLoadingHealth.value = false;
		}
	}

	async function fetchLatestDevices(params = {}) {
		isLoadingLatestDevices.value = true;
		try {
			const response = await axios.get("/api/dashboard/latest-devices");
			latestDevices.value = response.data;
		} catch (error) {
			console.error("Error fetching latest devices:", error);
			toastError("Error", "Failed to fetch latest devices.");
		} finally {
			isLoadingLatestDevices.value = false;
		}
	}

	return {
		fetchSysinfo,
		fetchConfiginfo,
		fetchHealth,
		fetchLatestDevices,
		sysinfo,
		configinfo,
		healthLatest,
		latestDevices,
		isLoadingSysinfo,
		isLoadingConfiginfo,
		isLoadingHealth,
		isLoadingLatestDevices,
		toastSuccess,
		toastError,
	};
}
