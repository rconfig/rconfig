// stores/useLicenseInfoStore.js
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import axios from "axios";
import semver from "semver";

export const useLicenseInfoStore = defineStore("licenseInfo", () => {
	// State - simplified to just data and loading
	const data = ref({
		license: null,
		update: null,
		notice: null,
		connectivity: null,
	});

	const loading = ref(false);
	const dismissedNoticeId = ref(localStorage.getItem("dismissed_portal_notice_id"));

	// Computed properties - all logic here instead of in composable
	const hasUpdate = computed(() => {
		if (!data.value.update) return false;
		return semver.gt(data.value.update.latest_version, data.value.update.current_version);
	});

	// Updated to work with simplified rConfig portal connectivity
	const internetStatus = computed(() => {
		const conn = data.value.connectivity;
		if (!conn) return { status: "unknown", text: "Unknown", icon: "Globe" };

		// Check for manual override
		if (conn.manual_override?.active) {
			const isReachable = conn.rconfig?.reachable || false;
			return {
				status: isReachable ? "connected" : "disconnected",
				text: isReachable ? "Online (Override)" : "Offline (Override)",
				icon: isReachable ? "Wifi" : "WifiOff",
			};
		}

		// Check environment overrides
		if (conn.environment_overrides?.checks_disabled) {
			return { status: "disabled", text: "Disabled", icon: "Globe" };
		}

		if (conn.environment_overrides?.force_offline) {
			return { status: "disconnected", text: "Forced Offline", icon: "WifiOff" };
		}

		if (conn.environment_overrides?.force_online) {
			return { status: "connected", text: "Forced Online", icon: "Wifi" };
		}

		// Normal connectivity status
		if (conn.rconfig?.reachable) {
			// Determine quality based on response time if available
			const responseTime = conn.rconfig.response_time_ms;
			let quality = "Online";

			if (responseTime) {
				if (responseTime < 100) quality = "Excellent";
				else if (responseTime < 300) quality = "Good";
				else if (responseTime < 1000) quality = "Fair";
				else quality = "Slow";
			}

			return {
				status: "connected",
				text: quality,
				icon: "Wifi",
			};
		}

		return { status: "disconnected", text: "Offline", icon: "WifiOff" };
	});

	const subscriptionStatus = computed(() => {
		const license = data.value.license?.data;
		if (!license) return { type: "unknown", active: false, text: "Unknown" };

		const planType = license.plan_type || "";
		const isActive = license.status === "active" || license.status === "valid";

		// Simple plan type detection
		let type = "professional";
		if (planType.includes("enterprise")) type = "enterprise";
		if (planType.includes("vector")) type = "vector";

		return {
			type,
			active: isActive,
			text: license.sub_name || type,
			error: license.error_code ? `${license.error_code}: ${license.error_message}` : null,
		};
	});

	const activeNotice = computed(() => {
		const notice = data.value.notice;
		if (!notice || dismissedNoticeId.value === notice.id) return null;

		if (notice.expires_at && new Date(notice.expires_at) < new Date()) return null;

		return notice;
	});

	// Single fetch method that gets everything
	const fetchAll = async () => {
		if (loading.value) return;

		loading.value = true;
		try {
			// Fetch all data in parallel
			const [licenseRes, updateRes, noticeRes, connRes] = await Promise.allSettled([axios.get("/api/license-info"), axios.get("/api/check-for-update"), axios.get("/api/portal-notice"), axios.get("/api/connectivity/status")]);

			// Update data based on successful responses
			if (licenseRes.status === "fulfilled") {
				data.value.license = licenseRes.value.data;
			}

			if (updateRes.status === "fulfilled" && updateRes.value.data?.[0]) {
				data.value.update = updateRes.value.data[0];
			}

			if (noticeRes.status === "fulfilled" && noticeRes.value.data?.success) {
				data.value.notice = noticeRes.value.data.data;
			}

			if (connRes.status === "fulfilled") {
				data.value.connectivity = connRes.value.data;
			}
		} catch (error) {
			console.error("Failed to fetch data:", error);
		} finally {
			loading.value = false;
		}
	};

	const dismissNotice = (noticeId) => {
		dismissedNoticeId.value = noticeId?.toString();
		localStorage.setItem("dismissed_portal_notice_id", dismissedNoticeId.value);
	};

	// Auto-refresh every 5 minutes
	let refreshInterval;
	const startAutoRefresh = () => {
		if (refreshInterval) clearInterval(refreshInterval);
		refreshInterval = setInterval(fetchAll, 5 * 60 * 1000);
	};

	const stopAutoRefresh = () => {
		if (refreshInterval) {
			clearInterval(refreshInterval);
			refreshInterval = null;
		}
	};

	return {
		// State
		data,
		loading,

		// Computed
		hasUpdate,
		internetStatus,
		subscriptionStatus,
		activeNotice,

		// Methods
		fetchAll,
		dismissNotice,
		startAutoRefresh,
		stopAutoRefresh,
	};
});
