import { ref, computed, watch, onMounted } from "vue";

export function useMultiSelect({ items, modelValue, singleSelect = false, displayField = "name", searchFields = null, emit }) {
	const selectedItems = ref([]);
	const open = ref(false);
	const searchTerm = ref("");

	// Use searchFields if provided, otherwise default to displayField
	const fieldsToSearch = searchFields || [displayField];

	// Initialize selected items based on modelValue and singleSelect mode
	const initializeSelectedItems = (value) => {
		if (singleSelect) {
			if (value && Object.keys(value).length > 0) {
				selectedItems.value = [value];
			} else {
				selectedItems.value = [];
			}
		} else {
			selectedItems.value = Array.isArray(value) ? [...value] : [];
		}
	};

	// Filter items based on search term and exclude already selected items
	const filteredItems = computed(() => {
		if (!items.value) return [];

		return items.value.filter((item) => {
			// Check if item matches search term in any of the specified fields
			const matchesSearch = fieldsToSearch.some((field) => item[field]?.toLowerCase().includes(searchTerm.value.toLowerCase()));

			// Check if item is not already selected
			const notSelected = !selectedItems.value.some((selectedItem) => selectedItem.id === item.id);

			// For single select mode, also exclude the currently selected item
			if (singleSelect && selectedItems.value.length > 0) {
				const notCurrentlySelected = selectedItems.value[0].id !== item.id;
				return matchesSearch && (selectedItems.value.length === 0 || notCurrentlySelected);
			}

			return matchesSearch && notSelected;
		});
	});

	// Handle item selection
	const selectItem = (item) => {
		if (singleSelect) {
			selectedItems.value = [item];
			emit("update:modelValue", item);
		} else {
			selectedItems.value.push(item);
			emit("update:modelValue", [...selectedItems.value]);
		}

		open.value = false;
		searchTerm.value = "";
	};

	// Handle item deletion
	const deleteItem = (itemId) => {
		if (singleSelect) {
			selectedItems.value = [];
			emit("update:modelValue", {});
		} else {
			const itemIndex = selectedItems.value.findIndex((item) => item.id === itemId);
			if (itemIndex !== -1) {
				selectedItems.value.splice(itemIndex, 1);
			}
			emit("update:modelValue", [...selectedItems.value]);
		}
	};

	// Watch for changes to modelValue prop
	watch(
		() => modelValue,
		(newValue) => {
			initializeSelectedItems(newValue);
		}
	);

	watch(
		() => items.value,
		(newItems) => {
			// Re-initialize when items change (e.g., when API data loads)
			if (newItems && newItems.length > 0) {
				initializeSelectedItems(modelValue);
			}
		},
		{ deep: true }
	);

	// Initialize on mount
	onMounted(() => {
		initializeSelectedItems(modelValue);
	});

	return {
		selectedItems,
		open,
		searchTerm,
		filteredItems,
		selectItem,
		deleteItem,
	};
}
