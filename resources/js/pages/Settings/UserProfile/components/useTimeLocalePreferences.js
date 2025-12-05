import { ref, watch, inject } from "vue";
import { useToaster } from "@/composables/useToaster";
import localeOptions from "./locale/locales.json"; // renamed to avoid conflict

export function useTimeLocalePreferences(profileUserId, user) {
	const isSaving = ref(false); // Reactive state for saving status
	const locales = ref(localeOptions); // now using renamed constant
	const userLocale = inject("userLocale");
	const selectedLocale = ref(userLocale);
	const { toastSuccess, toastError } = useToaster();

	const preferences = ref({
		timezone: "UTC",
		dateFormat: "MM/DD/YYYY",
		timeFormat: "24",
		locale: "en_US",
	});

	// Watch for user changes and update preferences
	watch(selectedLocale, (newLocale) => {
		preferences.value.locale = newLocale;
		savePreferences();
	});

	watch(
		preferences,
		(newPreferences) => {
			console.log("Preferences changed:", newPreferences);
			savePreferences();
		},
		{ deep: true }
	);

	async function savePreferences() {
		isSaving.value = true; // Set saving state to true
		try {
			await axios.post(`/api/user/${profileUserId}/setLocale`, {
				datestyle: preferences.value.dateFormat,
				timestyle: preferences.value.timeFormat,
				locale: preferences.value.locale,
			});

			toastSuccess("Preferences Updated", "Your time and locale preferences have been saved.");
		} catch (error) {
			let errorMessage = "Failed to update preferences";

			if (error.response?.data?.message) {
				errorMessage = error.response.data.message;
			}

			toastError("Update Failed", errorMessage);
		} finally {
			isSaving.value = false; // Reset saving state
		}
	}

	function setSaving(value) {
		isSaving.value = value;
	}

	return {
		preferences,
		locales,
		savePreferences,
		selectedLocale,
		setSaving,
		isSaving,
	};
}
