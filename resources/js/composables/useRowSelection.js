import { ref } from "vue";

export function useRowSelection(rows) {
	const selectedRows = ref([]);
	const selectAll = ref(false);

	const getRowsArray = () => {
		if (Array.isArray(rows.value)) {
			return rows.value;
		}
		if (rows.value && Array.isArray(rows.value.data)) {
			return rows.value.data;
		}
		return [];
	};

	// Toggle Select All
	const toggleSelectAll = () => {
		selectAll.value = !selectAll.value;
		if (selectAll.value) {
			// Select all rows
			selectedRows.value = getRowsArray()
				.map((row) => row.id);
		} else {
			// Deselect all rows
			selectedRows.value = [];
		}
	};

	// Toggle Select Single Row
	function toggleSelectRow(rowId) {

		if (selectedRows.value.includes(rowId)) {
			selectedRows.value = selectedRows.value.filter((id) => id !== rowId);
		} else {
			selectedRows.value.push(rowId);
		}
	}

	// Toggle Select Single Row (Single Select Version)
	function toggleSingleSelectRow(rowId) {
		if (selectedRows.value.includes(rowId)) {
			selectedRows.value = [];
		} else {
			selectedRows.value = [rowId];
		}
	}

	return {
		selectedRows,
		selectAll,
		toggleSelectAll,
		toggleSelectRow, // Add this
		toggleSingleSelectRow,
	};
}
