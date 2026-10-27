import { flushPromises, mount } from "@vue/test-utils";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { defineComponent, nextTick } from "vue";

vi.mock("axios", () => ({
	default: { get: vi.fn() },
}));

const toastError = vi.fn();
vi.mock("@/composables/useToaster", () => ({
	useToaster: () => ({ toastError }),
}));

import axios from "axios";
import { useCompareFilterCard } from "../useCompareFilterCard";

type CompareFilterCard = ReturnType<typeof useCompareFilterCard>;

interface CommandRow {
	command: string;
}

interface SelectedDevice {
	id: number;
	device_name: string;
}

/** Deferred promise so a test can hold one request open and inspect the in-flight state. */
const defer = <T,>() => {
	let resolve!: (value: T) => void;
	let reject!: (reason?: unknown) => void;
	const promise = new Promise<T>((res, rej) => {
		resolve = res;
		reject = rej;
	});
	return { promise, resolve, reject };
};

const commandsBody = (commands: string[]): { data: { data: CommandRow[] } } => ({
	data: { data: commands.map((command) => ({ command })) },
});

const device = (id: number): SelectedDevice => ({ id, device_name: `device-${id}` });

const COMMANDS_BY_DEVICE: Record<number, string[]> = {
	7: ["show run", "show version"],
	9: ["show full-configuration", "get system status"],
};

const mockCommandsByDevice = () => {
	vi.mocked(axios.get).mockImplementation((url: string) => {
		const match = url.match(/^\/api\/configs\/distinct-commands\/(\d+)$/);
		if (match && COMMANDS_BY_DEVICE[Number(match[1])]) {
			return Promise.resolve(commandsBody(COMMANDS_BY_DEVICE[Number(match[1])]));
		}
		return Promise.reject(new Error(`unexpected url ${url}`));
	});
};

// Mount the composable inside a throwaway component so lifecycle hooks have a context.
const mountFilterCard = () => {
	let card!: CompareFilterCard;
	const emit = vi.fn();
	const wrapper = mount(
		defineComponent({
			setup() {
				card = useCompareFilterCard(emit);
				return () => null;
			},
		}),
	);
	return { wrapper, card, emit };
};

const commandNames = (card: CompareFilterCard) => card.commands.value.map((row: CommandRow) => row.command);

describe("useCompareFilterCard", () => {
	beforeEach(() => {
		vi.mocked(axios.get).mockReset();
		toastError.mockReset();
	});

	it("does not load every device's commands before a device is selected", async () => {
		mockCommandsByDevice();

		const { card } = mountFilterCard();
		await flushPromises();

		expect(axios.get).not.toHaveBeenCalled();
		expect(card.commands.value).toEqual([]);
		expect(card.hasDevice.value).toBe(false);
	});

	it("loads only the selected device's commands", async () => {
		mockCommandsByDevice();
		const { card } = mountFilterCard();

		card.model.value.device = device(7);
		await flushPromises();

		expect(axios.get).toHaveBeenCalledWith("/api/configs/distinct-commands/7");
		expect(commandNames(card)).toEqual(["show run", "show version"]);
		expect(card.hasDevice.value).toBe(true);
	});

	it("clears the selected command and reloads when the device changes", async () => {
		mockCommandsByDevice();
		const { card } = mountFilterCard();

		card.model.value.device = device(7);
		await flushPromises();
		card.model.value.selectedCommand = "show run";

		card.model.value.device = device(9);
		await flushPromises();

		expect(card.model.value.selectedCommand).toBe("");
		expect(axios.get).toHaveBeenLastCalledWith("/api/configs/distinct-commands/9");
		expect(commandNames(card)).toEqual(["show full-configuration", "get system status"]);
	});

	it("empties the command list without calling the API when the device is cleared", async () => {
		mockCommandsByDevice();
		const { card } = mountFilterCard();

		card.model.value.device = device(7);
		await flushPromises();
		card.model.value.selectedCommand = "show run";
		vi.mocked(axios.get).mockClear();

		card.model.value.device = {};
		await flushPromises();

		expect(axios.get).not.toHaveBeenCalled();
		expect(card.commands.value).toEqual([]);
		expect(card.model.value.selectedCommand).toBe("");
		expect(card.hasDevice.value).toBe(false);
	});

	it("flags loading while the device's commands are in flight", async () => {
		const pending = defer<ReturnType<typeof commandsBody>>();
		vi.mocked(axios.get).mockReturnValue(pending.promise);
		const { card } = mountFilterCard();

		card.model.value.device = device(7);
		await nextTick();

		expect(card.isLoadingCommands.value).toBe(true);

		pending.resolve(commandsBody(["show run"]));
		await flushPromises();

		expect(card.isLoadingCommands.value).toBe(false);
		expect(commandNames(card)).toEqual(["show run"]);
	});

	it("ignores a slow response for a device that is no longer selected", async () => {
		const slowFirstDevice = defer<ReturnType<typeof commandsBody>>();
		vi.mocked(axios.get).mockImplementation((url: string) => {
			if (url === "/api/configs/distinct-commands/7") return slowFirstDevice.promise;
			if (url === "/api/configs/distinct-commands/9") return Promise.resolve(commandsBody(COMMANDS_BY_DEVICE[9]));
			return Promise.reject(new Error(`unexpected url ${url}`));
		});
		const { card } = mountFilterCard();

		card.model.value.device = device(7);
		await nextTick();
		card.model.value.device = device(9);
		await flushPromises();

		slowFirstDevice.resolve(commandsBody(COMMANDS_BY_DEVICE[7]));
		await flushPromises();

		expect(commandNames(card)).toEqual(["show full-configuration", "get system status"]);
		expect(card.isLoadingCommands.value).toBe(false);
	});

	it("resets the command list when the filters are cleared", async () => {
		mockCommandsByDevice();
		const { card, emit } = mountFilterCard();

		card.model.value.device = device(7);
		await flushPromises();

		card.clearAll();
		await flushPromises();

		expect(card.commands.value).toEqual([]);
		expect(card.model.value.selectedCommand).toBe("");
		expect(emit).toHaveBeenCalledWith("updateConfigFilter", card.model.value);
	});

	it("toasts and stops loading when the commands request fails", async () => {
		vi.mocked(axios.get).mockRejectedValue({ response: { data: { message: "Server error" } } });
		const { card } = mountFilterCard();

		card.model.value.device = device(7);
		await flushPromises();

		expect(toastError).toHaveBeenCalledWith("Error", "Server error");
		expect(card.isLoadingCommands.value).toBe(false);
		expect(card.commands.value).toEqual([]);
	});

	it("keeps the left and right panels' command lists independent", async () => {
		mockCommandsByDevice();
		const { card: left } = mountFilterCard();
		const { card: right } = mountFilterCard();

		left.model.value.device = device(7);
		right.model.value.device = device(9);
		await flushPromises();

		expect(commandNames(left)).toEqual(["show run", "show version"]);
		expect(commandNames(right)).toEqual(["show full-configuration", "get system status"]);
	});
});
