import axios from "axios";
import { ref, onMounted, onUnmounted, watch, inject } from "vue";
import { useDialogStore } from "@/stores/dialogActions";
import { useToaster } from "@/composables/useToaster";
import { useClipboard } from "@vueuse/core";

export function useSendSnippetModal(props, emit) {
	// --- Stores & Utilities ---
	const dialogStore = useDialogStore();
	const { closeDialog } = dialogStore;
	const { toastSuccess, toastError } = useToaster();
	const { copy, copied } = useClipboard();
	const formatters = inject("formatters");

	// --- State ---
	const isLoading = ref(true);
	const snippets = ref([]);
	const selectedSnippet = ref(null);
	const selectedSnippetForDisplay = ref(null); // HTML formatted for display
	const selectedSnippetPlainText = ref(null); // Plain text for API/copy
	const dynamicVarsArr = ref({});
	const selectedSnippetId = ref("0");

	// --- Lifecycle Hooks ---
	onMounted(async () => {
		try {
			await getSnippets();
			// Add keyboard shortcut event listener
			window.addEventListener("keydown", handleKeyDown);
		} finally {
			isLoading.value = false;
		}
	});

	onUnmounted(() => {
		window.removeEventListener("keydown", handleKeyDown);
	});

	// --- API Methods ---

	/**
	 * Fetch all available snippets.
	 */
	const getSnippets = async () => {
		try {
			const response = await axios.get("/api/snippets");
			snippets.value = response.data.data || response.data;
		} catch (error) {
			toastError("Error", "Failed to load snippets.");
			console.error("Error fetching snippets:", error);
		}
	};

	// --- Snippet Selection Methods ---

	/**
	 * Select a snippet and process dynamic variables.
	 */
	const selectSnippet = (snippetId) => {
		const id = parseInt(snippetId);
		if (id === 0) {
			selectedSnippet.value = null;
			selectedSnippetForDisplay.value = null;
			selectedSnippetPlainText.value = null;
			dynamicVarsArr.value = {};
			return;
		}

		const snippet = snippets.value.find((s) => s.id === id);
		if (snippet) {
			selectedSnippet.value = { ...snippet };
			// Keep original plain text for processing
			selectedSnippetPlainText.value = snippet.snippet;
			// Format for display with HTML
			selectedSnippetForDisplay.value = formatters.formatSnippetCode(snippet.snippet);
			getDynamicVariables();
		}
	};

	/**
	 * Extract dynamic variables from snippet content.
	 */
	const getDynamicVariables = () => {
		if (!selectedSnippet.value) return;

		const snippet = selectedSnippetPlainText.value;
		const regex = /{([^}]+)}/g;
		const matches = snippet.match(regex);

		if (!matches) {
			dynamicVarsArr.value = {};
			return;
		}

		const dynamicVarsObj = {};
		matches.forEach((match) => {
			const key = match.replace("{", "").replace("}", "");
			dynamicVarsObj[key] = "";
		});

		dynamicVarsArr.value = dynamicVarsObj;
	};

	/**
	 * Update snippet string with dynamic variable values.
	 */
	const updateSnippetString = () => {
		if (!selectedSnippet.value || !selectedSnippetPlainText.value) return;

		let plainSnippet = selectedSnippet.value.snippet; // Original plain snippet
		let displaySnippet = formatters.formatSnippetCode(selectedSnippet.value.snippet); // HTML formatted

		// Replace variables in both versions
		Object.keys(dynamicVarsArr.value).forEach((key) => {
			if (dynamicVarsArr.value[key] !== "") {
				const regex = new RegExp("{" + key + "}", "g");
				plainSnippet = plainSnippet.replace(regex, dynamicVarsArr.value[key]);
				displaySnippet = displaySnippet.replace(regex, dynamicVarsArr.value[key]);
			}
		});

		// Update both versions
		selectedSnippetPlainText.value = plainSnippet;
		selectedSnippetForDisplay.value = displaySnippet;
	};

	// --- Utility Methods ---

	/**
	 * Close the modal dialog.
	 */
	const close = () => {
		// Reset state
		selectedSnippetId.value = "0";
		selectedSnippet.value = null;
		selectedSnippetForDisplay.value = null;
		selectedSnippetPlainText.value = null;
		dynamicVarsArr.value = {};
		closeDialog("SendSnippetModal");
		emit("closeModal");
	};

	/**
	 * Submit the snippet to the device.
	 */
	const submitSnippet = async () => {
		// Validate that a snippet is selected
		if (!selectedSnippet.value) {
			toastError("Error", "Please select a snippet");
			return;
		}

		// Validate dynamic variables are filled
		if (Object.keys(dynamicVarsArr.value).length > 0) {
			const emptyVars = Object.keys(dynamicVarsArr.value).filter((key) => dynamicVarsArr.value[key] === "");

			if (emptyVars.length > 0) {
				toastError("Error", "Please fill in all dynamic variables");
				return;
			}
		}

		try {
			const response = await axios.post("/api/device/send-snippet-now", {
				device_id: props.deviceId,
				snippet_id: selectedSnippet.value.id,
				dynamic_vars: dynamicVarsArr.value,
				// Send the plain text version
				snippet_content: selectedSnippetPlainText.value,
			});

			toastSuccess("Success", `Snippet sent to queue for device ${props.deviceId} successfully`);

			emit("submitSnippet", selectedSnippet.value);
			close();
		} catch (error) {
			const errorMessage = error.response?.data?.message || error.message || "Unknown error occurred";
			toastError("Error", errorMessage);
			console.error("Submit snippet error:", error);
		}
	};

	// --- Keyboard Shortcut Handler ---

	/**
	 * Handle Ctrl+Enter keyboard shortcut to submit.
	 */
	const handleKeyDown = (event) => {
		if (event.ctrlKey && event.key === "Enter") {
			event.preventDefault();
			submitSnippet();
		}
	};

	watch(selectedSnippetId, (newId) => {
		selectSnippet(newId);
	});

	// --- Return API ---
	return {
		// State
		copied,
		dynamicVarsArr,
		isLoading,
		selectedSnippet,
		selectedSnippetId,
		selectedSnippetForDisplay, // HTML formatted for display
		selectedSnippetPlainText, // Plain text for API/copy
		snippets,

		// Methods
		close,
		copy,
		selectSnippet,
		submitSnippet,
		updateSnippetString,
	};
}
