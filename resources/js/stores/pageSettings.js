import { defineStore } from "pinia";
import { ref } from "vue";

export const usePageSettingsStore = defineStore("pageSettings", () => {
	// All settings stored as { [pageKey]: { perPage: 10, ... } }
	const settings = ref({});

	// Load from localStorage on init
	function load() {
		const raw = localStorage.getItem("rConfigPageSettings");
		settings.value = raw ? JSON.parse(raw) : {};
	}

	// Save to localStorage
	function save() {
		localStorage.setItem("rConfigPageSettings", JSON.stringify(settings.value));
	}

	// Get a setting for a page, with default fallback
	function get(pageKey, setting, defaultValue) {
		load();
		return settings.value?.[pageKey]?.[setting] ?? defaultValue;
	}

	// Set a setting for a page
	function set(pageKey, setting, value) {
		load();
		if (!settings.value[pageKey]) settings.value[pageKey] = {};
		settings.value[pageKey][setting] = value;
		save();
	}

	return { settings, get, set };
});
