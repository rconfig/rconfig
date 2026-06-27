import { describe, expect, it } from "vitest";
import { ref } from "vue";

// @ts-ignore — composable is JS, no type declarations
import { useRowSelection } from "../useRowSelection";

describe("useRowSelection", () => {
	const makeRows = () => ref([{ id: 1 }, { id: 2 }, { id: 3 }]);

	it("starts empty and unselected", () => {
		const { selectedRows, selectAll } = useRowSelection(makeRows());
		expect(selectedRows.value).toEqual([]);
		expect(selectAll.value).toBe(false);
	});

	it("toggleSelectAll selects every row id then clears them", () => {
		const { selectedRows, selectAll, toggleSelectAll } = useRowSelection(makeRows());

		toggleSelectAll();
		expect(selectAll.value).toBe(true);
		expect(selectedRows.value).toEqual([1, 2, 3]);

		toggleSelectAll();
		expect(selectAll.value).toBe(false);
		expect(selectedRows.value).toEqual([]);
	});

	it("reads rows from a paginated { data: [...] } shape", () => {
		const rows = ref({ data: [{ id: 7 }, { id: 8 }] });
		const { selectedRows, toggleSelectAll } = useRowSelection(rows);

		toggleSelectAll();
		expect(selectedRows.value).toEqual([7, 8]);
	});

	it("returns no ids for an unexpected rows shape", () => {
		const rows = ref(null);
		const { selectedRows, toggleSelectAll } = useRowSelection(rows);

		toggleSelectAll();
		expect(selectedRows.value).toEqual([]);
	});

	it("toggleSelectRow adds then removes a single id", () => {
		const { selectedRows, toggleSelectRow } = useRowSelection(makeRows());

		toggleSelectRow(2);
		expect(selectedRows.value).toEqual([2]);

		toggleSelectRow(2);
		expect(selectedRows.value).toEqual([]);
	});

	it("toggleSelectRow accumulates multiple ids", () => {
		const { selectedRows, toggleSelectRow } = useRowSelection(makeRows());

		toggleSelectRow(1);
		toggleSelectRow(3);
		expect(selectedRows.value).toEqual([1, 3]);
	});

	it("toggleSingleSelectRow keeps at most one id", () => {
		const { selectedRows, toggleSingleSelectRow } = useRowSelection(makeRows());

		toggleSingleSelectRow(1);
		expect(selectedRows.value).toEqual([1]);

		toggleSingleSelectRow(2);
		expect(selectedRows.value).toEqual([2]);

		toggleSingleSelectRow(2);
		expect(selectedRows.value).toEqual([]);
	});
});
