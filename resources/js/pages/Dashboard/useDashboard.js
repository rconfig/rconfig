import axios from "axios";
import { ref, watch } from "vue";
import { useToaster } from "@/composables/useToaster"; // Import the composable

export function useDashboard() {
	const sysinfo = ref([]);
	const configinfo = ref([]);
	const healthLatest = ref([]);
	const isLoadingSysinfo = ref(false);
	const isLoadingConfiginfo = ref(false);
	const isLoadingHealth = ref(false);
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

	return {
		fetchSysinfo,
		fetchConfiginfo,
		fetchHealth,
		sysinfo,
		configinfo,
		healthLatest,
		isLoadingSysinfo,
		isLoadingConfiginfo,
		isLoadingHealth,
		toastSuccess,
		toastError,
	};
}
