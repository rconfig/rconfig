import { describe, expect, it, vi } from "vitest";

// @ts-ignore — composable is JS, no type declarations
import { useReload } from "../useReload";

describe("useReload", () => {
	it("invokes a single fetch function", () => {
		const fn = vi.fn();
		const { reload } = useReload(fn);

		reload();

		expect(fn).toHaveBeenCalledTimes(1);
	});

	it("invokes every function in an array", () => {
		const a = vi.fn();
		const b = vi.fn();
		const { reload } = useReload([a, b]);

		reload();

		expect(a).toHaveBeenCalledTimes(1);
		expect(b).toHaveBeenCalledTimes(1);
	});

	it("skips non-function entries without throwing", () => {
		const fn = vi.fn();
		const { reload } = useReload([fn, null, undefined, "nope"]);

		expect(() => reload()).not.toThrow();
		expect(fn).toHaveBeenCalledTimes(1);
	});

	it("can be called repeatedly", () => {
		const fn = vi.fn();
		const { reload } = useReload(fn);

		reload();
		reload();

		expect(fn).toHaveBeenCalledTimes(2);
	});
});
