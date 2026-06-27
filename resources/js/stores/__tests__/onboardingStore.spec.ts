import { beforeEach, describe, expect, it, vi } from "vitest";
import { setActivePinia, createPinia } from "pinia";

const { get, post } = vi.hoisted(() => ({ get: vi.fn(), post: vi.fn() }));
const { toastSuccess, toastError } = vi.hoisted(() => ({
	toastSuccess: vi.fn(),
	toastError: vi.fn(),
}));
const { celebrateStepCompletion, celebrateWithFireworks } = vi.hoisted(() => ({
	celebrateStepCompletion: vi.fn(),
	celebrateWithFireworks: vi.fn(),
}));

vi.mock("axios", () => ({
	default: { get, post },
}));

vi.mock("@/composables/useToaster", () => ({
	useToaster: () => ({ toastSuccess, toastError }),
}));

vi.mock("@/composables/useConfetti", () => ({
	useConfetti: () => ({ celebrateStepCompletion, celebrateWithFireworks }),
}));

// @ts-ignore — store is JS, no type declarations
import { useOnboardingStore } from "../onboardingStore";

describe("useOnboardingStore", () => {
	beforeEach(() => {
		get.mockReset();
		post.mockReset();
		toastSuccess.mockClear();
		toastError.mockClear();
		celebrateStepCompletion.mockClear();
		celebrateWithFireworks.mockClear();
		localStorage.clear();
		setActivePinia(createPinia());
	});

	it("starts with empty state defaults", () => {
		const store = useOnboardingStore();
		expect(store.steps).toEqual({});
		expect(store.isLoading).toBe(false);
		expect(store.isOnboardingCompleted).toBe(false);
	});

	describe("getters", () => {
		it("hasSteps reflects whether any steps exist", () => {
			const store = useOnboardingStore();
			expect(store.hasSteps).toBe(false);
			store.setSteps({ a: { status: false } });
			expect(store.hasSteps).toBe(true);
		});

		it("completedPercentage returns 0 when there are no steps", () => {
			const store = useOnboardingStore();
			expect(store.completedPercentage).toBe(0);
		});

		it("completedPercentage rounds completed steps to a percentage", () => {
			const store = useOnboardingStore();
			store.setSteps({
				a: { status: true },
				b: { status: false },
			});
			// 1 of 2 completed -> round(0.5 * 20) * 5 = 50
			expect(store.completedPercentage).toBe(50);
		});
	});

	it("setSteps replaces the steps object", () => {
		const store = useOnboardingStore();
		store.setSteps({ x: { status: true } });
		expect(store.steps).toEqual({ x: { status: true } });
	});

	describe("fetchSteps", () => {
		it("short-circuits when onboarding is already completed locally", async () => {
			localStorage.setItem("onboarding_is_completed", "true");
			localStorage.setItem("onboarding_last_check", Date.now().toString());
			const store = useOnboardingStore();

			await store.fetchSteps();

			expect(get).not.toHaveBeenCalled();
			expect(store.isOnboardingCompleted).toBe(true);
			expect(store.steps).toEqual({});
		});

		it("marks completion and celebrates when the server returns no steps", async () => {
			get.mockResolvedValue({ data: {} });
			const store = useOnboardingStore();

			await store.fetchSteps();

			expect(store.isOnboardingCompleted).toBe(true);
			expect(localStorage.getItem("onboarding_is_completed")).toBe("true");
			expect(store.isLoading).toBe(false);
		});

		it("merges locally completed steps into the server response", async () => {
			localStorage.setItem("onboarding_completed_steps", JSON.stringify(["addDevice"]));
			get.mockResolvedValue({
				data: {
					addDevice: { status: false },
					addVendor: { status: false },
				},
			});
			const store = useOnboardingStore();

			await store.fetchSteps();

			expect(store.steps.addDevice.status).toBe(true);
			expect(store.steps.addVendor.status).toBe(false);
			expect(store.isOnboardingCompleted).toBe(false);
		});

		it("falls back to completed state from localStorage when the request fails", async () => {
			localStorage.setItem("onboarding_is_completed", "true");
			get.mockRejectedValue(new Error("offline"));
			const store = useOnboardingStore();

			await store.fetchSteps();

			expect(store.isOnboardingCompleted).toBe(true);
			expect(store.steps).toEqual({});
			expect(store.isLoading).toBe(false);
		});
	});

	describe("completeStep", () => {
		it("posts the step, persists it and updates state on success", async () => {
			post.mockResolvedValue({ data: { onboarding_is_completed: false } });
			const store = useOnboardingStore();
			store.setSteps({ addDevice: { status: false } });

			const data = await store.completeStep("addDevice");

			expect(post).toHaveBeenCalledWith("/api/onboarding/complete-step", { step: "addDevice" });
			expect(store.steps.addDevice.status).toBe(true);
			expect(celebrateStepCompletion).toHaveBeenCalled();
			expect(toastSuccess).toHaveBeenCalled();
			const stored = JSON.parse(localStorage.getItem("onboarding_completed_steps") || "[]");
			expect(stored).toContain("addDevice");
			expect(data).toEqual({ onboarding_is_completed: false });
		});

		it("skips when the step is already completed locally", async () => {
			localStorage.setItem("onboarding_completed_steps", JSON.stringify(["addDevice"]));
			const store = useOnboardingStore();

			await store.completeStep("addDevice");

			expect(post).not.toHaveBeenCalled();
		});

		it("skips when the step is already completed in state", async () => {
			const store = useOnboardingStore();
			store.setSteps({ addDevice: { status: true } });

			await store.completeStep("addDevice");

			expect(post).not.toHaveBeenCalled();
		});

		it("marks onboarding completed when the server says all steps are done", async () => {
			post.mockResolvedValue({ data: { onboarding_is_completed: true } });
			const store = useOnboardingStore();
			store.setSteps({ addDevice: { status: false } });

			await store.completeStep("addDevice");

			expect(store.isOnboardingCompleted).toBe(true);
			expect(localStorage.getItem("onboarding_is_completed")).toBe("true");
		});

		it("toasts, still persists locally and rethrows on failure", async () => {
			post.mockRejectedValue(new Error("server down"));
			const store = useOnboardingStore();
			store.setSteps({ addDevice: { status: false } });

			await expect(store.completeStep("addDevice")).rejects.toThrow("server down");
			expect(toastError).toHaveBeenCalled();
			expect(store.steps.addDevice.status).toBe(true);
			const stored = JSON.parse(localStorage.getItem("onboarding_completed_steps") || "[]");
			expect(stored).toContain("addDevice");
		});
	});

	describe("clearOnboardingData", () => {
		it("removes localStorage keys and resets state", () => {
			localStorage.setItem("onboarding_completed_steps", "[]");
			localStorage.setItem("onboarding_is_completed", "true");
			localStorage.setItem("onboarding_last_check", "123");
			const store = useOnboardingStore();
			store.isOnboardingCompleted = true;
			store.setSteps({ a: { status: true } });

			store.clearOnboardingData();

			expect(localStorage.getItem("onboarding_completed_steps")).toBeNull();
			expect(localStorage.getItem("onboarding_is_completed")).toBeNull();
			expect(localStorage.getItem("onboarding_last_check")).toBeNull();
			expect(store.isOnboardingCompleted).toBe(false);
			expect(store.steps).toEqual({});
		});
	});

	describe("getOnboardingStatus", () => {
		it("reports a snapshot of local and current state", () => {
			localStorage.setItem("onboarding_completed_steps", JSON.stringify(["a"]));
			localStorage.setItem("onboarding_is_completed", "true");
			const store = useOnboardingStore();
			store.setSteps({ a: { status: true } });

			const status = store.getOnboardingStatus();

			expect(status.localCompleted).toEqual(["a"]);
			expect(status.isCompleted).toBe(true);
			expect(status.currentSteps).toEqual({ a: { status: true } });
		});
	});
});
