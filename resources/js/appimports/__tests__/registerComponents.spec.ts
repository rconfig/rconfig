import { describe, expect, it, vi } from "vitest";

// @ts-ignore — JS module, no type declarations
import { registerComponents } from "../registerComponents";

describe("registerComponents", () => {
	it("registers each entry on the app under its key", () => {
		const component = vi.fn();
		const app = { component };

		const Foo = { name: "Foo" };
		const Bar = { name: "Bar" };

		registerComponents(app, { Foo, Bar });

		expect(component).toHaveBeenCalledTimes(2);
		expect(component).toHaveBeenCalledWith("Foo", Foo);
		expect(component).toHaveBeenCalledWith("Bar", Bar);
	});

	it("does nothing for an empty component map", () => {
		const component = vi.fn();

		registerComponents({ component }, {});

		expect(component).not.toHaveBeenCalled();
	});
});
