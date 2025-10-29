import { ref, onMounted, computed, watch } from "vue";
import { useFavoritesStore } from "@/stores/favorites";
import { useRoute, useRouter } from "vue-router";

export function useConfigs() {
	const favoritesStore = useFavoritesStore();
	const route = useRoute();
	const router = useRouter();

	// Reactive state
	const currentView = ref(localStorage.getItem("configsSelectedView") || "configs");
	const configsId = ref(parseInt(route.params.id) || null);
	const statusIdParam = ref(route.query.statusId || null);

	// Base navigation items configuration - Core features only
	const baseViewItems = computed(() => {
		return [
			{
				id: "configs",
				label: "Configurations",
				icon: "config-tools",
				isFavorite: favoritesStore.isFavorite("configs"),
				route: "/configs",
			},
			{
				id: "configsearch",
				label: "Config Search",
				icon: "config-search",
				isFavorite: favoritesStore.isFavorite("configsearch"),
				route: "/config-search",
			},
			{
				id: "configcompare",
				label: "Config Compare",
				icon: "config-compare",
				isFavorite: favoritesStore.isFavorite("configcompare"),
				route: "/config-compare",
			},
		];
	});

	// View items (can be extended later for custom ordering like inventory)
	const viewItems = computed(() => baseViewItems.value);

	// Nav pills items for the navigation component
	const navPillsItems = computed(() => {
		return viewItems.value.map((item) => ({
			label: item.label,
			to: item.id,
			icon: item.icon,
		}));
	});

	onMounted(() => {
		initializeView();
	});

	function initializeView() {
		if (route.params.view) {
			changeView(route.params.view);
			return;
		}

		if (viewItems.value.some((v) => v.id === route.name)) {
			currentView.value = route.name;
			return;
		}

		// Default to configs view
		if (route.path.includes("config")) {
			const routeName = route.name;
			if (["configs", "configsearch", "configcompare"].includes(routeName)) {
				currentView.value = routeName;
			}
		}
	}

	function handleRouteChange(routeName) {
		if (viewItems.value.some((v) => v.id === routeName)) {
			currentView.value = routeName;
		}
	}

	function changeView(view) {
		localStorage.setItem("configsSelectedView", view);
		currentView.value = view;

		if (route.name !== view) {
			router.push({ name: view }).catch((err) => console.error("Navigation error:", err));
		}
	}

	function handleNavSelection(view) {
		changeView(view);
	}

	function toggleFavorite(viewId) {
		const viewItem = viewItems.value.find((item) => item.id === viewId);
		if (viewItem) {
			favoritesStore.toggleFavorite(viewItem);
		}
	}

	// Watch for route changes
	watch(
		() => route.name,
		(newRouteName) => {
			handleRouteChange(newRouteName);
		}
	);

	// Watch for route params changes
	watch(
		() => route.params.id,
		(newId) => {
			configsId.value = parseInt(newId) || null;
		}
	);

	// Watch for query params changes
	watch(
		() => route.query.statusId,
		(newStatusId) => {
			statusIdParam.value = newStatusId || null;
		}
	);

	return {
		// State
		configsId,
		statusIdParam,
		currentView,
		viewItems,
		navPillsItems,

		// Methods
		changeView,
		handleNavSelection,
		toggleFavorite,

		// Store references
		favoritesStore,
		route,
		router,
	};
}