import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";

const copy = vi.fn();

// useClipboard is provided by @vueuse/core; stub it so the composable has no
// real browser clipboard dependency.
vi.mock("@vueuse/core", () => ({
	useClipboard: () => ({
		text: { value: "" },
		copy,
		copied: { value: false },
		isSupported: { value: true },
	}),
}));

// @ts-ignore — composable is JS, no type declarations
import { useCopy } from "../useCopy";

describe("useCopy", () => {
	beforeEach(() => {
		vi.useFakeTimers();
		copy.mockClear();
	});

	afterEach(() => {
		vi.useRealTimers();
	});

	it("copies the value through the clipboard helper", async () => {
		const { copyItem } = useCopy();

		await copyItem("path", "show running-config");

		expect(copy).toHaveBeenCalledWith("show running-config");
	});

	it("flags the active icon then clears it after the timeout", async () => {
		const { copyItem, activeCopyIcon } = useCopy();

		await copyItem("row-1", "value");
		expect(activeCopyIcon.value["row-1"]).toBe(true);

		vi.advanceTimersByTime(1500);
		expect(activeCopyIcon.value["row-1"]).toBe(false);
	});

	it("tracks icon state independently per key", async () => {
		const { copyItem, activeCopyIcon } = useCopy();

		await copyItem("a", "1");
		await copyItem("b", "2");

		expect(activeCopyIcon.value["a"]).toBe(true);
		expect(activeCopyIcon.value["b"]).toBe(true);
	});
});
