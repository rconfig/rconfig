// useAppStatusBar.js
import { computed, onMounted, onUnmounted, inject, ref } from "vue";
import { useLicenseInfoStore } from "@/stores/useLicenseInfoStore";
import { useRouter } from "vue-router";
import { Code, CheckCircle, Zap, Crown, AlertCircle, Wifi, WifiOff, Globe, Lock } from "lucide-vue-next";

export function useAppStatusBar() {
	const store = useLicenseInfoStore();
	const router = useRouter();
	const formatters = inject("formatters");

	// --- HTTPS notification state ---
	const showHttpsPopover = ref(false);
	const isHttpsDisabled = computed(() => store.data.connectivity?.https_enabled === false);

	function openHttpsPopover() {
		showHttpsPopover.value = true;
	}
	function closeHttpsPopover() {
		showHttpsPopover.value = false;
	}

	// Version status configuration
	const versionConfig = computed(() => {
		if (!store.data.update) {
			return { icon: Code, variant: "secondary", text: "Unknown" };
		}

		if (store.hasUpdate) {
			return {
				icon: Zap,
				variant: "info",
				text: `Update Available: ${store.data.update.latest_version}`,
			};
		}

		return {
			icon: CheckCircle,
			variant: "success",
			text: "Up to Date",
		};
	});

	// rConfig portal connectivity configuration - now using store's internetStatus
	const internetConfig = computed(() => {
		const status = store.internetStatus;
		const iconMap = { Wifi, WifiOff, Globe };

		return {
			...status,
			icon: iconMap[status.icon] || Globe,
			class: {
				"text-green-500": status.status === "connected",
				"text-red-500": status.status === "disconnected",
				"text-amber-500": status.status === "disabled",
				"text-gray-500": status.status === "unknown",
			},
		};
	});

	// Subscription configuration
	const subscriptionConfig = computed(() => {
		const sub = store.subscriptionStatus;

		const configs = {
			professional: { icon: Crown, color: "text-blue-600 dark:text-blue-400" },
			enterprise: { icon: Zap, color: "text-purple-600 dark:text-purple-400" },
			vector: { icon: Crown, color: "text-blue-600 dark:text-blue-400" },
		};

		const config = configs[sub.type] || configs.professional;

		if (sub.error) {
			return {
				icon: AlertCircle,
				color: "text-red-600 dark:text-red-400",
				text: "License Error",
				active: false,
				error: sub.error,
			};
		}

		return {
			...config,
			text: formatters.capitalizeFirstLetter(sub.text),
			active: sub.active,
		};
	});

	// Event handlers
	const handleRefresh = () => store.fetchAll();
	const handleUpdate = () => router.push("/settings/update");

	// Lifecycle
	onMounted(() => {
		store.fetchAll();
		store.startAutoRefresh();
	});

	onUnmounted(() => {
		store.stopAutoRefresh();
	});

	return {
		// Store data
		loading: computed(() => store.loading),
		currentVersion: computed(() => store.data.update?.current_version),
		latestVersion: computed(() => store.data.update?.latest_version),
		licenseData: computed(() => store.data.license?.data),
		connectivityData: computed(() => store.data.connectivity),
		activeNotice: computed(() => store.activeNotice),

		// Configurations
		versionConfig,
		internetConfig,
		subscriptionConfig,

		// HTTPS notification
		isHttpsDisabled,
		showHttpsPopover,
		openHttpsPopover,
		closeHttpsPopover,

		// Methods
		handleRefresh,
		handleUpdate,
		dismissNotice: store.dismissNotice,

		// Utils
		formatters,
	};
}