import { ref } from 'vue';

export function useRowSelection(rows) {
  const selectedRows = ref([]);
  const selectAll = ref(false);

  // Toggle Select All
  const toggleSelectAll = () => {
    selectAll.value = !selectAll.value;
    if (selectAll.value) {
      // Select all rows
      selectedRows.value = rows.value.data.map(row => row.id);
    } else {
      // Deselect all rows
      selectedRows.value = [];
    }
  };

  // Toggle Select Single Row
  const toggleSelectRow = rowId => {
    if (selectedRows.value.includes(rowId)) {
      selectedRows.value = selectedRows.value.filter(id => id !== rowId);
    } else {
      selectedRows.value.push(rowId);
    }
  };

  return {
    selectedRows,
    selectAll,
    toggleSelectAll,
    toggleSelectRow
  };
}
