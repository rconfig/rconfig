import { ref, onMounted, onUnmounted, inject } from "vue";
import axios from "axios";
import { useToaster } from "@/composables/useToaster";
import { useSheetStore } from "@/stores/sheetActions";

export default function useConfigViewMainPanel(props) {
	const { toastError } = useToaster();
	const errors = ref([]);
	const config_location = ref("");
	const extension = ref("");
	const sheetStore = useSheetStore();
	const { openSheet } = sheetStore;
	const editorInstance = ref(null);


	function getDefaultEditorCode(meditor) {
		meditor.updateOptions({
			value: "Something went wrong - could not retrieve the configuration from the file system!",
		});
		toastError("Error", "Something went wrong - could not retrieve the configuration from the file system!");
	}

	async function showConfiguration(configId, meditor) {
		try {
			editorInstance.value = meditor;

			const { data } = await axios.get(`/api/configs/view-config/${configId}`);

			const content = data?.data?.content ?? "";
			const location = data?.data?.config_location ?? null;

			config_location.value = location || "No file location provided";
			meditor.getModel().setValue(content || "No content available.");

			if (location) {
				const filename = extractFilename(location);
				extension.value = getExtension(filename);

				switch (extension.value) {
					case "json":
						meditor.getModel().setLanguage("javascript");
						break;
					case "xml":
						meditor.getModel().setLanguage("xml");
						break;
					case "yaml":
					case "yml":
						meditor.getModel().setLanguage("yaml");
						break;
					default:
						meditor.getModel().setLanguage("plaintext");
						break;
				}
			} else {
				meditor.getModel().setLanguage("plaintext");
			}
		} catch (err) {
			console.error("Failed to load config:", err);
			meditor.updateOptions({
				value: "⚠️ Error: Unable to retrieve configuration from file system.",
				language: "plaintext",
			});
			toastError("Error", "Could not load the configuration. Please try again or check the logs.");
		}
	}

	function handleKeyDown(event, saveFunction) {
		if (event.ctrlKey && event.key === "Enter") {
			saveFunction();
		}
	}

	onMounted(async () => {
		// await checkPrismServerStatus();
		window.addEventListener("keydown", (event) => handleKeyDown(event, () => saveDialog(props.configId, model, null, emit, () => emit("close"))));
	});

	onUnmounted(() => {
		window.removeEventListener("keydown", handleKeyDown);
	});

	function extractFilename(pathOrFile) {
		if (!pathOrFile) return "";

		// Normalize path separator
		const parts = pathOrFile.split(/[/\\]/);
		return parts[parts.length - 1];
	}

	function getExtension(filename) {
		if (!filename.includes(".")) return "";
		return filename.split(".").pop().toLowerCase();
	}

	function sanitizeFilename(filename) {
		return filename.replace(/\s+/g, "_");
	}

	return {
		// State
		config_location,
		errors,

		// Functions
		getDefaultEditorCode,
		handleKeyDown,
		showConfiguration,

		openSheet,
	};
}
