import { describe, expect, it } from "vitest";
import {
	compareChecks,
	countStatuses,
	defaultFilters,
	filterChecks,
	formatEvidence,
	groupOptions,
	relativeAge,
	runLabel,
	statusLabel,
	toggleStatus,
	type SecurityCheckRow,
} from "../securityReview";

function row(overrides: Partial<SecurityCheckRow>): SecurityCheckRow {
	return {
		id: "app.debug",
		group: "app",
		group_label: "Application",
		title: "Debug mode is off",
		severity: "critical",
		status: "pass",
		message: "APP_DEBUG is off.",
		...overrides,
	};
}

const rows: SecurityCheckRow[] = [
	row({ id: "app.debug", status: "pass" }),
	row({ id: "redis.auth", group: "redis", group_label: "Redis", status: "fail", severity: "critical" }),
	row({ id: "app.env", status: "warn", severity: "high" }),
	row({ id: "devices.telnet", group: "devices", group_label: "Devices", status: "warn", severity: "medium" }),
	row({ id: "os.firewall", group: "os", group_label: "Operating system", status: "skip", severity: "high" }),
	row({ id: "app.key", status: "fail", severity: "high" }),
];

describe("Security Center helpers", () => {
	it("shows failures then warnings by severity by default", () => {
		expect(filterChecks(rows, defaultFilters()).map((check) => check.id)).toEqual(["redis.auth", "app.key", "app.env", "devices.telnet"]);
	});

	it("filters by status and group together", () => {
		expect(filterChecks(rows, { statuses: ["warn"], groups: ["devices"] }).map((check) => check.id)).toEqual(["devices.telnet"]);
		expect(filterChecks(rows, { statuses: [], groups: [] })).toHaveLength(rows.length);
	});

	it("sorts by status, then severity, then ID", () => {
		const sorted = [...rows].sort(compareChecks).map((check) => check.id);
		expect(sorted).toEqual(["redis.auth", "app.key", "app.env", "devices.telnet", "os.firewall", "app.debug"]);
	});

	it("counts every status", () => {
		expect(countStatuses(rows)).toEqual({ fail: 2, warn: 2, pass: 1, skip: 1 });
	});

	it("selects one status from a tile and goes back to needs attention on a second click", () => {
		expect(toggleStatus(["fail", "warn"], "pass")).toEqual(["pass"]);
		expect(toggleStatus(["pass"], "pass")).toEqual(["fail", "warn"]);
	});

	it("lists each group once, sorted by label", () => {
		expect(groupOptions(rows)).toEqual([
			{ value: "app", label: "Application" },
			{ value: "devices", label: "Devices" },
			{ value: "os", label: "Operating system" },
			{ value: "redis", label: "Redis" },
		]);
	});

	it("labels statuses, evidence and the run button", () => {
		expect(statusLabel("warn")).toBe("Warning");
		expect(formatEvidence(["a", "b"])).toBe("a, b");
		expect(formatEvidence(true)).toBe("yes");
		expect(formatEvidence(3)).toBe("3");
		expect(runLabel(false, null)).toBe("Run checks");
		expect(runLabel(true, "queued")).toBe("Queued…");
		expect(runLabel(true, "running")).toBe("Running checks…");
	});

	it("describes the age of the last run", () => {
		const now = new Date("2026-11-19T12:00:00Z");
		expect(relativeAge(null, now)).toBe("never");
		expect(relativeAge("2026-11-19T11:59:30Z", now)).toBe("just now");
		expect(relativeAge("2026-11-19T09:00:00Z", now)).toBe("3 h ago");
		expect(relativeAge("2026-11-18T09:00:00Z", now)).toBe("1 day ago");
	});
});
