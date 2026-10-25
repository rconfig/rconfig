/**
 * Helpers for the rows returned by /api/dashboard/health-latest.
 *
 * SystemHealthController builds each row positionally, so the documented API
 * shape carries the check result (ok, warning, failed, crashed, skipped) in
 * `notificationMessage`, the human message in `shortSummary`, and the short
 * summary shown on the card in `status`. Read the result through checkResult()
 * rather than guessing from the display text.
 */
import { RCONFIG_DOCS_URL } from "@/config/constants";

/**
 * Checks whose failure is a security problem that should be called out on the
 * dashboard, each with the docs page that explains how to fix it.
 */
export const SECURITY_CHECK_DOCS = {
	"Redis Auth": `${RCONFIG_DOCS_URL}/auth-security/redis-authentication/`,
	"Worker Privilege": `${RCONFIG_DOCS_URL}/auth-security/worker-privilege/`,
};

export const SECURITY_CHECK_NAMES = Object.keys(SECURITY_CHECK_DOCS);

export function checkResult(item) {
	return item?.notificationMessage ?? "";
}

export function dotClass(item) {
	const result = checkResult(item);

	if (result === "ok") {
		return "bg-green-500";
	}

	if (result === "warning") {
		return "bg-amber-500";
	}

	return "bg-red-500";
}

/**
 * Security checks that are not ok, worst first, each with the message to show.
 */
export function securityAlerts(items) {
	if (!Array.isArray(items)) {
		return [];
	}

	return items
		.filter((item) => SECURITY_CHECK_NAMES.includes(item.name) && checkResult(item) !== "ok" && checkResult(item) !== "skipped")
		.map((item) => ({
			name: item.name,
			label: item.label || item.name,
			severity: checkResult(item) === "warning" ? "warning" : "failed",
			message: item.shortSummary || item.status,
			docsUrl: SECURITY_CHECK_DOCS[item.name],
		}))
		.sort((a, b) => (a.severity === b.severity ? 0 : a.severity === "failed" ? -1 : 1));
}
