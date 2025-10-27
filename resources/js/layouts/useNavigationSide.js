import { ref, onMounted, onUnmounted, watch } from "vue";
import { useRouter } from "vue-router";
import { useExternalLinksStore } from "@/stores/externalLinksStore";
import { useFavoritesStore } from "@/stores/favorites";
import { usePanelStore } from "@/stores/panelStore";
import { useSheetStore } from "@/stores/sheetActions";
import { useToaster } from "@/composables/useToaster";

export function useNavigationSide(userid) {
	const router = useRouter();
	const favoritesStore = useFavoritesStore();
	const externalLinksStore = useExternalLinksStore();
	const externalLinks = ref([]);
	const panelStore = usePanelStore();
	const panelElement = ref(null);
	const sheetStore = useSheetStore();
	const { openSheet } = sheetStore;
	const externalLinksDialogKey = ref(0);
	const notificationsLength = ref(0);
	const { toastSuccess, toastError } = useToaster();

	// Use localStorage to persist the state of the collapsibles
	const sideNavExtLinksIsOpen = ref(JSON.parse(localStorage.getItem("sideNavExtLinksIsOpen")) ?? true);
	const sideNavFavLinksIsOpen = ref(JSON.parse(localStorage.getItem("sideNavFavLinksIsOpen")) ?? true);

	watch(sideNavExtLinksIsOpen, (newVal) => {
		localStorage.setItem("sideNavExtLinksIsOpen", JSON.stringify(newVal));
	});

	watch(sideNavFavLinksIsOpen, (newVal) => {
		localStorage.setItem("sideNavFavLinksIsOpen", JSON.stringify(newVal));
	});

	// Media query for a certain breakpoint
	const mobileQuery = window.matchMedia("(max-width: 768px)");

	// Function to handle panel state based on screen size
	function handleBreakpointChange() {
		if (mobileQuery.matches) {
			// If the screen width is less than or equal to 768px, close the panel
			panelStore.panelRef?.collapse();
		} else {
			// If the screen width is greater than 768px, expand the panel (optional)
			panelStore.panelRef?.expand();
		}
	}

	onMounted(() => {
		panelStore.panelRef = panelElement; // Set the panelElement ref globally via Pinia

		// Add event listener for window resize
		mobileQuery.addEventListener("change", handleBreakpointChange);

		// Call it initially to set the correct state on load
		handleBreakpointChange();

		loadLinksFromStoreOrDb();
	});

	const navToSettingsUpgrade = () => {
		router.push({ name: "settings-upgrade" });
	};

	onUnmounted(() => {
		// Clean up the event listener when the component is unmounted
		mobileQuery.removeEventListener("change", handleBreakpointChange);
	});

	function closeExtDialog() {
		externalLinksDialogKey.value += 1;
		loadLinksFromStoreOrDb();
	}

	function loadLinksFromStoreOrDb() {
		// Check if the store already has links
		if (externalLinksStore.links.length > 0) {
			// Use the links from the store
			externalLinks.value = externalLinksStore.links;
		} else {
			axios
				.get(`/api/user/get-external-links/${userid}`)
				.then((response) => {
					// Store the fetched links in Pinia for future use
					externalLinksStore.setLinks(response.data);

					// Assign the links to the local reference
					externalLinks.value = response.data;
				})
				.catch((error) => {
					console.error("Error fetching external links:", error);
				});
		}
	}

	const removeExternalLink = async (name) => {
		try {
			// Make API request to delete the link by name
			await axios
				.post(`/api/user/remove-external-link`, { name: encodeURIComponent(name) })
				.then((response) => {
					// Store the fetched links in Pinia for future use
					externalLinksStore.setLinks(response.data);

					// Assign the links to the local reference
					externalLinks.value = response.data;

					// Update the local reference to reflect changes
					loadLinksFromStoreOrDb();
					console.log("Link removed successfully");
					toastSuccess("External Link", "Link removed successfully");
				})
				.catch((error) => {
					console.error("Error fetching external links:", error);
					toastError("External Link", "Error removing link");
				});
		} catch (error) {
			console.error("Error removing link:", error);
			toastError("External Link", "Error removing link");
		}
	};

	function closeNav() {
		panelElement?.value.isCollapsed ? panelElement?.value.expand() : panelElement?.value.collapse();
	}

	function notificationsCount(count) {
		notificationsLength.value = count;
	}

	return {
		sideNavExtLinksIsOpen,
		sideNavFavLinksIsOpen,
		externalLinks,
		externalLinksDialogKey,
		favoritesStore,
		notificationsLength,
		panelElement,
		closeNav,
		closeExtDialog,
		removeExternalLink,
		notificationsCount,
		navToSettingsUpgrade,
		openSheet,
	};
}