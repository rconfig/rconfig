import { ref, onMounted, computed, watch } from "vue";
import { useFavoritesStore } from "@/stores/favorites";
import { useRoute, useRouter } from "vue-router";

export function useInventory() {
	const favoritesStore = useFavoritesStore();
	const route = useRoute();
	const router = useRouter();

	const actualRoute = route.name;

	// Reactive state
	const currentView = ref(localStorage.getItem("inventorySelectedView") || "devices");

	// New state for ordering
	const customOrder = ref(JSON.parse(localStorage.getItem("inventoryItemOrder") || "[]"));
	const sortDirection = ref(localStorage.getItem("inventorySortDirection") || "asc");

	// Base navigation items configuration - Core features only
	const baseViewItems = computed(() => {
		return [
			{
				id: "commandgroups",
				label: "Command Groups",
				icon: "command-group",
				isFavorite: favoritesStore.isFavorite("commandgroups"),
				route: "/commandgroups",
			},
			{
				id: "commands",
				label: "Commands",
				icon: "commands",
				isFavorite: favoritesStore.isFavorite("commands"),
				route: "/commands",
			},
			{
				id: "devices",
				label: "Devices",
				icon: "device",
				isFavorite: favoritesStore.isFavorite("devices"),
				route: "/devices",
			},
			{
				id: "tags",
				label: "Tags",
				icon: "tag",
				isFavorite: favoritesStore.isFavorite("tags"),
				route: "/tags",
			},
			{
				id: "templates",
				label: "Templates",
				icon: "template",
				isFavorite: favoritesStore.isFavorite("templates"),
				route: "/templates",
			},
			{
				id: "vendors",
				label: "Vendors",
				icon: "vendor",
				isFavorite: favoritesStore.isFavorite("vendors"),
				route: "/vendors",
			},
		];
	});

	// Ordered view items based on custom order or default order
	const viewItems = computed(() => {
		const items = baseViewItems.value;

		if (customOrder.value.length === 0) {
			return items;
		}

		// Create a map for quick lookup
		const itemsMap = new Map(items.map((item) => [item.id, item]));
		const orderedItems = [];

		// Add items in custom order
		customOrder.value.forEach((id) => {
			if (itemsMap.has(id)) {
				orderedItems.push(itemsMap.get(id));
				itemsMap.delete(id);
			}
		});

		// Add any remaining items that weren't in the custom order
		itemsMap.forEach((item) => {
			orderedItems.push(item);
		});

		return orderedItems;
	});

	onMounted(() => {
		initializeView();
	});

	/**
	 * Save custom order to localStorage
	 */
	function saveCustomOrder(newOrder) {
		customOrder.value = newOrder;
		localStorage.setItem("inventoryItemOrder", JSON.stringify(newOrder));
	}

	/**
	 * Sort items alphabetically - toggles between ascending and descending
	 */
	function sortAlphabetically() {
		// Toggle sort direction
		const newDirection = sortDirection.value === "asc" ? "desc" : "asc";
		sortDirection.value = newDirection;
		localStorage.setItem("inventorySortDirection", newDirection);

		const sortedItems = [...baseViewItems.value].sort((a, b) => {
			const comparison = a.label.localeCompare(b.label);
			return newDirection === "asc" ? comparison : -comparison;
		});

		const newOrder = sortedItems.map((item) => item.id);
		saveCustomOrder(newOrder);
	}

	/**
	 * Reset to default order
	 */
	function resetToDefaultOrder() {
		customOrder.value = [];
		sortDirection.value = "asc";
		localStorage.removeItem("inventoryItemOrder");
		localStorage.removeItem("inventorySortDirection");
	}

	/**
	 * Handle drag start
	 */
	function handleDragStart(event, itemId) {
		event.dataTransfer.setData("text/plain", itemId);
		event.dataTransfer.effectAllowed = "move";

		// Add visual feedback
		event.target.style.opacity = "0.5";
	}

	/**
	 * Handle drag end
	 */
	function handleDragEnd(event) {
		event.target.style.opacity = "1";
	}

	/**
	 * Handle drag over
	 */
	function handleDragOver(event) {
		event.preventDefault();
		event.dataTransfer.dropEffect = "move";
	}

	/**
	 * Handle drop
	 */
	function handleDrop(event, targetItemId) {
		event.preventDefault();

		const draggedItemId = event.dataTransfer.getData("text/plain");
		if (draggedItemId === targetItemId) return;

		const currentOrder = viewItems.value.map((item) => item.id);
		const draggedIndex = currentOrder.indexOf(draggedItemId);
		const targetIndex = currentOrder.indexOf(targetItemId);

		if (draggedIndex === -1 || targetIndex === -1) return;

		// Remove dragged item and insert at target position
		currentOrder.splice(draggedIndex, 1);
		currentOrder.splice(targetIndex, 0, draggedItemId);

		saveCustomOrder(currentOrder);
	}

	function handleRouteChange(routeName) {
		if (viewItems.value.some((v) => v.id === routeName)) {
			currentView.value = routeName;
		}
	}

	function initializeView() {
		if (route.params.view) {
			changeView(route.params.view);
			return;
		}

		if (viewItems.value.some((v) => v.id === route.name)) {
			currentView.value = route.name;
			return;
		}

		if (route.path.includes("inventory")) {
			changeView("devices");
		}
	}

	function changeView(view) {
		localStorage.setItem("inventorySelectedView", view);
		currentView.value = view;

		if (route.name !== view) {
			router.push({ name: view }).catch((err) => console.error("Navigation error:", err));
		}
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

	return {
		// State
		actualRoute,
		currentView,
		viewItems,
		sortDirection,

		// Methods
		changeView,
		toggleFavorite,

		// Drag & sort methods
		sortAlphabetically,
		resetToDefaultOrder,
		handleDragStart,
		handleDragEnd,
		handleDragOver,
		handleDrop,
	};
}