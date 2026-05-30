import axios from "axios";
import { ref } from "vue";
import { FileDown, FileText, Check } from "lucide-vue-next";
import { useToaster } from "@/composables/useToaster"; // Import the composable

export function useImportData() {
	// Track the current step
	const currentStep = ref(1);
	const { toastSuccess, toastError } = useToaster();

	const steps = [
		{
			step: 1,
			title: "Download Template",
			description: "Download and populate import template",
			icon: FileDown,
			content: "Download the import template to get started. The template is a XLSX file that you can populate with your device data. The template includes all the required fields for importing devices.",
		},
		{
			step: 2,
			title: "Populate Template",
			description: "Match XLSX fields to system fields",
			icon: FileText,
			content: "Map the columns from your XLSX file to the corresponding system fields. Ensure required fields like device name and IP address are properly mapped.",
		},
		{
			step: 3,
			title: "Import",
			description: "Verify and complete import",
			icon: Check,
			content: "Review your data before finalizing the import. Check for any validation issues and confirm that all mappings are correct before proceeding.",
		},
	];

	function goToStep(step) {
		if (step >= 1 && step <= steps.length) {
			currentStep.value = step;
		}
	}

	// Get current step content
	const getCurrentStep = () => steps.find((s) => s.step === currentStep.value);

	function downloadTemplate() {
		// Generated locally so the template columns always match the importer.
		window.open("/download-import-template", "_blank");
	}

	// File upload state
	const file = ref(null);
	const fileObj = ref(null);
	const fileName = ref("");
	const fileErrors = ref([]);
	const isUploading = ref(false);
	const uploadSuccess = ref(false);
	const uploadError = ref(false);
	const errorMessage = ref("");

	function handleFileChange(event) {
		const selectedFile = event.target.files[0];
		if (selectedFile) {
			file.value = selectedFile;
			fileObj.value = selectedFile;
			fileName.value = selectedFile.name;
		}
	}

	function importFile() {
		if (!file.value) {
			toastError("No File Selected", "Please select a file to import.");
			return;
		}

		// Reset states
		isUploading.value = true;
		uploadSuccess.value = false;
		uploadError.value = false;
		fileErrors.value = [];

		// Create form data
		let formData = new FormData();
		formData.append("file", file.value);

		// Make API call
		axios
			.post("/api/settings/import/devices", formData, {
				headers: {
					"Content-Type": "multipart/form-data",
				},
			})
			.then((response) => {
				// Check for validation errors or warnings
				if (response.data.data?.errorlog && response.data.data.errorlog.length > 0) {
					fileErrors.value = response.data.data.errorlog;
				} else {
					// Handle success
					uploadSuccess.value = true;
					toastSuccess("Import Successful", "Devices imported successfully!");
				}

				// Reset file input
				resetFileInput();

				// Auto-hide success message after 5 seconds
				setTimeout(() => {
					uploadSuccess.value = false;
				}, 5000);
			})
			.catch((error) => {
				// Handle error
				uploadError.value = true;
				errorMessage.value = error.response?.data?.message || "An error occurred during import";

				// Add any validation errors
				if (error.response?.data?.data?.errorlog) {
					fileErrors.value = error.response.data.data.errorlog;
				}

				toastError("Import Failed", errorMessage.value);

				// Auto-hide error message after 5 seconds
				setTimeout(() => {
					uploadError.value = false;
				}, 5000);
			})
			.finally(() => {
				isUploading.value = false;
			});
	}

	function resetFileInput() {
		file.value = null;
		fileObj.value = null;
		fileName.value = "";
		const fileInput = document.getElementById("dropzone-file");
		if (fileInput) {
			fileInput.value = "";
		}
	}

	return {
		currentStep,
		steps,
		goToStep,
		getCurrentStep,
		downloadTemplate,
		handleFileChange,
		importFile,
		file,
		fileObj,
		fileName,
		fileErrors,
		isUploading,
		uploadSuccess,
		uploadError,
		errorMessage,
	};
}
