import { ref, onMounted, onUnmounted, reactive } from "vue";
import axios from "axios";
import { useToaster } from "@/composables/useToaster";
import { useRouter } from "vue-router";

export default function useCommandOptions(props, emit, meditor) {
	// Services
	const { toastSuccess, toastError } = useToaster();
	const router = useRouter();

	// Form data
	const errors = ref([]);

	// Selection states
	const commandOptions = ref([]);
	const selectedCategory = ref([]);
	const commandSelection = ref();
	const isSubmitting = ref(false);
	const showResetConfirmDialog = ref(false);

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
	const code = ref("");

	// Lifecycle hooks
	onMounted(() => {
		const handleEscape = (event) => {
			if (event.key === "Escape") {
				event.preventDefault();
				onEsc();
			}
		};

		window.addEventListener("keydown", handleEscape);
		window._handleEscape = handleEscape;
	});

	onUnmounted(() => {
		// Clean up event listeners
		if (window._handleKeyDown) {
			window.removeEventListener("keydown", window._handleKeyDown);
			delete window._handleKeyDown;
		}
		if (window._handleCtrlS) {
			window.removeEventListener("keydown", window._handleCtrlS);
			delete window._handleCtrlS;
		}
		if (window._handleEscape) {
			window.removeEventListener("keydown", window._handleEscape);
			delete window._handleEscape;
		}
	});

	// API Functions
	function getCompareOptionsFromSettings(meditor) {
		isLoading.value = true;

		axios
			.get("/api/settings/compare-options/")
			.then((response) => {
				isLoading.value = false;

				// Update reactive settings object
				if (response.data.data.config_compare_settings) {
					Object.assign(configCompareSettings, response.data.data.config_compare_settings);
				} else {
					console.warn("No config_compare_settings found in response, setting default options.");
					setDefaultOptions();
				}

				configCompareExclusionFileContents.value = response.data.data.config_compare_exclusion_file;

				if (configCompareExclusionFileContents.value === null) {
					console.warn("No config_compare_exclusion_file found in response, fetching default template.");
					getDefaultTemplate(meditor);
				} else if (meditor) {
					meditor.getModel().setValue(configCompareExclusionFileContents.value);
				}
			})
			.catch((error) => {
				console.error("Error getting compare options:", error);
				isLoading.value = false;

				if (meditor) {
					meditor.updateOptions({
						value: "Something went wrong - could not retrieve the default template from the file system!",
					});
				}

				toastError("Error", "Could not retrieve compare options from settings: " + error.message);
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
				console.error("Error getting default template:", error);

				if (meditor) {
					meditor.updateOptions({
						value: "Something went wrong - could not retrieve the default template from the file system!",
					});
				}

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
				console.error("Error saving compare settings:", error);
				toastError("Error", "Could not save compare settings: " + error.message);
				throw error;
			})
			.finally(() => {
				isSubmitting.value = false;
			});
	}

	function fetchCommandOptions(categoryId) {
		axios
			.get(`/api/commands/by-category/${categoryId}`)
			.then((response) => {
				commandOptions.value = response.data.data;
			})
			.catch((error) => {
				console.error("Error fetching command options:", error);
				toastError("Error", "Could not fetch command options: " + error.message);
			});
	}

	// Utility Functions
	function setDefaultOptions() {
		Object.assign(configCompareSettings, {
			context: 3,
			ignoreCase: false,
			ignoreLineEnding: false,
			ignoreWhitespace: false,
			lengthLimit: 20000,
		});
	}

	function updateCategoryOptions(selectedCategories) {
		selectedCategory.value = selectedCategories;

		if (selectedCategories && selectedCategories.length > 0) {
			fetchCommandOptions(selectedCategories[0].id);
		} else {
			commandOptions.value = [];
			commandSelection.value = null;
		}
	}

	// UI Management
	function handleSave(meditor) {
		if (meditor) {
			configCompareExclusionFileContents.value = meditor.getValue();
			saveCompareSettings(meditor);
		} else {
			console.error("Editor not initialized");
			toastError("Error", "Editor not initialized");
		}
	}

	function close() {
		router.push({ name: "commands" });
	}

	function onEsc() {
		close();
	}

	function handleConfirmReset() {
		setDefaultOptions();
		showResetConfirmDialog.value = false;
	}

	// Return exposed properties and methods
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
		updateCategoryOptions,
		handleSave,
		handleConfirmReset,
	};
}
