// useFeedbackForm.js
import { ref, computed, onMounted, onUnmounted, watchEffect } from "vue";
import axios from "axios";
import { useToaster } from "@/composables/useToaster";

export function useFeedbackForm() {
	const { toastSuccess, toastError, toastWarning } = useToaster();

	// State
	const isDialogOpen = ref(false);
	const isSubmitting = ref(false);
	const config = ref(null);
	const connectivityStatus = ref({
		rconfig: { reachable: false, response_time_ms: null },
		feedback_enabled: false,
		last_checked: null,
		manual_override: { active: false },
		environment_overrides: {},
	});

	let connectivityInterval = null;

	const feedbackForm = ref({
		name: "",
		email: "",
		feedbackType: "",
		message: "",
	});

	// Computed properties - updated for simplified connectivity
	const isConnected = computed(() => connectivityStatus.value.rconfig.reachable);
	const canSubmitFeedback = computed(() => connectivityStatus.value.feedback_enabled);

	const networkQuality = computed(() => {
		if (!isConnected.value) return "offline";

		const responseTime = connectivityStatus.value.rconfig.response_time_ms;
		if (!responseTime) return "unknown";

		if (responseTime < 100) return "excellent";
		if (responseTime < 300) return "good";
		if (responseTime < 1000) return "fair";
		return "poor";
	});

	const connectivityBadgeVariant = computed(() => {
		if (!isConnected.value) return "destructive";
		switch (networkQuality.value) {
			case "excellent":
				return "success";
			case "good":
				return "default";
			case "fair":
				return "warning";
			case "poor":
				return "destructive";
			default:
				return "secondary";
		}
	});

	const connectivityMessage = computed(() => {
		// Check for overrides first
		if (connectivityStatus.value.manual_override?.active) {
			return "Manual Override Active";
		}

		if (connectivityStatus.value.environment_overrides?.checks_disabled) {
			return "Checks Disabled";
		}

		if (!isConnected.value) return "Portal Offline";
		if (!canSubmitFeedback.value) return "Feedback Disabled";

		switch (networkQuality.value) {
			case "excellent":
				return "Excellent Connection";
			case "good":
				return "Good Connection";
			case "fair":
				return "Fair Connection";
			case "poor":
				return "Poor Connection";
			default:
				return "Connected";
		}
	});

	const feedbackOptions = computed(() => {
		if (!config.value) return [];

		return Object.entries(config.value.feedback_types).map(([value, label]) => ({
			value,
			label: label,
		}));
	});

	// Character count for message
	const messageCharCount = computed(() => {
		return feedbackForm.value.message?.length || 0;
	});

	const maxMessageLength = computed(() => {
		return config.value?.max_message_length || 2000;
	});

	// Methods
	const loadFeedbackConfig = async () => {
		try {
			const response = await axios.get("/api/feedback/config");
			config.value = response.data;
		} catch (error) {
			console.warn("Could not load feedback config:", error);
			// Use fallback config
			config.value = {
				feedback_types: {
					"feature-request": "Feature Request",
					"bug-report": "Bug Report",
					improvement: "Improvement Suggestion",
					"dashboard-component": "Dashboard Component Request",
					general: "General Feedback",
				},
				max_message_length: 2000,
			};
		}
	};

	const checkConnectivity = async () => {
		try {
			const response = await axios.get("/api/connectivity/status", {
				timeout: 5000,
			});
			connectivityStatus.value = response.data;
		} catch (error) {
			console.warn("Connectivity check failed:", error);
			// Assume offline if check fails
			connectivityStatus.value = {
				rconfig: { reachable: false, response_time_ms: null },
				feedback_enabled: false,
				last_checked: new Date().toISOString(),
				manual_override: { active: false },
				environment_overrides: {},
			};
		}
	};

	const refreshConnectivity = async () => {
		try {
			toastWarning("Checking connectivity...");
			const response = await axios.post("/api/connectivity/refresh");
			connectivityStatus.value = response.data.status;
			toastSuccess("Connectivity status updated");
		} catch (error) {
			toastError("Failed to check connectivity");
		}
	};

	const submitFeedback = async () => {
		// Check connectivity before submitting
		if (!canSubmitFeedback.value) {
			toastError("Feedback submission is currently unavailable due to connectivity issues");
			return;
		}

		// Client-side validation
		if (!feedbackForm.value.name?.trim() || !feedbackForm.value.email?.trim() || !feedbackForm.value.feedbackType || !feedbackForm.value.message?.trim()) {
			toastError("Please fill in all required fields");
			return;
		}

		// Email validation
		const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		if (!emailRegex.test(feedbackForm.value.email)) {
			toastError("Please enter a valid email address");
			return;
		}

		// Message length validation
		if (config.value && feedbackForm.value.message.length > config.value.max_message_length) {
			toastError(`Message must be less than ${config.value.max_message_length} characters`);
			return;
		}

		isSubmitting.value = true;

		try {
			const payload = {
				name: feedbackForm.value.name.trim(),
				email: feedbackForm.value.email.trim(),
				feedbackType: feedbackForm.value.feedbackType,
				message: feedbackForm.value.message.trim(),
			};

			const response = await axios.post("/api/feedback/submit", payload, {
				timeout: 30000, // 30 seconds timeout
				headers: {
					"Content-Type": "application/json",
					Accept: "application/json",
				},
			});

			if (response.data.success) {
				toastSuccess("Thank you! Your feedback has been submitted successfully.");

				// Reset form and close dialog
				resetForm();
				isDialogOpen.value = false;
			} else {
				throw new Error(response.data.message || "Submission failed");
			}
		} catch (error) {
			console.error("Error submitting feedback:", error);

			if (error.response) {
				// Server responded with error status
				const status = error.response.status;
				const data = error.response.data;

				if (status === 503 && data.error === "service_unavailable") {
					toastError("Feedback service is currently unavailable due to connectivity issues. Please try again later.");
					// Refresh connectivity status
					await checkConnectivity();
				} else if (status === 422 && data.errors) {
					// Validation errors
					const firstError = Object.values(data.errors)[0][0];
					toastError(firstError);
				} else if (status === 502) {
					toastError("Feedback service is temporarily unavailable. Please try again later.");
				} else {
					toastError(data.message || `Server error (${status})`);
				}
			} else if (error.code === "ECONNABORTED") {
				// Timeout error
				toastError("Request timed out. Please check your connection and try again.");
				await checkConnectivity();
			} else if (error.message.includes("Network Error")) {
				toastError("Network error. Please check your portal connection.");
				await checkConnectivity();
			} else {
				toastError(`Error: ${error.message}`);
			}
		} finally {
			isSubmitting.value = false;
		}
	};

	const resetForm = () => {
		feedbackForm.value = {
			name: "",
			email: "",
			feedbackType: "",
			message: "",
		};
	};

	// Watch for connectivity changes
	const handleConnectivityChange = () => {
		if (!isConnected.value && isDialogOpen.value) {
			toastWarning("Portal connection lost. Feedback submission may not be available.");
		}
	};

	// Lifecycle
	const initialize = async () => {
		await Promise.all([loadFeedbackConfig(), checkConnectivity()]);

		// Check connectivity every 2 minutes
		connectivityInterval = setInterval(checkConnectivity, 120000);
	};

	const cleanup = () => {
		if (connectivityInterval) {
			clearInterval(connectivityInterval);
		}
	};

	// Watch connectivity status
	watchEffect(() => {
		handleConnectivityChange();
	});

	return {
		// State
		isDialogOpen,
		isSubmitting,
		config,
		connectivityStatus,
		feedbackForm,

		// Computed
		isConnected,
		canSubmitFeedback,
		networkQuality,
		connectivityBadgeVariant,
		connectivityMessage,
		feedbackOptions,
		messageCharCount,
		maxMessageLength,

		// Methods
		loadFeedbackConfig,
		checkConnectivity,
		refreshConnectivity,
		submitFeedback,
		resetForm,
		initialize,
		cleanup,
	};
}