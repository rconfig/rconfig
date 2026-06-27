import { beforeEach, describe, expect, it, vi } from "vitest";

const toast = vi.fn();

// useToast is a shadcn-vue UI composable; stub it to capture the payloads.
vi.mock("@/components/ui/toast/use-toast", () => ({
	useToast: () => ({ toast }),
}));

// @ts-ignore — composable is JS, no type declarations
import { useToaster } from "../useToaster";

describe("useToaster", () => {
	beforeEach(() => {
		toast.mockClear();
	});

	describe("setTitle", () => {
		const { setTitle } = useToaster();

		it("moves a lone title into the description slot", () => {
			expect(setTitle("Just a message", undefined)).toEqual({
				title: null,
				description: "Just a message",
			});
		});

		it("keeps title and description when both are present", () => {
			expect(setTitle("Heading", "Body")).toEqual({
				title: "Heading",
				description: "Body",
			});
		});
	});

	it("toastSuccess uses the success variant", () => {
		const { toastSuccess } = useToaster();

		toastSuccess("Saved", "All good");

		expect(toast).toHaveBeenCalledTimes(1);
		const payload = toast.mock.calls[0][0];
		expect(payload.variant).toBe("success");
		expect(payload.title).toBe("Saved");
		expect(payload.description).toBe("All good");
	});

	it("toastError uses the destructive variant", () => {
		const { toastError } = useToaster();

		toastError("Boom", "It broke");

		expect(toast.mock.calls[0][0].variant).toBe("destructive");
	});

	it("toastWarning uses the warning variant", () => {
		const { toastWarning } = useToaster();

		toastWarning("Careful", "Heads up");

		expect(toast.mock.calls[0][0].variant).toBe("warning");
	});

	it("honours a custom duration", () => {
		const { toastInfo } = useToaster();

		toastInfo("Hi", "There", 8000);

		expect(toast.mock.calls[0][0].duration).toBe(8000);
	});

	it("collapses a title-only call into the description", () => {
		const { toastDefault } = useToaster();

		toastDefault("Only a title");

		const payload = toast.mock.calls[0][0];
		expect(payload.title).toBeNull();
		expect(payload.description).toBe("Only a title");
	});
});
