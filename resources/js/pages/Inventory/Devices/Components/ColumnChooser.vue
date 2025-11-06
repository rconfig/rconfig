<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { Check, ChevronUp, ChevronDown, Columns } from "lucide-vue-next";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { Button } from "@/components/ui/button";
import { Switch } from "@/components/ui/switch";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";

const props = defineProps({
	columns: {
		type: Array,
		required: true,
	},
	modelValue: {
		type: Array,
		required: true,
	},
	storageKey: {
		type: String,
		required: true,
	},
});

const emit = defineEmits(["update:modelValue"]);

const isOpen = ref(false);
const selectedColumns = ref([...props.modelValue]);

// Watch for external changes to model value
watch(
	() => props.modelValue,
	(newValue) => {
		selectedColumns.value = [...newValue];
	},
	{ deep: true }
);

// Get all columns sorted by original order
const allColumns = computed(() => {
	// Sort columns by their original order
	return [...props.columns].sort((a, b) => {
		const indexA = selectedColumns.value.indexOf(a.key);
		const indexB = selectedColumns.value.indexOf(b.key);

		// Put selected columns first in their selected order
		if (indexA >= 0 && indexB >= 0) return indexA - indexB;
		if (indexA >= 0) return -1;
		if (indexB >= 0) return 1;

		// For unselected columns, use their original order
		const origIndexA = props.columns.findIndex((col) => col.key === a.key);
		const origIndexB = props.columns.findIndex((col) => col.key === b.key);
		return origIndexA - origIndexB;
	});
});

// Check if a column is selected
const isSelected = (columnKey) => {
	return selectedColumns.value.includes(columnKey);
};

// Save column selection to localStorage
function saveColumnsToStorage() {
	localStorage.setItem(props.storageKey, JSON.stringify(selectedColumns.value));
}

// Load column selection from localStorage
function loadColumnsFromStorage() {
	const saved = localStorage.getItem(props.storageKey);
	if (saved) {
		try {
			const parsed = JSON.parse(saved);
			// Validate that all columns still exist
			const validColumns = parsed.filter((col) => props.columns.some((availableCol) => availableCol.key === col));
			selectedColumns.value = validColumns;
			emit("update:modelValue", validColumns);
		} catch (e) {
			console.error("Error loading column preferences:", e);
		}
	}
}

// Toggle a column's visibility
function toggleColumn(columnKey) {
	if (selectedColumns.value.includes(columnKey)) {
		// Remove column if it exists
		selectedColumns.value = selectedColumns.value.filter((key) => key !== columnKey);
	} else {
		// Add column if it doesn't exist
		selectedColumns.value.push(columnKey);

		// Re-order selectedColumns to match the original column order
		selectedColumns.value.sort((a, b) => {
			const indexA = props.columns.findIndex((col) => col.key === a);
			const indexB = props.columns.findIndex((col) => col.key === b);
			return indexA - indexB;
		});
	}

	emit("update:modelValue", selectedColumns.value);
	saveColumnsToStorage();
}

// Move a column up in the visible list
function moveColumnUp(columnKey) {
	const index = selectedColumns.value.indexOf(columnKey);
	if (index > 0) {
		const newSelectedColumns = [...selectedColumns.value];
		const temp = newSelectedColumns[index];
		newSelectedColumns[index] = newSelectedColumns[index - 1];
		newSelectedColumns[index - 1] = temp;
		selectedColumns.value = newSelectedColumns;
		emit("update:modelValue", newSelectedColumns);
		saveColumnsToStorage();
	}
}

// Move a column down in the visible list
function moveColumnDown(columnKey) {
	const index = selectedColumns.value.indexOf(columnKey);
	if (index < selectedColumns.value.length - 1) {
		const newSelectedColumns = [...selectedColumns.value];
		const temp = newSelectedColumns[index];
		newSelectedColumns[index] = newSelectedColumns[index + 1];
		newSelectedColumns[index + 1] = temp;
		selectedColumns.value = newSelectedColumns;
		emit("update:modelValue", newSelectedColumns);
		saveColumnsToStorage();
	}
}

