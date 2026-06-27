import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { nextTick, ref } from "vue";

// @ts-ignore — composable is JS, no type declarations
import { useToggleSort } from "../useToggleSort";

describe("useToggleSort", () => {
	beforeEach(() => {
		localStorage.clear();
	});

	afterEach(() => {
		vi.restoreAllMocks();
	});

	it("toggles a field between ascending and descending", () => {
		const sort = ref("device_name");
		const { toggleSort } = useToggleSort(sort, undefined, "devices");

		toggleSort("device_name");
		expect(sort.value).toBe("-device_name");

		toggleSort("device_name");
		expect(sort.value).toBe("device_name");
	});

	it("switches to a new field ascending when a different column is clicked", () => {
		const sort = ref("-device_name");
		const { toggleSort } = useToggleSort(sort, undefined, "devices");

		toggleSort("ip");
		expect(sort.value).toBe("ip");
	});

	it("calls the fetch callback on every toggle", () => {
		const sort = ref("id");
		const fetchFn = vi.fn();
		const { toggleSort } = useToggleSort(sort, fetchFn, "devices");

		toggleSort("id");
		expect(fetchFn).toHaveBeenCalledTimes(1);
	});

	it("persists the new sort to localStorage under a namespaced key", async () => {
		const sort = ref("id");
		const { toggleSort } = useToggleSort(sort, undefined, "devices");

		toggleSort("id");
		await nextTick();

		expect(localStorage.getItem("sort_devices")).toBe("-id");
	});

	it("restores a previously saved sort on initialisation", () => {
		localStorage.setItem("sort_reports", "-created_at");
		const sort = ref("id");

		useToggleSort(sort, undefined, "reports");

		expect(sort.value).toBe("-created_at");
	});
});
