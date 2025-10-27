import { mount } from "@vue/test-utils";
import { defineComponent, ref, nextTick } from "vue";
import { describe, it, expect, vi, beforeEach } from "vitest";
import { useMultiSelect } from "../useMultiSelect";

function mountComposable(fn: () => void) {
	mount(defineComponent({ setup: fn, template: "<div />" }));
}

describe("useMultiSelect", () => {
	let items = ref([]);
	let emit = vi.fn();
	let result: any;

	beforeEach(() => {
		items = ref([
			{ id: 1, name: "Cisco" },
			{ id: 2, name: "Juniper" },
			{ id: 3, name: "Fortinet" },
		]);
		emit = vi.fn();
		result = null;
	});

	it("initializes selectedItems (multi-select)", async () => {
		const modelValue = ref([{ id: 2, name: "Juniper" }]);

		mountComposable(() => {
			result = useMultiSelect({
				items,
				modelValue: modelValue.value,
				singleSelect: false,
				displayField: "name",
				emit,
			});
		});

		await nextTick();
		expect(result.selectedItems.value).toEqual([{ id: 2, name: "Juniper" }]);
	});

	it("filters items by searchTerm", async () => {
		const modelValue = ref([]);

		mountComposable(() => {
			result = useMultiSelect({
				items,
				modelValue: modelValue.value,
				singleSelect: false,
				displayField: "name",
				emit,
			});
		});

		result.searchTerm.value = "fort";
		await nextTick();

		expect(result.filteredItems.value).toEqual([{ id: 3, name: "Fortinet" }]);
	});

	it("selects item in single-select mode", async () => {
		const modelValue = ref({});
		mountComposable(() => {
			result = useMultiSelect({
				items,
				modelValue: modelValue.value,
				singleSelect: true,
				displayField: "name",
				emit,
			});
		});

		result.selectItem(items.value[0]);
		expect(result.selectedItems.value).toEqual([items.value[0]]);
		expect(emit).toHaveBeenCalledWith("update:modelValue", items.value[0]);
	});

	it("selects multiple items in multi-select mode", async () => {
		const modelValue = ref([]);
		mountComposable(() => {
			result = useMultiSelect({
				items,
				modelValue: modelValue.value,
				singleSelect: false,
				displayField: "name",
				emit,
			});
		});

		result.selectItem(items.value[0]);
		result.selectItem(items.value[1]);

		expect(result.selectedItems.value).toEqual([
			items.value[0],
			items.value[1],
		]);
		expect(emit).toHaveBeenCalledTimes(2);
		expect(emit).toHaveBeenLastCalledWith("update:modelValue", [
			items.value[0],
			items.value[1],
		]);
	});

	it("deletes item by ID", async () => {
		const modelValue = ref([]);
		mountComposable(() => {
			result = useMultiSelect({
				items,
				modelValue: modelValue.value,
				singleSelect: false,
				displayField: "name",
				emit,
			});
		});

		result.selectItem(items.value[0]);
		result.selectItem(items.value[1]);

		result.deleteItem(1); // delete Cisco

		expect(result.selectedItems.value).toEqual([items.value[1]]);
		expect(emit).toHaveBeenLastCalledWith("update:modelValue", [
			items.value[1],
		]);
	});
});
