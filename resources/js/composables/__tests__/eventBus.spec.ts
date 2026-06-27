import { describe, expect, it, vi } from "vitest";

// @ts-ignore — composable is JS, no type declarations
import { eventBus } from "../eventBus";

describe("eventBus", () => {
	it("exposes the mitt emitter surface", () => {
		expect(typeof eventBus.on).toBe("function");
		expect(typeof eventBus.off).toBe("function");
		expect(typeof eventBus.emit).toBe("function");
	});

	it("delivers an emitted event with its payload to subscribers", () => {
		const handler = vi.fn();
		eventBus.on("device:updated", handler);

		eventBus.emit("device:updated", { id: 42 });

		expect(handler).toHaveBeenCalledTimes(1);
		expect(handler).toHaveBeenCalledWith({ id: 42 });

		eventBus.off("device:updated", handler);
	});

	it("stops delivering after a handler is removed", () => {
		const handler = vi.fn();
		eventBus.on("ping", handler);
		eventBus.off("ping", handler);

		eventBus.emit("ping");

		expect(handler).not.toHaveBeenCalled();
	});
});
