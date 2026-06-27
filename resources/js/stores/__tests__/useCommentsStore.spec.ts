import { beforeEach, describe, expect, it, vi } from "vitest";
import { setActivePinia, createPinia } from "pinia";

const { get } = vi.hoisted(() => ({ get: vi.fn() }));

vi.mock("axios", () => ({
	default: { get },
}));

// @ts-ignore — store is JS, no type declarations
import { useCommentsStore } from "../useCommentsStore";

describe("useCommentsStore", () => {
	beforeEach(() => {
		get.mockReset();
		setActivePinia(createPinia());
	});

	it("starts with empty counters and initialized maps", () => {
		const store = useCommentsStore();
		expect(store.commentCounters).toEqual({});
		expect(store.initializedDevices).toEqual({});
	});

	it("initializeCommentsForDevice loads the comment count and marks the device", async () => {
		get.mockResolvedValue({ data: [{}, {}, {}] });
		const store = useCommentsStore();

		await store.initializeCommentsForDevice(5);

		expect(get).toHaveBeenCalledWith("/api/device-comments/5");
		expect(store.commentCounters[5]).toBe(3);
		expect(store.initializedDevices[5]).toBe(true);
	});

	it("initializeCommentsForDevice skips already-initialized devices", async () => {
		get.mockResolvedValue({ data: [{}] });
		const store = useCommentsStore();

		await store.initializeCommentsForDevice(5);
		await store.initializeCommentsForDevice(5);

		expect(get).toHaveBeenCalledTimes(1);
	});

	it("initializeCommentsForDevice leaves state untouched on failure", async () => {
		get.mockRejectedValue(new Error("network"));
		const store = useCommentsStore();

		await store.initializeCommentsForDevice(9);

		expect(store.commentCounters[9]).toBeUndefined();
		expect(store.initializedDevices[9]).toBeUndefined();
	});

	it("incrementCounter starts from zero and bumps the count", () => {
		const store = useCommentsStore();
		store.incrementCounter(2);
		expect(store.commentCounters[2]).toBe(1);
		store.incrementCounter(2);
		expect(store.commentCounters[2]).toBe(2);
	});

	it("decrementCounter lowers an existing count", () => {
		const store = useCommentsStore();
		store.commentCounters[3] = 2;
		store.decrementCounter(3);
		expect(store.commentCounters[3]).toBe(1);
	});

	it("decrementCounter does nothing when count is zero or missing", () => {
		const store = useCommentsStore();
		store.decrementCounter(4);
		expect(store.commentCounters[4]).toBeUndefined();
	});
});
