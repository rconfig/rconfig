// stores/onboardingStore.js
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import axios from "axios";
import { useConfetti } from "@/composables/useConfetti";
import { useToaster } from "@/composables/useToaster";

export const useOnboardingStore = defineStore("onboarding", () => {
	const { toastSuccess, toastError } = useToaster();
	const steps = ref({});
	const isLoading = ref(false);
	const isOnboardingCompleted = ref(false);
	const { celebrateStepCompletion, celebrateWithFireworks } = useConfetti();

	// LocalStorage keys
	const STORAGE_KEYS = {
		completedSteps: "onboarding_completed_steps",
		isCompleted: "onboarding_is_completed",
		lastCheck: "onboarding_last_check",
	};

	const hasSteps = computed(() => Object.keys(steps.value).length > 0);

	const completedPercentage = computed(() => {
		const stepValues = Object.values(steps.value);
		if (stepValues.length === 0) return 0;

		const completedSteps = stepValues.filter((step) => step.status).length;
		return Math.round((completedSteps / stepValues.length) * 20) * 5;
	});

	// Get completed steps from localStorage
	const getLocalCompletedSteps = () => {
		try {
			const stored = localStorage.getItem(STORAGE_KEYS.completedSteps);
			return stored ? JSON.parse(stored) : [];
		} catch (error) {
			console.error("Failed to parse localStorage completed steps:", error);
			return [];
		}
	};

	// Check if onboarding is completed in localStorage
	const getLocalOnboardingCompleted = () => {
		return localStorage.getItem(STORAGE_KEYS.isCompleted) === "true";
	};

	// Save completed step to localStorage
	const saveCompletedStepLocally = (stepKey) => {
		const completedSteps = getLocalCompletedSteps();
		if (!completedSteps.includes(stepKey)) {
			completedSteps.push(stepKey);
			localStorage.setItem(STORAGE_KEYS.completedSteps, JSON.stringify(completedSteps));
		}
	};

	// Save onboarding completion status
	const saveOnboardingCompletedLocally = () => {
		localStorage.setItem(STORAGE_KEYS.isCompleted, "true");
		localStorage.setItem(STORAGE_KEYS.lastCheck, Date.now().toString());
	};

	// Check if we need to fetch from server (daily check or first time)
	const shouldFetchFromServer = () => {
		const lastCheck = localStorage.getItem(STORAGE_KEYS.lastCheck);
		if (!lastCheck) return true;

		const oneDayAgo = Date.now() - 24 * 60 * 60 * 1000;
		return parseInt(lastCheck) < oneDayAgo;
	};

	// Merge local and server data
	const mergeStepsData = (serverSteps) => {
		const localCompleted = getLocalCompletedSteps();
		const mergedSteps = { ...serverSteps };

		// Mark locally completed steps as completed
		Object.keys(mergedSteps).forEach((stepKey) => {
			if (localCompleted.includes(stepKey)) {
				mergedSteps[stepKey].status = true;
			}
		});

		return mergedSteps;
	};

	const setSteps = (newSteps) => {
		steps.value = newSteps;
	};

	const fetchSteps = async () => {
		// Check if onboarding is completed locally first
		if (getLocalOnboardingCompleted() && !shouldFetchFromServer()) {
			isOnboardingCompleted.value = true;
			steps.value = {};
			return;
		}

		isLoading.value = true;
		try {
			const { data } = await axios.get("/api/onboarding/steps");

			// If server returns empty steps, user has completed onboarding
			if (Object.keys(data).length === 0) {
				// Only celebrate if not already stored in localStorage
				const wasAlreadyCompleted = getLocalOnboardingCompleted();

				isOnboardingCompleted.value = true;
				saveOnboardingCompletedLocally();
				steps.value = {};

				// Only run celebration if this is the first time detecting completion
				if (!wasAlreadyCompleted) {
					setTimeout(() => {
						celebrateWithFireworks();
					}, 200);
				}
			} else {
				// Merge server data with local completed steps
				const mergedSteps = mergeStepsData(data);
				steps.value = mergedSteps;
				isOnboardingCompleted.value = false;
			}
		} catch (error) {
			console.error("Failed to fetch steps:", error);
			// Fallback to localStorage if server fails
			if (getLocalOnboardingCompleted()) {
				isOnboardingCompleted.value = true;
				steps.value = {};
			}
		} finally {
			isLoading.value = false;
		}
	};

	const completeStep = async (stepKey) => {
		// Check if step is already completed locally
		const localCompleted = getLocalCompletedSteps();
		if (localCompleted.includes(stepKey)) {
			console.log(`Step ${stepKey} already completed locally`);
			return;
		}

		// Check if step is already completed in current state
		if (steps.value[stepKey]?.status) {
			console.log(`Step ${stepKey} already completed in current state`);
			return;
		}

		try {
			const response = await axios.post("/api/onboarding/complete-step", {
				step: stepKey,
			});

			// Save to localStorage immediately
			saveCompletedStepLocally(stepKey);

			// Update local state
			if (steps.value[stepKey]) {
				steps.value[stepKey].status = true;
			}

			// Trigger confetti for step completion (only if not previously completed)
			celebrateStepCompletion();
			toastSuccess(`Onboarding step '${stepKey}' completed successfully.`);

			// Check if all steps completed
			if (response.data.onboarding_is_completed) {
				console.log("All onboarding steps completed!" + response.data);
				toastSuccess(`All onboarding steps completed!`);

				// Save completion status locally
				saveOnboardingCompletedLocally();
				isOnboardingCompleted.value = true;

				// Clear steps to hide onboarding
				setTimeout(() => {
					steps.value = {};
				}, 1000);
			}

			return response.data;
		} catch (error) {
			console.error("Failed to complete step:", error);
			toastError(`Failed to complete onboarding step '${stepKey}': ${error.message}`);

			// Still save locally in case of network issues
			saveCompletedStepLocally(stepKey);
			if (steps.value[stepKey]) {
				steps.value[stepKey].status = true;
			}
			throw error;
		}
	};

	// Clear localStorage (for testing or reset)
	const clearOnboardingData = () => {
		localStorage.removeItem(STORAGE_KEYS.completedSteps);
		localStorage.removeItem(STORAGE_KEYS.isCompleted);
		localStorage.removeItem(STORAGE_KEYS.lastCheck);
		isOnboardingCompleted.value = false;
		steps.value = {};
	};

	// Get onboarding status for debugging
	const getOnboardingStatus = () => {
		return {
			localCompleted: getLocalCompletedSteps(),
			isCompleted: getLocalOnboardingCompleted(),
			lastCheck: localStorage.getItem(STORAGE_KEYS.lastCheck),
			shouldFetch: shouldFetchFromServer(),
			currentSteps: steps.value,
		};
	};

	return {
		steps,
		isLoading,
		isOnboardingCompleted,
		hasSteps,
		completedPercentage,
		setSteps,
		fetchSteps,
		completeStep,
		clearOnboardingData,
		getOnboardingStatus,
	};
});