// Show all columns
function showAllColumns() {
	selectedColumns.value = props.columns.map((col) => col.key);
	emit("update:modelValue", selectedColumns.value);
	saveColumnsToStorage();
}

// Hide all columns (except mandatory ones)
function hideAllColumns() {
	const mandatoryColumns = props.columns.filter((col) => col.mandatory).map((col) => col.key);

	selectedColumns.value = mandatoryColumns;
	emit("update:modelValue", selectedColumns.value);
	saveColumnsToStorage();
}

// Reset to default columns
function resetToDefaultColumns() {
	const defaultColumns = props.columns.filter((col) => col.default).map((col) => col.key);

	selectedColumns.value = defaultColumns;
	emit("update:modelValue", selectedColumns.value);
	saveColumnsToStorage();
}

onMounted(() => {
	loadColumnsFromStorage();
});
</script>

<template>
	<DropdownMenu v-model:open="isOpen">
		<DropdownMenuTrigger as-child>
			<Button variant="ghost" size="sm" class="h-8 w-8 p-0 hidden xl:inline-flex" title="Column Options">
				<Columns class="h-4 w-4" />
				<span class="sr-only">Column Options</span>
			</Button>
		</DropdownMenuTrigger>

		<DropdownMenuContent align="end" class="w-60">
			<DropdownMenuLabel>Customize Columns</DropdownMenuLabel>
			<DropdownMenuSeparator />

			<div class="p-2">
				<div class="flex justify-between mb-2">
					<Button variant="outline" size="sm" class="text-xs" @click="showAllColumns">
						Show All
					</Button>
					<Button variant="outline" size="sm" class="text-xs" @click="hideAllColumns">
						Hide All
					</Button>
					<Button variant="outline" size="sm" class="text-xs" @click="resetToDefaultColumns">
						Reset
					</Button>
				</div>
			</div>

			<DropdownMenuSeparator />

			<ScrollArea class="h-[300px]">
				<div class="p-2">
					<div v-for="column in allColumns" :key="column.key" class="flex items-center justify-between py-1">
						<div class="flex items-center space-x-2">
							<Switch :id="`col-toggle-${column.key}`" :checked="isSelected(column.key)" :disabled="column.mandatory" @update:checked="toggleColumn(column.key)" />
							<label :for="`col-toggle-${column.key}`" class="text-sm cursor-pointer" :class="{ 'opacity-50': column.mandatory }">
								{{ column.label }}
								<span v-if="column.mandatory" class="text-xs text-muted-foreground ml-1">(Required)</span>
							</label>
						</div>
						<div class="flex items-center space-x-1" v-if="isSelected(column.key)">
							<Button variant="ghost" size="icon" class="h-6 w-6" @click="moveColumnUp(column.key)" :disabled="selectedColumns.indexOf(column.key) === 0">
								<ChevronUp class="h-3 w-3" />
							</Button>
							<Button variant="ghost" size="icon" class="h-6 w-6" @click="moveColumnDown(column.key)" :disabled="selectedColumns.indexOf(column.key) === selectedColumns.length - 1">
								<ChevronDown class="h-3 w-3" />
							</Button>
						</div>
					</div>
				</div>
			</ScrollArea>
		</DropdownMenuContent>
	</DropdownMenu>
</template>

<style scoped>
/* In ColumnChooser.vue */
.column-chooser-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 8px 12px;
	background-color: #f5f5f5;
	border-bottom: 1px solid #ddd;
	border-top-left-radius: 4px;
	border-top-right-radius: 4px;
}

.dark .column-chooser-header {
	background-color: #222;
	border-color: #444;
}

.column-item {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 6px 12px;
	border-bottom: 1px solid #eee;
	transition: background-color 0.2s;
}

.dark .column-item {
	border-color: #333;
}

.column-item:hover {
	background-color: #f9f9f9;
}

.dark .column-item:hover {
	background-color: #2a2a2a;
}

.column-item.mandatory {
	opacity: 0.7;
}

.column-actions {
	opacity: 0;
	transition: opacity 0.2s;
}

.column-item:hover .column-actions {
	opacity: 1;
}
</style>