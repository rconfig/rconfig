import { ref, onMounted, computed } from "vue";
import { useFavoritesStore } from "@/stores/favorites";
import { useRoute, useRouter } from "vue-router";

export function useConfigs() {
	const favoritesStore = useFavoritesStore();
	const route = useRoute();
	const router = useRouter();
	const currentView = ref(localStorage.getItem("inventorySelectedView") || "configs");
	const configsId = ref(parseInt(route.params.id) || 0);
	const statusIdParam = ref(route.query.statusId || null);

	const viewItems = [
		{ id: "configs", label: "Configurations", icon: "config-tools", isFavorite: ref(false), route: "/configs" },
		{ id: "configsearch", label: "Config Search", icon: "config-search", isFavorite: ref(false), route: "/config-search" },
		{ id: "configcompare", label: "Config Compare", icon: "config-compare", isFavorite: ref(false), route: "/config-compare" },
		{ id: "configreport", label: "Config Report", icon: "config-reports", isFavorite: ref(false), route: "/config-report" },
	];

	// NavPills-compatible items format
	const navPillsItems = computed(() =>
		viewItems.map((item) => ({
			label: item.label,
			to: item.id, // Use id instead of route for consistency with NavPills
			icon: item.icon,
		}))
	);

	onMounted(() => {
		const routeName = route.name;

		// Set current view if route matches a known view
		if (viewItems.some((v) => v.id === routeName)) {
			currentView.value = routeName;
			localStorage.setItem("inventorySelectedView", routeName);
		}
		// Handle explicit view parameter
		else if (route.params.view && viewItems.some((v) => v.id === route.params.view)) {
			changeViewSidePopover(route.params.view);
		}

		// Preserve query parameters if they exist
		if (Object.keys(route.query).length > 0 && currentView.value === routeName) {
			router.replace({ name: currentView.value, query: route.query });
		}

		// Initialize favorites from store
		viewItems.forEach((item) => {
			item.isFavorite.value = favoritesStore.isFavorite(item.id);
		});
	});

	function changeViewSidePopover(view) {
		// Update localStorage for persistence
		localStorage.setItem("inventorySelectedView", view);
		currentView.value = view;

		// Always use Vue Router navigation for view changes
		if (route.name !== view) {
			router.push({ name: view }).catch((err) => console.error("Navigation error:", err));
		}
	}

	function changeViewNavPills(view) {
		// Update localStorage for persistence
		localStorage.setItem("inventorySelectedView", view);
		currentView.value = view;

		// Handle URL updates
		if (route.path.includes("/config") || route.name?.includes("config")) {
			// Use history.replaceState for smooth navigation within configs module
			if (route.name !== view) {
				const pathMap = {
					configs: "/configs",
					configsearch: "/config-search",
					configcompare: "/config-compare",
					configreport: "/config-report",
				};

				const newPath = pathMap[view];
				if (newPath) {
					window.history.replaceState({}, "", newPath);
				} else {
					// Fallback to router resolution
					const newUrl = router.resolve({ name: view }).href;
					window.history.replaceState({}, "", newUrl);
				}
			}
		} else {
			// Use normal router navigation if outside configs module
			if (route.name !== view) {
				router.push({ name: view }).catch((err) => console.error("Navigation error:", err));
			}
		}
	}

	function toggleFavorite(viewId) {
		const viewItem = viewItems.find((item) => item.id === viewId);
		if (viewItem) {
			viewItem.isFavorite.value = !viewItem.isFavorite.value;
			favoritesStore.toggleFavorite(viewItem);
		}
	}

	function handleNavSelection(selectedView) {
		changeViewNavPills(selectedView);
	}

	return {
		// State
		configsId,
		statusIdParam,
		currentView,
		viewItems,
		navPillsItems,

		// Methods
		changeViewNavPills,
		changeViewSidePopover,
		handleNavSelection,
		toggleFavorite,

		// Store references
		favoritesStore,
		route,
		router,
	};
}
