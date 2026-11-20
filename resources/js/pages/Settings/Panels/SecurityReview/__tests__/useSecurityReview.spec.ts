import { mount } from "@vue/test-utils";
import axios from "axios";
import { defineComponent, nextTick } from "vue";
import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
// @ts-ignore — composable is JS, no type declarations
import { useSecurityReview } from "../useSecurityReview";

vi.mock("axios");

interface ReviewPayload {
	report: { checks: { id: string; group: string; status: string; severity: string; title: string; message: string }[] } | null;
	run: unknown;
	schedule: { enabled: boolean; time: string };
	notify: boolean;
	advisories_url: string;
	running_task: string | null;
}

function payload(overrides: Partial<ReviewPayload> = {}): { data: ReviewPayload } {
	return {
		data: {
			report: null,
			run: null,
			schedule: { enabled: true, time: "04:15" },
			notify: true,
			advisories_url: "https://www.rconfig.com/security-advisories",
			running_task: null,
			...overrides,
		},
	};
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
function mountReview(): any {
	let review: unknown;
	mount(defineComponent({
		setup() {
			review = useSecurityReview();
			return () => null;
		},
	}));
	return review;
}

describe("useSecurityReview", () => {
	beforeEach(() => {
		vi.useFakeTimers();
		vi.mocked(axios.get).mockReset();
		vi.mocked(axios.post).mockReset();
		vi.mocked(axios.put).mockReset();
	});

	afterEach(() => {
		vi.useRealTimers();
	});

	it("queues a run, polls the task and reloads the report when it is done", async () => {
		const done = payload({ report: { checks: [{ id: "app.debug", group: "app", status: "fail", severity: "critical", title: "Debug", message: "On" }] } });
		vi.mocked(axios.get)
			.mockResolvedValueOnce(payload())
			.mockResolvedValueOnce({ data: { token: "t1", status: "running" } })
			.mockResolvedValueOnce({ data: { token: "t1", status: "done", result: { summary: { fail: 1 } } } })
			.mockResolvedValueOnce(done);
		vi.mocked(axios.post).mockResolvedValueOnce({ data: { token: "t1", status: "queued" } });

		const review = mountReview();
		await review.load();

		const finished = review.runChecks();
		await nextTick();
		expect(review.isRunning.value).toBe(true);
		expect(review.task.value.status).toBe("queued");

		await vi.advanceTimersByTimeAsync(500);
		expect(review.task.value.status).toBe("running");

		await vi.advanceTimersByTimeAsync(2000);
		await finished;

		expect(review.isRunning.value).toBe(false);
		expect(review.counts.value.fail).toBe(1);
		expect(vi.mocked(axios.post)).toHaveBeenCalledWith("/api/settings/security-review/run");
	});

	it("shows a friendly error when the run is rate limited", async () => {
		vi.mocked(axios.get).mockResolvedValueOnce(payload());
		vi.mocked(axios.post).mockRejectedValueOnce({ response: { status: 429 } });

		const review = mountReview();
		await review.load();
		await review.runChecks();

		expect(review.taskError.value).toContain("Wait a minute");
		expect(review.isRunning.value).toBe(false);
	});

	it("saves the email switch and rolls back when saving fails", async () => {
		vi.mocked(axios.get).mockResolvedValueOnce(payload());
		vi.mocked(axios.put).mockResolvedValueOnce({ data: { notify: false } }).mockRejectedValueOnce(new Error("offline"));

		const review = mountReview();
		await review.load();

		await review.setNotify(false);
		expect(review.data.value.notify).toBe(false);
		expect(vi.mocked(axios.put)).toHaveBeenCalledWith("/api/settings/security-review/notify", { enabled: false });

		await review.setNotify(true);
		expect(review.data.value.notify).toBe(false);
		expect(review.notifyError.value).toContain("could not be saved");
	});
});
