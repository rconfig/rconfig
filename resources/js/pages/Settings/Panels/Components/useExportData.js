import axios from "axios";
import { ref, onMounted } from "vue";

export function useExportData() {
	const isLoading = ref(false);
	const isExporting = ref(false);
	const selectedTable = ref("");
	const tables = ref([]);
	const downloadUrl = ref(null);
	const filename = ref("");
	const isSuccess = ref(false);
	const isError = ref(false);
	const successMsg = ref("");
	const errorMsg = ref("");

	// Lifecycle hooks
	onMounted(() => {
		getExportableTables();
	});

	// Method to fetch exportable tables
	function getExportableTables() {
		isLoading.value = true;

		axios
			.get("/api/settings/export/list-tables")
			.then((response) => {
				tables.value = response.data.data.tables;
				isLoading.value = false;
			})
			.catch((error) => {
				isLoading.value = false;
				isError.value = true;
				errorMsg.value = error.response?.data?.message || "Failed to load exportable tables";
				setTimeout(() => {
					isError.value = false;
				}, 5000);
			});
	}

	// Method to export selected table to CSV
	function exportToCsv() {
		isExporting.value = true;

		axios
			.get(`/api/settings/export/get-table/${selectedTable.value}`)
			.then((response) => {
				downloadUrl.value = response.data.data.downloadUrl;
				filename.value = response.data.data.filename;
				isSuccess.value = true;
				successMsg.value = response.data.message || "Export successful. Click the download button to get your file.";
				setTimeout(() => {
					isSuccess.value = false;
				}, 5000);
			})
			.catch((error) => {
				isError.value = true;
				errorMsg.value = error.response?.data?.message || "Failed to export table";
				setTimeout(() => {
					isError.value = false;
				}, 5000);
			})
			.finally(() => {
				isExporting.value = false;
			});
	}

	// Method to handle direct download
	function downloadCsv() {
		if (downloadUrl.value) {
			// Create a temporary anchor element
			const link = document.createElement("a");
			link.href = downloadUrl.value;
			link.setAttribute("download", filename.value || "export.csv");
			link.setAttribute("target", "_blank");

			// Append to the document, trigger click, then remove
			document.body.appendChild(link);
			link.click();
			document.body.removeChild(link);
		}
	}
	return {
		downloadCsv,
		isLoading,
		isExporting,
		selectedTable,
		tables,
		downloadUrl,
		filename,
		isSuccess,
		isError,
		successMsg,
		errorMsg,
		getExportableTables,
		exportToCsv,
	};
}
