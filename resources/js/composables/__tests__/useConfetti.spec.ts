import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";

const { confetti } = vi.hoisted(() => ({ confetti: vi.fn(() => Promise.resolve()) }));
vi.mock("canvas-confetti", () => ({
	default: confetti,
}));

// @ts-ignore — composable is JS, no type declarations
import { useConfetti } from "../useConfetti";

describe("useConfetti", () => {
	beforeEach(() => {
		confetti.mockClear();
	});

	afterEach(() => {
		vi.restoreAllMocks();
	});

	it("celebrateStepCompletion fires three bursts and resolves", async () => {
		const { celebrateStepCompletion } = useConfetti();

		await celebrateStepCompletion();

		expect(confetti).toHaveBeenCalledTimes(3);
	});

	it("celebrateWithFireworks fires immediately then on a schedule", () => {
		vi.useFakeTimers();
		const { celebrateWithFireworks } = useConfetti();

		celebrateWithFireworks();
		expect(confetti).toHaveBeenCalledTimes(1);

		vi.advanceTimersByTime(600);
		expect(confetti).toHaveBeenCalledTimes(5);

		vi.useRealTimers();
	});

	it("celebrateAllStepsComplete drives bursts via requestAnimationFrame", () => {
		vi.useFakeTimers();
		// Deterministic randomness: always inside the 70% fire chance.
		vi.spyOn(Math, "random").mockReturnValue(0.1);
		const rafSpy = vi.spyOn(globalThis, "requestAnimationFrame").mockImplementation((cb: FrameRequestCallback) => {
			return setTimeout(() => cb(0), 16) as unknown as number;
		});

		const { celebrateAllStepsComplete } = useConfetti();
		celebrateAllStepsComplete();

		// First synchronous frame fires once.
		expect(confetti).toHaveBeenCalledTimes(1);
		expect(rafSpy).toHaveBeenCalled();

		vi.useRealTimers();
	});
});
