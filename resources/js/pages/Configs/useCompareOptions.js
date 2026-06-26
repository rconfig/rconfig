import { ref, onMounted, onUnmounted, reactive } from "vue";
import axios from "axios";
import { useToaster } from "@/composables/useToaster";
import { useRouter } from "vue-router";

export default function useCompareOptions() {
	const { toastSuccess, toastError } = useToaster();
	const router = useRouter();

	// Compare settings - reactive object for form binding
	const configCompareSettings = reactive({
		context: 3,
		ignoreCase: false,
		ignoreLineEnding: false,
		ignoreWhitespace: false,
		lengthLimit: 20000,
	});

	const configCompareExclusionFileContents = ref(null);
	const isLoading = ref(false);
	const isLoadingEditor = ref(true);
	const isSubmitting = ref(false);
	const showResetConfirmDialog = ref(false);

	onMounted(() => {
		const handleEscape = (event) => {
			if (event.key === "Escape") {
				event.preventDefault();
				close();
			}
		};
		window.addEventListener("keydown", handleEscape);
		window._handleEscape = handleEscape;
	});

	onUnmounted(() => {
		if (window._handleEscape) {
			window.removeEventListener("keydown", window._handleEscape);
			delete window._handleEscape;
		}
	});

	function getCompareOptionsFromSettings(meditor) {
		isLoading.value = true;

		axios
			.get("/api/settings/compare-options")
			.then((response) => {
				isLoading.value = false;

				if (response.data.data.config_compare_settings) {
					Object.assign(configCompareSettings, response.data.data.config_compare_settings);
				} else {
					setDefaultOptions();
				}

				configCompareExclusionFileContents.value = response.data.data.config_compare_exclusion_file;

				if (configCompareExclusionFileContents.value === null) {
					getDefaultTemplate(meditor);
				} else if (meditor) {
					meditor.getModel().setValue(configCompareExclusionFileContents.value);
				}
			})
			.catch((error) => {
				isLoading.value = false;
				if (meditor) {
					meditor.updateOptions({ value: "Something went wrong - could not retrieve the compare options!" });
				}
				toastError("Error", "Could not retrieve compare options: " + error.message);
			});
	}

	function getDefaultTemplate(meditor) {
		axios
			.get("/api/settings/compare-options/default-template")
			.then((response) => {
				configCompareExclusionFileContents.value = response.data.data;
				if (meditor) {
					meditor.getModel().setValue(configCompareExclusionFileContents.value);
				}
			})
			.catch((error) => {
				toastError("Error", "Could not retrieve default template: " + error.message);
			});
	}

	function saveCompareSettings(meditor) {
		isSubmitting.value = true;

		const payload = {
			context: configCompareSettings.context,
			ignoreCase: configCompareSettings.ignoreCase,
			ignoreLineEnding: configCompareSettings.ignoreLineEnding,
			ignoreWhitespace: configCompareSettings.ignoreWhitespace,
			lengthLimit: configCompareSettings.lengthLimit,
			config_compare_exclusion_file: meditor.getModel().getValue(),
		};

		return axios
			.patch("/api/settings/compare-options/1", payload)
			.then((response) => {
				toastSuccess("Success", "Compare settings saved successfully");
				return response;
			})
			.catch((error) => {
				toastError("Error", "Could not save compare settings: " + error.message);
				throw error;
			})
			.finally(() => {
				isSubmitting.value = false;
			});
	}

	function setDefaultOptions() {
		Object.assign(configCompareSettings, {
			context: 3,
			ignoreCase: false,
			ignoreLineEnding: false,
			ignoreWhitespace: false,
			lengthLimit: 20000,
		});
	}

	function close() {
		router.push({ name: "configcompare" });
	}

	function handleConfirmReset() {
		setDefaultOptions();
		showResetConfirmDialog.value = false;
	}

	return {
		// State
		configCompareSettings,
		isLoading,
		isLoadingEditor,
		isSubmitting,
		showResetConfirmDialog,

		// Methods
		close,
		getCompareOptionsFromSettings,
		saveCompareSettings,
		setDefaultOptions,
		handleConfirmReset,
	};
}
