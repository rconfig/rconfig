import { beforeEach, describe, expect, it, vi } from "vitest";

const { get } = vi.hoisted(() => ({ get: vi.fn() }));
vi.mock("axios", () => ({
	default: { get },
}));

const toastError = vi.fn();
const toastSuccess = vi.fn();
vi.mock("@/composables/useToaster", () => ({
	useToaster: () => ({ toastError, toastSuccess }),
}));

// @ts-ignore — composable is JS, no type declarations
import { useVersionCheck } from "../useVersionCheck";

const okPayload = (overrides = {}) => ({
	data: {
		data: {
			current_version: "8.2.3",
			latest_version: "8.3.0",
			update_available: true,
			latest_url: "https://github.com/rconfig/releases/8.3.0",
			reachable: true,
			checked: true,
			last_checked_at: "2026-06-27T10:00:00.000Z",
			consecutive_failures: 0,
			last_error: null,
			...overrides,
		},
	},
});

describe("useVersionCheck", () => {
	beforeEach(() => {
		get.mockReset();
		toastError.mockClear();
		toastSuccess.mockClear();
	});

	it("populates the shared state from the API response", async () => {
		get.mockResolvedValue(okPayload());
		const { fetchVersionStatus, currentVersion, latestVersion, updateAvailable, loading } = useVersionCheck();

		await fetchVersionStatus();

		expect(get).toHaveBeenCalledWith("/api/version-check");
		expect(currentVersion.value).toBe("8.2.3");
		expect(latestVersion.value).toBe("8.3.0");
		expect(updateAvailable.value).toBe(true);
		expect(loading.value).toBe(false);
	});

	it("stays silent on a background load", async () => {
		get.mockResolvedValue(okPayload());
		const { fetchVersionStatus } = useVersionCheck();

		await fetchVersionStatus();

		expect(toastSuccess).not.toHaveBeenCalled();
		expect(toastError).not.toHaveBeenCalled();
	});

	it("appends the cache-busting query and toasts on an explicit recheck", async () => {
		get.mockResolvedValue(okPayload());
		const { fetchVersionStatus } = useVersionCheck();

		await fetchVersionStatus(true);

		expect(get).toHaveBeenCalledWith("/api/version-check?clearCache=true");
		expect(toastSuccess).toHaveBeenCalledTimes(1);
	});

	it("toasts an up-to-date message when no update is available", async () => {
		get.mockResolvedValue(okPayload({ update_available: false }));
		const { fetchVersionStatus } = useVersionCheck();

		await fetchVersionStatus(true);

		expect(toastSuccess).toHaveBeenCalledWith("Up to date", "You are running the latest version.");
	});

	it("warns when GitHub is unreachable on an explicit recheck", async () => {
		get.mockResolvedValue(okPayload({ reachable: false, update_available: false }));
		const { fetchVersionStatus } = useVersionCheck();

		await fetchVersionStatus(true);

		expect(toastError).toHaveBeenCalledWith("Version check", "Couldn't reach GitHub to check for updates.");
	});

	it("handles a request failure by flagging unreachable and toasting", async () => {
		vi.spyOn(console, "error").mockImplementation(() => {});
		get.mockRejectedValue(new Error("network down"));
		const { fetchVersionStatus, reachable, loading } = useVersionCheck();

		await fetchVersionStatus(true);

		expect(reachable.value).toBe(false);
		expect(loading.value).toBe(false);
		expect(toastError).toHaveBeenCalledWith("Version check", "Failed to check for updates.");
	});
});
