import axios from "axios";
import { useClipboard } from "@vueuse/core";
import { ref, onMounted, onUnmounted, computed, inject } from "vue";
import { useFavoritesStore } from "@/stores/favorites";
import { usePanelStore } from "@/stores/panelStore"; // Import the Pinia store
import { useToaster } from "@/composables/useToaster"; // Import the composable
import { useDialogStore } from "@/stores/dialogActions";

export function useDeviceViewPane(props, emit) {
	const appDirPath = inject("appDirPath", null);
	const deviceData = ref(null);
	const downloadStatus = ref(null);
	const favoritesStore = useFavoritesStore();
	const isLoading = ref(false);
	const leftNavSelected = ref("details");
	const mainNavSelected = ref("notifications"); // Set default value
	const panelElement2 = ref(null);
	const panelStore = usePanelStore(); // Access the panel store
	const { text, copy, copied, isSupported } = useClipboard();
	const { toastSuccess, toastError } = useToaster(); // Using toaster for notifications
	const dialogStore = useDialogStore();
	const { openDialog } = dialogStore;

	const favoriteItem = ref({
		id: props.editId,
		label: "",
		icon: "network-device",
		isFavorite: false,
		route: "/device/view/" + props.editId,
	});

	// Vue Lifecycle Hooks
	onMounted(() => {
		fetchDevice(props.editId);

		panelStore.panelRef2 = panelElement2.value;

		window.addEventListener("keydown", (e) => {
			if (e.key === "Escape") {
				onEsc();
			}
		});

		if (deviceData) {
			[...favoritesStore.favorites].forEach((favorite) => {
				if (favorite.id === props.editId) {
					favoriteItem.value.isFavorite = true;
				}
			});
		}

		// Restore the main nav selection from localStorage
		restoreMainNavView();

	});

	onUnmounted(() => {
		window.removeEventListener("keydown", (e) => {
			if (e.key === "Escape") {
				onEsc();
			}
		});
	});

	// Data Fetching
	function fetchDevice(id) {
		isLoading.value = true;
		axios.get(`/api/devices/${id}`).then((response) => {
			if (deviceData.value) {
				Object.assign(deviceData.value, response.data);
			} else {
				deviceData.value = response.data;
			}
			isLoading.value = false;
		});
	}

	// Business Logic
	function addToFavorites() {
		if (deviceData) {
			favoriteItem.value.id = deviceData.value.id;
			favoriteItem.value.label = deviceData.value.device_name;
			favoriteItem.value.route = `/device/view/${deviceData.value.id}`;
			favoriteItem.value.isFavorite = !favoriteItem.value.isFavorite;
			favoritesStore.toggleFavorite(favoriteItem.value);
		}
	}

	function selectLeftNavView(viewName) {
		if (viewName === "details") {
			leftNavSelected.value = "details";
		} else if (viewName === "comments") {
			leftNavSelected.value = "comments";
		} else {
			leftNavSelected.value = "details";
		}
	}

	function selectMainNavView(viewName) {
		const validViews = ["notifications", "configs"];
		const selectedView = validViews.includes(viewName) ? viewName : "notifications";

		// Update the reactive value
		mainNavSelected.value = selectedView;

		// Store in localStorage
		localStorage.setItem("DeviceDetailsMainNav", selectedView);
	}

	// Function to restore main nav selection from localStorage
	function restoreMainNavView() {
		const saved = localStorage.getItem("DeviceDetailsMainNav");
		const validViews = ["notifications", "configs"];

		if (saved && validViews.includes(saved)) {
			mainNavSelected.value = saved;
		} else {
			mainNavSelected.value = "notifications"; // default fallback
		}
	}

	function close() {
		emit("close");
	}

	function onEsc() {
		close();
	}

	function copyDebug(value) {
		try {
			copy(value);
			toastSuccess("Copied", "Debug command copied to clipboard");
		} catch (error) {
			toastError("Error", "Failed to copy Debug command to clipboard");
		}
	}

	function downloadNow() {
		downloadStatus.value = "Downloading...";

		toastSuccess("Download Queued", "Download started for device " + deviceData.value.device_name);
		axios
			.post("/api/device/download-now", {
				device_id: deviceData.value.id,
			})
			.then((response) => {
				downloadStatus.value = "Queued...";
				toastSuccess("Download Started", "Download job for " + deviceData.value.device_name + " was pushed to the queue.");
				checkTrackedJobStatus();
			})
			.catch((error) => {
				downloadStatus.value = "Queued...";
				toastError("Error", "Failed to start download job for " + deviceData.value.device_name);
				console.error("Error starting download job:", error);
			});
	}

	function checkTrackedJobStatus() {
		const interval = setInterval(function () {
			axios.get("/api/tracked-jobs/" + deviceData.value.id).then((response) => {
				downloadStatus.value = response.data.data.status;
			});

			if (downloadStatus.value === "finished") {
				// console.log('checkTrackedJobStatus finished');
				toastSuccess("Download Finished", "Download finished for device " + deviceData.value.device_name);
				clearInterval(interval); // thanks @Luca D'Amico
				setTimeout(() => {
					downloadStatus.value = null;
				}, 3000);
			}
		}, 2000);
	}

	function closeNav() {
		panelElement2?.value.isCollapsed ? panelElement2?.value.expand() : panelElement2?.value.collapse();
	}

	function recheckEocValidation() {
		axios
			.post("/api/eoc-recheck-device", {
				device_id: deviceData.value.id,
				category_id: deviceData.value.device_category_id,
			})
			.then((response) => {
				if (response.data.success) {
					toastSuccess("EOC Recheck", "EOC validation rechecked successfully queued for device " + deviceData.value.device_name);
					toastSuccess("EOC Recheck", "Refresh the configs table to see the updated EOC validation status in a few moments.");
				} else {
					toastError("EOC Recheck Failed", "Failed to recheck EOC validation for device " + deviceData.value.device_name);
				}
			})
			.catch((error) => {
				console.error("Error rechecking EOC validation:", error);
				toastError("Error", "Failed to recheck EOC validation: " + error.message);
			});
	}

	return {
		// State
		appDirPath,
		deviceData,
		downloadStatus,
		favoriteItem,
		isLoading,
		leftNavSelected,
		mainNavSelected,
		panelElement2,

		// Methods
		addToFavorites,
		closeNav,
		copyDebug,
		downloadNow,
		fetchDevice,
		openIntegrationUrl,
		recheckEocValidation,
		selectLeftNavView,
		selectMainNavView,
	};
}
