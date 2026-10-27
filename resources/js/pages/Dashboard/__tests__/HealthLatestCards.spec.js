import { describe, expect, it } from "vitest";
import { mount } from "@vue/test-utils";
import HealthLatestCards from "@/pages/Dashboard/HealthLatestCards.vue";
import { dotClass, securityAlerts } from "@/pages/Dashboard/healthStatus";

/**
 * Mirrors the row shape SystemHealthController returns: the check result is in
 * notificationMessage, the message in shortSummary, the card text in status.
 */
function row(name, result, status, message = "") {
	return { name, label: name, notificationMessage: result, shortSummary: message, status, meta: [] };
}

const healthyRows = [
	row("CpuLoad", "ok", "0.23 0.4 0.42"),
	row("Horizon", "ok", "Running"),
	row("Ping", "ok", "rconfig.com reachable"),
	row("Database", "ok", "Ok"),
	row("Redis Auth", "ok", "authenticated", "Redis requires authentication."),
	row("Worker Privilege", "ok", "Ok", "Horizon and the scheduler run as the web user."),
];

function mountCard(data) {
	return mount(HealthLatestCards, {
		props: { healthLatest: { data }, isLoadingHealth: false, SystemUptime: "up 1 day" },
		global: {
			stubs: { RcIcon: true, HorizonLogo: true, HoverCard: true, HoverCardContent: true, HoverCardTrigger: true },
		},
	});
}

describe("healthStatus helpers", () => {
	it("colours dots from the check result, not the display text", () => {
		expect(dotClass(row("Ping", "ok", "rconfig.com reachable"))).toBe("bg-green-500");
		expect(dotClass(row("Disk Space: /storage", "warning", "75%"))).toBe("bg-amber-500");
		expect(dotClass(row("Ping", "failed", "unreachable"))).toBe("bg-red-500");
		expect(dotClass(row("Horizon", "crashed", ""))).toBe("bg-red-500");
	});

	it("returns no alerts when the security checks pass", () => {
		expect(securityAlerts(healthyRows)).toEqual([]);
		expect(securityAlerts(undefined)).toEqual([]);
	});

	it("ignores non-security checks that fail", () => {
		expect(securityAlerts([row("Ping", "failed", "unreachable", "Pinging failed")])).toEqual([]);
	});

	it("lists failed security checks before warnings, with the backend message", () => {
		const alerts = securityAlerts([
			row("Worker Privilege", "warning", "Run sudo ./update.sh", "Horizon runs as root."),
			row("Redis Auth", "failed", "unauthenticated", "Redis has no password set. Run: php artisan rconfig:redis-secure"),
		]);

		expect(alerts.map((a) => [a.name, a.severity])).toEqual([
			["Redis Auth", "failed"],
			["Worker Privilege", "warning"],
		]);
		expect(alerts[0].message).toBe("Redis has no password set. Run: php artisan rconfig:redis-secure");
	});

	it("links each security alert to its fix page on the Core docs site", () => {
		const alerts = securityAlerts([
			row("Redis Auth", "failed", "unauthenticated", "Redis has no password set."),
			row("Worker Privilege", "warning", "Run sudo ./update.sh", "Horizon runs as root."),
		]);

		expect(alerts.map((a) => a.docsUrl)).toEqual([
			"https://v8coredocs.rconfig.com/auth-security/redis-authentication/",
			"https://v8coredocs.rconfig.com/auth-security/worker-privilege/",
		]);
	});

	it("raises an HTTPS alert with the ssl command, linked to the Core SSL page", () => {
		const alerts = securityAlerts([row("HTTPS", "warning", "Enable HTTPS", "rConfig is served over HTTP. Run: sudo php artisan rconfig:ssl-self-signed for a self-signed certificate, or install your own certificate.")]);

		expect(alerts).toHaveLength(1);
		expect(alerts[0].severity).toBe("warning");
		expect(alerts[0].message).toContain("sudo php artisan rconfig:ssl-self-signed");
		expect(alerts[0].docsUrl).toBe("https://v8coredocs.rconfig.com/auth-security/ssl-setup/");
	});

	it("raises no HTTPS alert once rConfig is served over HTTPS", () => {
		expect(securityAlerts([row("HTTPS", "ok", "Ok", "rConfig is served over HTTPS.")])).toEqual([]);
	});

	it("treats an unreachable Redis (warning) as an alert", () => {
		const alerts = securityAlerts([row("Redis Auth", "warning", "unreachable", "Redis is unreachable; authentication state could not be determined.")]);

		expect(alerts).toHaveLength(1);
		expect(alerts[0].severity).toBe("warning");
	});
});

describe("HealthLatestCards", () => {
	it("shows no security alert when everything is ok, and Ping is green", () => {
		const wrapper = mountCard(healthyRows);

		expect(wrapper.find('[role="alert"]').exists()).toBe(false);

		const pingRow = wrapper.findAll("div.flex.justify-between").find((r) => r.text().includes("rconfig.com reachable"));
		expect(pingRow.find(".rounded-full").classes()).toContain("bg-green-500");
	});

	it("shows a red alert when Redis has no password", () => {
		const rows = healthyRows.map((r) => (r.name === "Redis Auth" ? row("Redis Auth", "failed", "unauthenticated", "Redis has no password set. Run: php artisan rconfig:redis-secure") : r));
		const alert = mountCard(rows).find('[data-testid="security-alert-Redis Auth"]');

		expect(alert.exists()).toBe(true);
		expect(alert.text()).toContain("php artisan rconfig:redis-secure");
		expect(alert.classes()).toContain("bg-red-500/10");

		const link = alert.find("a");
		expect(link.attributes("href")).toBe("https://v8coredocs.rconfig.com/auth-security/redis-authentication/");
		expect(link.attributes("target")).toBe("_blank");
		expect(link.attributes("rel")).toContain("noopener");
	});

	it("shows an amber alert when the worker privilege check warns", () => {
		const rows = healthyRows.map((r) => (r.name === "Worker Privilege" ? row("Worker Privilege", "warning", "Run sudo ./update.sh", "Horizon runs as root.") : r));
		const alert = mountCard(rows).find('[data-testid="security-alert-Worker Privilege"]');

		expect(alert.exists()).toBe(true);
		expect(alert.classes()).toContain("bg-amber-500/10");
	});
});
