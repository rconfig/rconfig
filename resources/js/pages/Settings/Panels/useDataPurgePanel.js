// --- Imports ---
import { ref, reactive, computed, onMounted, watch, onBeforeUnmount, inject } from "vue";
import axios from "axios";
// Internal composables
import { useToaster } from "@/composables/useToaster";

export const CONFIGS_TARGET = "configs";

export function parsePurgeOptions(options) {
	try {
		return (typeof options === "string" ? JSON.parse(options) : options) || {};
	} catch (e) {
		return {};
	}
}

export function useDataPurgePanel() {
	// --- Stores & injected deps ---
	const formatters = inject("formatters");
	const { toastSuccess, toastError } = useToaster();
	// --- UI state (refs) ---
	const loading = ref(true);
	const error = ref(null);
	const editingId = ref(null);
	const savingEdit = ref(false);
	const showTip = ref(localStorage.getItem("showDataPurgeTip") !== "false");
	const configsSaving = ref(false);
	const configsSaveError = ref(null);
	const purgeAllFailedLoading = ref(false);

	let searchDebounceId = null;
	let configsDebounceId = null;
	// Last config purge values known to be saved, so loading the page never triggers a save.
	let savedConfigsSnapshot = null;
	// --- Data state (refs) ---
	const purgeSettings = ref([]);
	const configsSetting = ref(null);
	const otherSettings = ref([]);

	const configsEditForm = reactive({
		retention_days: 1825,
		batch_size: 1000,
		cleanup_frequency: "daily",
		non_downloaded_retention_days: 30,
		min_versions_per_device_command: 3,
		enabled: false,
	});

	const meta = ref(null);
	const filters = reactive({
		page: 1,
		perPage: 25,
		enabledOnly: false,
		search: "",
	});
	const searchTerm = ref(filters.search);
	const cleanupFrequencyOptions = [
		{ label: "Daily", value: "daily" },
		{ label: "Weekly", value: "weekly" },
		{ label: "Monthly", value: "monthly" },
	];
	const editForm = reactive({
		id: null,
		target: "",
		retention_days: 30,
		batch_size: 1000,
		cleanup_frequency: "daily",
		enabled: false,
	});
	const sortState = reactive({
		field: "target",
		direction: "asc",
	});
	// --- Computed ---
	const sortParam = computed(() => (sortState.direction === "asc" ? sortState.field : `-${sortState.field}`));
	const totalRecords = computed(() => otherSettings.value.length);
	const lastPage = computed(() => meta.value?.last_page ?? 1);
	// --- Table / list utilities ---
	function getOptionValue(setting, key, defaultValue) {
		return parsePurgeOptions(setting?.options)[key] ?? defaultValue;
	}
	function toggleSort(field) {
		if (sortState.field === field) {
			sortState.direction = sortState.direction === "asc" ? "desc" : "asc";
		} else {
			sortState.field = field;
			sortState.direction = "asc";
		}
		filters.page = 1;
		fetchPurgeSettings();
	}
	function handlePageChange(page) {
		if (page === filters.page) return;
		filters.page = page;
		fetchPurgeSettings();
	}
	function handlePerPageChange(perPage) {
		if (perPage === filters.perPage) return;
		filters.perPage = perPage;
		filters.page = 1;
		fetchPurgeSettings();
	}
	// --- Fetch / API ---
	function fetchPurgeSettings() {
		loading.value = true;
		error.value = null;
		const params = {
			page: filters.page,
			perPage: filters.perPage,
			sort: sortParam.value,
		};
		if (filters.enabledOnly) {
			params["filter[enabled_only]"] = 1;
		}
		const searchQuery = filters.search.trim();
		if (searchQuery) {
			params["filter[q]"] = searchQuery;
		}
		return axios
			.get("/api/settings/purge", { params })
			.then((res) => {
				purgeSettings.value = res.data?.data ?? [];
				meta.value = {
					current_page: res.data?.current_page ?? 1,
					per_page: res.data?.per_page ?? filters.perPage,
					total: res.data?.total ?? purgeSettings.value.length,
					last_page: res.data?.last_page ?? 1,
				};
				filters.page = meta.value.current_page;
				filters.perPage = meta.value.per_page;

				// The config purge card stays in place when a search or filter leaves it out of the list.
				const foundConfigsSetting = purgeSettings.value.find((s) => s.target === CONFIGS_TARGET);
				if (foundConfigsSetting) {
					configsSetting.value = foundConfigsSetting;
				}
				otherSettings.value = purgeSettings.value.filter((s) => s.target !== CONFIGS_TARGET);
			})
			.catch((e) => {
				error.value = extractErrorMessage(e, "Failed to load purge settings.");
				purgeSettings.value = [];
				otherSettings.value = [];
				toastError("Error", error.value);
			})
			.finally(() => {
				loading.value = false;
			});
	}
	// --- Formatters ---
	function extractErrorMessage(error, fallback = "An unexpected error occurred.") {
		const response = error?.response?.data;
		if (response?.errors) {
			const firstError = Object.values(response.errors)
				.flat()
				.find((msg) => msg);
			if (firstError) {
				return firstError;
			}
		}
		if (response?.message && typeof response.message === "string") {
			return response.message;
		}
		return error?.message || fallback;
	}
	// --- Business logic / Actions ---
	function resetEditForm() {
		Object.assign(editForm, {
			id: null,
			target: "",
			retention_days: 30,
			batch_size: 1000,
			cleanup_frequency: "daily",
			enabled: false,
		});
	}
	function startEdit(setting) {
		editingId.value = setting.id;
		const parsedOptions = parsePurgeOptions(setting.options);
		Object.assign(editForm, {
			id: setting.id,
			target: setting.target ?? "",
			retention_days: setting.retention_days ?? 30,
			batch_size: parsedOptions.batch_size ?? 1000,
			cleanup_frequency: parsedOptions.cleanup_frequency ?? "daily",
			enabled: parsedOptions.enabled ?? false,
		});
	}
	function cancelEdit() {
		editingId.value = null;
		resetEditForm();
	}
	function validateEditForm() {
		if (!editForm.target || editForm.target.trim() === "") {
			toastError("Validation Error", "Target table name is required.");
			return false;
		}
		if (!Number.isInteger(editForm.retention_days) || editForm.retention_days < 1) {
			toastError("Validation Error", "Retention days must be a positive integer.");
			return false;
		}
		if (!Number.isInteger(editForm.batch_size) || editForm.batch_size < 1) {
			toastError("Validation Error", "Batch size must be a positive integer.");
			return false;
		}
		return true;
	}
	function saveEdit() {
		if (!editingId.value || savingEdit.value) return;
		if (!validateEditForm()) {
			return;
		}
		const payload = {
			target: editForm.target.trim(),
			retention_days: editForm.retention_days,
			options: JSON.stringify({
				batch_size: editForm.batch_size,
				cleanup_frequency: editForm.cleanup_frequency,
				enabled: editForm.enabled,
			}),
		};
		savingEdit.value = true;
		axios
			.patch(`/api/settings/purge/${editingId.value}`, payload)
			.then(() => {
				toastSuccess("Setting Updated", "Purge setting updated successfully.");
				cancelEdit();
				fetchPurgeSettings();
			})
			.catch((e) => {
				toastError("Error", extractErrorMessage(e, "Failed to update purge setting."));
			})
			.finally(() => {
				savingEdit.value = false;
			});
	}
	function toggleEnableDisable(setting) {
		const isEnabled = getOptionValue(setting, "enabled", false) === true;
		const payload = {
			target: setting.target,
			retention_days: setting.retention_days,
			options: JSON.stringify({ enabled: !isEnabled }),
		};
		axios
			.patch(`/api/settings/purge/${setting.id}`, payload)
			.then(() => {
				toastSuccess("Setting Updated", `Purge setting ${isEnabled ? "disabled" : "enabled"} successfully.`);
				fetchPurgeSettings();
			})
			.catch((e) => {
				toastError("Error", extractErrorMessage(e, "Failed to toggle purge setting."));
			});
	}

	function closeTip() {
		showTip.value = false;
		localStorage.setItem("showDataPurgeTip", "false");
	}

	function applyEnabledFilter() {
		filters.page = 1;
		fetchPurgeSettings();
	}

	function refreshSettings() {
		fetchPurgeSettings();
	}

	function clearSearch() {
		searchTerm.value = "";
	}

	async function purgeAllFailedConfigs() {
		purgeAllFailedLoading.value = true;
		try {
			await axios.post("/api/device/purge-failed-configs", { device_id: "--all" });
			toastSuccess("Purge Started", "Failed configs purge job has been queued for all devices.");
		} catch {
			toastError("Error", "Failed to start the purge job.");
		} finally {
			purgeAllFailedLoading.value = false;
		}
	}

	// --- Config purge card (auto-save) ---
	function configsFormValues() {
		return {
			retention_days: configsEditForm.retention_days,
			batch_size: configsEditForm.batch_size,
			cleanup_frequency: configsEditForm.cleanup_frequency,
			non_downloaded_retention_days: configsEditForm.non_downloaded_retention_days,
			min_versions_per_device_command: configsEditForm.min_versions_per_device_command,
			enabled: configsEditForm.enabled,
		};
	}

	function configsFormIsValid(values) {
		return ["retention_days", "batch_size", "non_downloaded_retention_days", "min_versions_per_device_command"].every((key) => Number.isInteger(values[key]) && values[key] >= 1);
	}

	async function saveConfigsSetting(values) {
		if (!configsSetting.value) return;

		if (!configsFormIsValid(values)) {
			configsSaveError.value = "Every value must be a whole number of 1 or more.";
			return;
		}

		const snapshot = JSON.stringify(values);
		configsSaving.value = true;
		configsSaveError.value = null;
		try {
			const { retention_days, ...options } = values;
			await axios.patch(`/api/settings/purge/${configsSetting.value.id}`, {
				retention_days,
				options: JSON.stringify(options),
			});
			savedConfigsSnapshot = snapshot;
		} catch (e) {
			configsSaveError.value = extractErrorMessage(e, "Failed to save config purge settings.");
			toastError("Error", configsSaveError.value);
		} finally {
			configsSaving.value = false;
		}
	}

	// --- Watchers ---
	watch(
		() => searchTerm.value,
		(newValue) => {
			filters.search = newValue;
			filters.page = 1;
			if (searchDebounceId) {
				clearTimeout(searchDebounceId);
			}
			searchDebounceId = setTimeout(() => {
				fetchPurgeSettings();
			}, 300);
		},
	);

	// Fill the card from the server. The snapshot marks these values as already saved.
	watch(configsSetting, (val) => {
		if (!val) return;
		Object.assign(configsEditForm, {
			retention_days: val.retention_days ?? 1825,
			batch_size: getOptionValue(val, "batch_size", 1000),
			cleanup_frequency: getOptionValue(val, "cleanup_frequency", "daily"),
			non_downloaded_retention_days: getOptionValue(val, "non_downloaded_retention_days", 30),
			min_versions_per_device_command: getOptionValue(val, "min_versions_per_device_command", 3),
			enabled: getOptionValue(val, "enabled", false) === true,
		});
		savedConfigsSnapshot = JSON.stringify(configsFormValues());
	});

	watch(configsFormValues, (newVal) => {
		if (!configsSetting.value || JSON.stringify(newVal) === savedConfigsSnapshot) return;
		if (configsDebounceId) clearTimeout(configsDebounceId);
		configsDebounceId = setTimeout(() => saveConfigsSetting(newVal), 400);
	});

	onBeforeUnmount(() => {
		if (searchDebounceId) clearTimeout(searchDebounceId);
		if (configsDebounceId) clearTimeout(configsDebounceId);
	});
	// --- Lifecycle ---
	onMounted(fetchPurgeSettings);
	// --- Return ---
	return {
		// state
		purgeSettings,
		configsSetting,
		configsSaving,
		configsSaveError,
		otherSettings,
		configsEditForm,
		loading,
		error,
		filters,
		searchTerm,
		editingId,
		editForm,
		savingEdit,
		cleanupFrequencyOptions,
		sortParam,
		totalRecords,
		lastPage,
		formatters,
		showTip,

		// methods
		getOptionValue,
		toggleSort,
		handlePageChange,
		handlePerPageChange,
		startEdit,
		cancelEdit,
		saveEdit,
		toggleEnableDisable,
		applyEnabledFilter,
		refreshSettings,
		clearSearch,
		closeTip,
		purgeAllFailedConfigs,
		purgeAllFailedLoading,
	};
}
