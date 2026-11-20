/**
 * Pure helpers for the Security Review page: filtering, sorting, grouping and display tokens.
 * Kept free of Vue so they are unit-tested directly.
 */

export type CheckStatus = "pass" | "warn" | "fail" | "skip";
export type CheckSeverity = "critical" | "high" | "medium" | "low" | "info";

export interface SecurityCheckRow {
	id: string;
	group: string;
	group_label?: string;
	title: string;
	severity: CheckSeverity;
	status: CheckStatus;
	message: string;
	fix?: string | null;
	docs_url?: string;
	references?: string[];
	rationale?: string;
	evidence?: Record<string, unknown>;
}

export interface SecurityFilters {
	statuses: CheckStatus[];
	groups: string[];
}

export const STATUS_ORDER: Record<CheckStatus, number> = { fail: 3, warn: 2, skip: 1, pass: 0 };
export const SEVERITY_ORDER: Record<CheckSeverity, number> = { critical: 4, high: 3, medium: 2, low: 1, info: 0 };
export const NEEDS_ACTION: CheckStatus[] = ["fail", "warn"];

export function defaultFilters(): SecurityFilters {
	return { statuses: [...NEEDS_ACTION], groups: [] };
}

/** Failures first, then warnings, skipped and passed; within a status by severity, then ID. */
export function compareChecks(a: SecurityCheckRow, b: SecurityCheckRow): number {
	return (STATUS_ORDER[b.status] ?? 0) - (STATUS_ORDER[a.status] ?? 0) || (SEVERITY_ORDER[b.severity] ?? 0) - (SEVERITY_ORDER[a.severity] ?? 0) || a.id.localeCompare(b.id);
}

export function matchesFilters(row: SecurityCheckRow, filters: SecurityFilters): boolean {
	if (filters.statuses.length > 0 && !filters.statuses.includes(row.status)) {
		return false;
	}

	return filters.groups.length === 0 || filters.groups.includes(row.group);
}

export function filterChecks(rows: SecurityCheckRow[], filters: SecurityFilters): SecurityCheckRow[] {
	return rows.filter((row) => matchesFilters(row, filters)).sort(compareChecks);
}

export interface StatusCounts {
	fail: number;
	warn: number;
	pass: number;
	skip: number;
}

export function countStatuses(rows: SecurityCheckRow[]): StatusCounts {
	const counts: StatusCounts = { fail: 0, warn: 0, pass: 0, skip: 0 };

	for (const row of rows) {
		counts[row.status]++;
	}

	return counts;
}

/** Tile click: show only that status, or go back to failures and warnings when it is already the only one shown. */
export function toggleStatus(current: CheckStatus[], status: CheckStatus): CheckStatus[] {
	return current.length === 1 && current[0] === status ? [...NEEDS_ACTION] : [status];
}

export function groupOptions(rows: SecurityCheckRow[]): { value: string; label: string }[] {
	const seen = new Map<string, string>();

	for (const row of rows) {
		seen.set(row.group, row.group_label ?? row.group);
	}

	return [...seen.entries()].map(([value, label]) => ({ value, label })).sort((a, b) => a.label.localeCompare(b.label));
}

export function statusLabel(status: CheckStatus): string {
	return { fail: "Failed", warn: "Warning", pass: "Passed", skip: "Skipped" }[status] ?? status;
}

/** Tailwind classes for a status pill, light and dark. */
export function statusClasses(status: CheckStatus): string {
	return {
		fail: "bg-red-100 text-red-800 border-red-200 dark:bg-red-900/40 dark:text-red-200 dark:border-red-800",
		warn: "bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-900/40 dark:text-amber-200 dark:border-amber-800",
		pass: "bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-200 dark:border-emerald-800",
		skip: "bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700",
	}[status];
}

export function severityClasses(severity: CheckSeverity): string {
	return {
		critical: "text-red-700 dark:text-red-300",
		high: "text-orange-700 dark:text-orange-300",
		medium: "text-amber-700 dark:text-amber-300",
		low: "text-slate-600 dark:text-slate-300",
		info: "text-slate-500 dark:text-slate-400",
	}[severity];
}

/** "3 h ago" style age of an ISO timestamp, against now. */
export function relativeAge(iso: string | null | undefined, now: Date = new Date()): string {
	if (!iso) return "never";

	const seconds = Math.max(0, Math.round((now.getTime() - new Date(iso).getTime()) / 1000));

	if (seconds < 60) return "just now";
	if (seconds < 3600) return `${Math.floor(seconds / 60)} min ago`;
	if (seconds < 86400) return `${Math.floor(seconds / 3600)} h ago`;

	const days = Math.floor(seconds / 86400);

	return days === 1 ? "1 day ago" : `${days} days ago`;
}

/** Evidence values as text: lists joined, booleans as yes/no. */
export function formatEvidence(value: unknown): string {
	if (Array.isArray(value)) return value.join(", ");
	if (typeof value === "boolean") return value ? "yes" : "no";
	return String(value);
}

/** Run button label for the current task state. */
export function runLabel(isRunning: boolean, taskStatus: string | null | undefined): string {
	if (!isRunning) return "Run checks";
	return taskStatus === "queued" ? "Queued…" : "Running checks…";
}
