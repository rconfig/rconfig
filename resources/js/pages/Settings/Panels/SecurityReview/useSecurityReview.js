import { computed, onBeforeUnmount, reactive, ref } from "vue";
import axios from "axios";
import { countStatuses, defaultFilters, filterChecks, groupOptions as buildGroupOptions } from "./securityReview";

const POLL_MS = 2000;
const POLL_LIMIT = 300;

/**
 * State and actions for Settings > Security Center. Runs are queued on the server;
 * this polls the task until it is done, then reloads the stored report.
 */
export function useSecurityReview() {
	const isLoading = ref(true);
	const loadError = ref("");
	const data = ref(null);
	const filters = reactive(defaultFilters());
	const task = ref(null);
	const taskError = ref("");
	const notifySaving = ref(false);
	const notifyError = ref("");
	let pollTimer = null;
	let unmounted = false;

	const report = computed(() => data.value?.report ?? null);
	const checks = computed(() => report.value?.checks ?? []);
	const counts = computed(() => countStatuses(checks.value));
	const filtered = computed(() => filterChecks(checks.value, filters));
	const groupOptions = computed(() => buildGroupOptions(checks.value));
	const isRunning = computed(() => task.value !== null && ["queued", "running"].includes(task.value.status));

	async function load() {
		try {
			loadError.value = "";
			const response = await axios.get("/api/settings/security-review");
			data.value = response.data;

			if (response.data?.running_task && task.value === null) {
				watchTask(response.data.running_task);
			}
		} catch {
			loadError.value = "The Security Center could not be loaded. Refresh to try again.";
		} finally {
			isLoading.value = false;
		}
	}

	function stopPolling() {
		if (pollTimer) clearTimeout(pollTimer);
		pollTimer = null;
	}

	/**
	 * Polls a queued run. Resolves with the finished task, or null on failure or timeout.
	 */
	function watchTask(token) {
		stopPolling();
		task.value = { token, status: "queued" };
		taskError.value = "";

		return new Promise((resolve) => {
			let attempts = 0;

			const tick = async () => {
				if (unmounted) return resolve(null);
				attempts++;

				try {
					const response = await axios.get(`/api/settings/security-review/tasks/${token}`);
					task.value = response.data;

					if (response.data.status === "done") {
						await load();
						task.value = null;
						return resolve(response.data);
					}

					if (response.data.status === "failed") {
						taskError.value = response.data.error || "The checks did not finish.";
						task.value = null;
						return resolve(null);
					}
				} catch {
					if (attempts > 3) {
						taskError.value = "Lost track of the run. Refresh the page to see the latest results.";
						task.value = null;
						return resolve(null);
					}
				}

				if (attempts >= POLL_LIMIT) {
					taskError.value = "This is taking longer than expected. Check that Horizon is running, then refresh.";
					task.value = null;
					return resolve(null);
				}

				pollTimer = setTimeout(tick, POLL_MS);
			};

			pollTimer = setTimeout(tick, 500);
		});
	}

	async function runChecks() {
		if (isRunning.value) return null;

		taskError.value = "";

		try {
			const response = await axios.post("/api/settings/security-review/run");
			return watchTask(response.data.token);
		} catch (error) {
			taskError.value = error.response?.status === 429 ? "Checks were run a moment ago. Wait a minute, then try again." : "The checks could not be started.";
			return null;
		}
	}

	async function setNotify(enabled) {
		const previous = data.value?.notify;
		notifySaving.value = true;
		notifyError.value = "";

		if (data.value) data.value.notify = enabled;

		try {
			const response = await axios.put("/api/settings/security-review/notify", { enabled });
			if (data.value) data.value.notify = response.data.notify;
		} catch {
			if (data.value) data.value.notify = previous;
			notifyError.value = "The email setting could not be saved.";
		} finally {
			notifySaving.value = false;
		}
	}

	function resetFilters() {
		Object.assign(filters, defaultFilters());
	}

	onBeforeUnmount(() => {
		unmounted = true;
		stopPolling();
	});

	return {
		isLoading,
		loadError,
		data,
		report,
		checks,
		counts,
		filtered,
		groupOptions,
		filters,
		task,
		taskError,
		isRunning,
		notifySaving,
		notifyError,
		load,
		runChecks,
		setNotify,
		resetFilters,
	};
}
