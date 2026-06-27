import { describe, expect, it, vi } from "vitest";

// @ts-ignore — composable is JS, no type declarations
import { useFormatters } from "../useFormatters";

// ---------------------------------------------------------------------------
// Fixtures
// ---------------------------------------------------------------------------

/**
 * Fixed timestamp so assertions stay predictable regardless of when the
 * suite runs.
 */
const TEST_TIMESTAMP = "2026-06-05T10:25:30.000Z";

describe("useFormatters", () => {
	// -----------------------------------------------------------------------
	// Text casing helpers. Each guards falsy input (except 0) with "".
	// -----------------------------------------------------------------------

	describe("text casing", () => {
		const { uppercase, lowercase, capitalize, capitalizeFirstLetter, getFirstLetterCapitalized } = useFormatters();

		it("uppercase converts to upper case", () => {
			expect(uppercase("cisco")).toBe("CISCO");
		});

		it("lowercase converts to lower case", () => {
			expect(lowercase("CISCO")).toBe("cisco");
		});

		it("capitalize upper-cases the first letter of every word", () => {
			expect(capitalize("cisco edge router")).toBe("Cisco Edge Router");
		});

		it("capitalizeFirstLetter upper-cases only the first character", () => {
			expect(capitalizeFirstLetter("hello world")).toBe("Hello world");
		});

		it("getFirstLetterCapitalized returns just the first letter", () => {
			expect(getFirstLetterCapitalized("hello")).toBe("H");
		});

		it("casing helpers return '' for null but keep the 0 value", () => {
			expect(uppercase(null)).toBe("");
			expect(lowercase(undefined)).toBe("");
			expect(capitalize(0)).toBe("0");
			expect(getFirstLetterCapitalized(0)).toBe("0");
		});
	});

	// -----------------------------------------------------------------------
	// String shaping helpers.
	// -----------------------------------------------------------------------

	describe("string shaping", () => {
		const { removeOuterQuotationMarks, formatTextWithNewlines, formatKeyLabel, snakeToNormalUcFirst, arrayToPrettyString } = useFormatters();

		it("removeOuterQuotationMarks strips surrounding double quotes", () => {
			expect(removeOuterQuotationMarks('"quoted"')).toBe("quoted");
		});

		it("removeOuterQuotationMarks strips surrounding single quotes", () => {
			expect(removeOuterQuotationMarks("'quoted'")).toBe("quoted");
		});

		it("formatTextWithNewlines swaps newlines for break tags", () => {
			expect(formatTextWithNewlines("Line 1\nLine 2")).toBe("Line 1<br />Line 2");
		});

		it("formatKeyLabel humanises snake_case and camelCase keys", () => {
			expect(formatKeyLabel("device_ip")).toBe("device ip");
			expect(formatKeyLabel("deviceName")).toBe("device Name");
		});

		it("snakeToNormalUcFirst humanises and upper-cases the first letter", () => {
			expect(snakeToNormalUcFirst("device_ip_address")).toBe("Device ip address");
		});

		it("arrayToPrettyString joins with commas and a trailing 'and'", () => {
			expect(arrayToPrettyString([])).toBe("");
			expect(arrayToPrettyString(["a"])).toBe("a");
			expect(arrayToPrettyString(["a", "b"])).toBe("a and b");
			expect(arrayToPrettyString(["a", "b", "c"])).toBe("a, b, and c");
		});
	});

	// -----------------------------------------------------------------------
	// Null / empty guards for the time formatters.
	// -----------------------------------------------------------------------

	describe("null and empty timestamp handling", () => {
		const { formatTime, formatDateOnly, formatTimeOnly } = useFormatters();

		it("formatTime returns -- for null", () => {
			expect(formatTime(null)).toBe("--");
		});

		it("formatTime returns -- for undefined", () => {
			expect(formatTime(undefined)).toBe("--");
		});

		it("formatTime returns -- for empty string", () => {
			expect(formatTime("")).toBe("--");
		});

		it("formatDateOnly returns -- for null", () => {
			expect(formatDateOnly(null)).toBe("--");
		});

		it("formatTimeOnly returns -- for null", () => {
			expect(formatTimeOnly(null)).toBe("--");
		});
	});

	// -----------------------------------------------------------------------
	// Valid timestamps format without throwing.
	// -----------------------------------------------------------------------

	describe("valid timestamp handling", () => {
		const { formatTime, formatDateOnly, formatTimeOnly } = useFormatters();

		it("formatTime renders a non-empty string for a real timestamp", () => {
			const result = formatTime(TEST_TIMESTAMP);
			expect(result).not.toBe("--");
			expect(result.length).toBeGreaterThan(0);
		});

		it("formatDateOnly omits the time portion", () => {
			expect(formatDateOnly(TEST_TIMESTAMP)).not.toMatch(/\d{1,2}:\d{2}/);
		});

		it("formatTimeOnly includes a time portion", () => {
			expect(formatTimeOnly(TEST_TIMESTAMP)).toMatch(/\d{1,2}:\d{2}/);
		});
	});

	// -----------------------------------------------------------------------
	// timeFrom: relative time, with a guard for invalid input.
	// -----------------------------------------------------------------------

	describe("timeFrom", () => {
		const { timeFrom } = useFormatters();

		it("returns 'just now' for the current moment", () => {
			expect(timeFrom(new Date().toISOString())).toBe("just now");
		});

		it("pluralises the largest fitting interval", () => {
			const twoHoursAgo = new Date(Date.now() - 2 * 3600 * 1000).toISOString();
			expect(timeFrom(twoHoursAgo)).toBe("2 hrs ago");
		});

		it("uses the singular label for a single unit", () => {
			const oneDayAgo = new Date(Date.now() - 86400 * 1000).toISOString();
			expect(timeFrom(oneDayAgo)).toBe("1 day ago");
		});

		it("guards invalid timestamps", () => {
			vi.spyOn(console, "error").mockImplementation(() => {});
			expect(timeFrom("not-a-date")).toBe(" -- ");
			vi.restoreAllMocks();
		});
	});

	// -----------------------------------------------------------------------
	// formatDuration: difference between two timestamps, in seconds.
	// -----------------------------------------------------------------------

	describe("formatDuration", () => {
		const { formatDuration } = useFormatters();

		it("reports the gap between two timestamps in whole seconds", () => {
			expect(formatDuration("2026-06-05T10:25:30.000Z", "2026-06-05T10:25:45.000Z")).toBe("15 seconds");
		});

		it("reports zero seconds for identical timestamps", () => {
			expect(formatDuration(TEST_TIMESTAMP, TEST_TIMESTAMP)).toBe("0 seconds");
		});
	});

	// -----------------------------------------------------------------------
	// formatSeconds: pluralised, unit-aware human-readable durations.
	// -----------------------------------------------------------------------

	describe("formatSeconds", () => {
		const { formatSeconds } = useFormatters();

		it("renders sub-minute values in seconds", () => {
			expect(formatSeconds(45)).toBe("45 seconds");
		});

		it("renders minute values with the singular unit", () => {
			expect(formatSeconds(60)).toBe("1 minute");
		});

		it("renders minute values with the plural unit", () => {
			expect(formatSeconds(180)).toBe("3 minutes");
		});

		it("renders hour values", () => {
			expect(formatSeconds(3600)).toBe("1 hour");
		});
	});

	describe("formatSecondsWithClass", () => {
		const { formatSecondsWithClass } = useFormatters();

		it("wraps the value and unit in styled spans", () => {
			const html = formatSecondsWithClass(120);
			expect(html).toContain(">2<");
			expect(html).toContain("minutes");
			expect(html).toContain("text-muted-foreground");
		});
	});

	// -----------------------------------------------------------------------
	// formatFileSize: byte counts rounded down to the largest fitting unit.
	// -----------------------------------------------------------------------

	describe("formatFileSize", () => {
		const { formatFileSize } = useFormatters();

		it("returns 0 B for zero bytes", () => {
			expect(formatFileSize(0)).toBe("0 B");
		});

		it("keeps small values in bytes", () => {
			expect(formatFileSize(512)).toBe("512 B");
		});

		it("scales into kilobytes", () => {
			expect(formatFileSize(2048)).toBe("2 KB");
		});

		it("scales into megabytes", () => {
			expect(formatFileSize(1048576)).toBe("1 MB");
		});
	});

	// -----------------------------------------------------------------------
	// capitalize edge cases (shared with the casing block above).
	// -----------------------------------------------------------------------

	describe("capitalize", () => {
		const { capitalize } = useFormatters();

		it("returns an empty string for null", () => {
			expect(capitalize(null)).toBe("");
		});
	});

	// -----------------------------------------------------------------------
	// getLogLevelClass: maps a log level to a tailwind colour class.
	// -----------------------------------------------------------------------

	describe("getLogLevelClass", () => {
		const { getLogLevelClass } = useFormatters();

		it("maps error-class levels to red", () => {
			expect(getLogLevelClass("error")).toBe("text-red-400");
			expect(getLogLevelClass("CRITICAL")).toBe("text-red-400");
		});

		it("maps warnings to amber", () => {
			expect(getLogLevelClass("warning")).toBe("text-amber-500");
		});

		it("maps info to blue", () => {
			expect(getLogLevelClass("info")).toBe("text-blue-500");
		});

		it("falls back to gray for unknown or empty levels", () => {
			expect(getLogLevelClass("verbose")).toBe("text-gray-500");
			expect(getLogLevelClass(null)).toBe("text-gray-500");
		});
	});

	// -----------------------------------------------------------------------
	// severityBadgeWithStyling: text + classes for a severity badge.
	// -----------------------------------------------------------------------

	describe("severityBadgeWithStyling", () => {
		const { severityBadgeWithStyling } = useFormatters();

		it("returns an empty badge for a falsy severity", () => {
			const badge = severityBadgeWithStyling("");
			expect(badge.text).toBe("");
			expect(badge.classes).toContain("bg-gray-600");
		});

		it("capitalises the text and colours critical red", () => {
			const badge = severityBadgeWithStyling("critical");
			expect(badge.text).toBe("Critical");
			expect(badge.classes).toContain("bg-red-600");
		});

		it("colours low severity green", () => {
			expect(severityBadgeWithStyling("low").classes).toContain("bg-green-500");
		});

		it("falls back to gray for an unknown severity", () => {
			expect(severityBadgeWithStyling("bogus").classes).toContain("bg-gray-600");
		});
	});

	// -----------------------------------------------------------------------
	// cronToHuman: delegates to cronstrue, guarding invalid expressions.
	// -----------------------------------------------------------------------

	describe("cronToHuman", () => {
		const { cronToHuman } = useFormatters();

		it("describes a valid cron expression", () => {
			expect(cronToHuman("0 0 * * *")).toMatch(/12:00 AM/i);
		});

		it("returns a guard message for an invalid expression", () => {
			vi.spyOn(console, "error").mockImplementation(() => {});
			expect(cronToHuman("not a cron")).toBe("Invalid cron expression");
			vi.restoreAllMocks();
		});
	});
});
