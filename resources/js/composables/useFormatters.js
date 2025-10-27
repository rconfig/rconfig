/**
 * useFormatters - A collection of formatting utilities for consistent data presentation
 *
 * This composable provides formatting functions organized by category:
 * - Text formatting: Capitalization, case conversion
 * - Time formatting: Timestamps, durations, relative times
 * - Number formatting: File sizes, durations in seconds
 * - Styling: Classes for log levels and other status indicators
 * - Specialized formatting: Cron expressions
 */

import cronstrue from "cronstrue";

export function useFormatters() {
	/**
	 * ===========================
	 * TEXT FORMATTING FUNCTIONS
	 * ===========================
	 */

	/**
	 * Convert string to uppercase
	 * @param {string} value - Input string
	 * @returns {string} Uppercase string
	 */
	const uppercase = (value) => {
		if (!value && value !== 0) return "";
		return value.toString().toUpperCase();
	};

	const lowercase = (value) => {
		if (!value && value !== 0) return "";
		return value.toString().toLowerCase();
	};

	/**
	 * Capitalize first letter of each word in a string
	 * @param {string} value - Input string
	 * @returns {string} String with each word capitalized
	 * @example "hello world" => "Hello World"
	 */
	const capitalize = (value) => {
		if (!value && value !== 0) return "";
		return value
			.toString()
			.split(" ")
			.map((word) => word.charAt(0).toUpperCase() + word.slice(1))
			.join(" ");
	};

	/**
	 * Capitalize only the first letter of a string
	 * @param {string} value - Input string
	 * @returns {string} String with first letter capitalized
	 * @example "hello world" => "Hello world"
	 */
	const capitalizeFirstLetter = (value) => {
		if (!value && value !== 0) return "";
		const str = value.toString();
		return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
	};

	/**
	 * Get only the first letter of a string, capitalized
	 * @param {string} value - Input string
	 * @returns {string} First letter capitalized
	 * @example "hello" => "H"
	 */
	const getFirstLetterCapitalized = (value) => {
		if (!value && value !== 0) return "";
		return value.toString().charAt(0).toUpperCase();
	};

	const removeOuterQuotationMarks = (value) => {
		if (!value && value !== 0) return "";
		return value.toString().replace(/^["']|["']$/g, "");
	};

	/**
	 * Function to process text and replace newlines with <br /> tags
	 * @param {string} text - Input text with newlines
	 * @returns {string} Text with <br /> tags replacing newlines
	 * @example "Line 1\nLine 2" => "Line 1<br />Line 2"
	 *
	 */
	const formatTextWithNewlines = (text) => {
		return text.replace(/\n/g, "<br />");
	};

	// array to pretty string
	function arrayToPrettyString(ids) {
		if (ids.length === 0) return "";
		if (ids.length === 1) return ids[0].toString();
		if (ids.length === 2) return ids.join(" and ");
		return ids.slice(0, -1).join(", ") + ", and " + ids[ids.length - 1];
	}

	/**
	 * Convert keys with underscores or camelCase to human-readable words
	 * @param {string} value - Input key
	 * @returns {string} Formatted key (e.g., "Device_ip" => "Device ip")
	 */
	const formatKeyLabel = (value) => {
		if (!value && value !== 0) return "";
		return value
			.toString()
			.replace(/_/g, " ")
			.replace(/([a-z])([A-Z])/g, "$1 $2")
			.replace(/\s+/g, " ") // Normalize spaces
			.trim();
	};

	const snakeToNormalUcFirst = (value) => {
		if (!value && value !== 0) return "";
		const str = value
			.toString()
			.replace(/_/g, " ")
			.replace(/([a-z])([A-Z])/g, "$1 $2")
			.replace(/\s+/g, " ") // Normalize spaces
			.trim();

		return str.charAt(0).toUpperCase() + str.slice(1);
	};

	/**
	 * ===========================
	 * TIME FORMATTING FUNCTIONS
	 * ===========================
	 */

	/**
	 * Format timestamp to locale string
	 * @param {string|Date} timestamp - Timestamp to format
	 * @returns {string} Formatted date and time
	 */
	function formatTime(timestamp, dateOnly = false) {
		if (!timestamp) return "--";
		const date = new Date(timestamp);
		return dateOnly ? date.toLocaleDateString() : date.toLocaleString();
	}

	function formatDateOnly(timestamp) {
		if (!timestamp) return "--";
		return new Date(timestamp).toLocaleDateString();
	}

	function formatTimeOnly(timestamp) {
		if (!timestamp) return "--";
		return new Date(timestamp).toLocaleTimeString();
	}

	/**
	 * Calculate and format time duration between two timestamps
	 * @param {string|Date} starttime - Start timestamp
	 * @param {string|Date} endtime - End timestamp
	 * @returns {string} Formatted duration in seconds
	 */
	function formatDuration(starttime, endtime) {
		const start = new Date(starttime);
		const end = new Date(endtime);
		const diff = end - start;
		const seconds = Math.floor(diff / 1000);
		return `${seconds} seconds`;
	}

	function formatSnippetCode(content) {
		// If content is already a string, use it directly
		let textContent = typeof content === "object" ? JSON.stringify(content, null, 2) : content;

		// Store raw unformatted content for copying
		const rawCode = textContent;

		// Escape HTML to prevent XSS issues
		textContent = textContent.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

		// Format with proper line breaks
		const lines = textContent.split("\n");

		const formattedLines = lines.map((line) => {
			line = line.trim(); // Remove leading/trailing whitespace

			if (line.startsWith("//")) {
				return `<span class="text-green-500 float-left">${line}</span>`;
			}

			line = line.replace(/\{([^}]+)\}/g, '<span class="text-blue-400">{$1}</span>');
			return line.replace(/(\#\[[^\]]+\])/g, '<span class="text-green-500">$1</span>');
		});

		// Trim the final result to remove any leading/trailing blank lines
		return formattedLines.join("\n").trim();
	}

	/**
	 * Format timestamp as relative time (e.g., "2 hrs ago")
	 * @param {string|Date} timestamp - Timestamp to format
	 * @returns {string} Relative time string
	 */
	const timeFrom = (timestamp) => {
		const now = new Date();
		const past = new Date(timestamp);

		// Validate if the timestamp is a valid date
		if (isNaN(past)) {
			console.error(`Invalid timestamp: ${timestamp}`);
			return " -- ";
		}

		const diffInSeconds = Math.floor((now - past) / 1000);

		const intervals = [
			{ label: "yr", seconds: 31536000 },
			{ label: "mth", seconds: 2592000 },
			{ label: "day", seconds: 86400 },
			{ label: "hr", seconds: 3600 },
			{ label: "min", seconds: 60 },
		];

		for (const interval of intervals) {
			const count = Math.floor(diffInSeconds / interval.seconds);
			if (count >= 1) {
				return `${count} ${interval.label}${count > 1 ? "s" : ""} ago`;
			}
		}
		return "just now";
	};

	/**
	 * ===========================
	 * NUMBER FORMATTING FUNCTIONS
	 * ===========================
	 */

	/**
	 * Format file size in bytes to human-readable format
	 * @param {number} bytes - File size in bytes
	 * @returns {string} Formatted file size (e.g., "4.2 MB")
	 */
	function formatFileSize(bytes) {
		if (bytes === 0) return "0 B";
		const sizes = ["B", "KB", "MB", "GB", "TB"];
		const i = Math.floor(Math.log(bytes) / Math.log(1024));
		return Math.floor(bytes / Math.pow(1024, i)) + " " + sizes[i];
	}

	/**
	 * Format seconds to human-readable time
	 * @param {number} seconds - Time in seconds
	 * @returns {string} Formatted time string
	 * @example 70 => "1 minute", 3600 => "1 hour"
	 */
	const formatSeconds = (seconds) => {
		if (seconds < 60) {
			return `${seconds} seconds`;
		} else if (seconds < 3600) {
			const minutes = Math.floor(seconds / 60);
			return `${minutes} ${minutes === 1 ? "minute" : "minutes"}`;
		} else {
			const hours = Math.floor(seconds / 3600);
			return `${hours} ${hours === 1 ? "hour" : "hours"}`;
		}
	};

	/**
	 * Format seconds with HTML for styling
	 * @param {number} seconds - Time in seconds
	 * @returns {string} HTML string with styled time values
	 */
	const formatSecondsWithClass = (seconds) => {
		const valueClasses = "text-white"; // For the numeric part
		const unitClasses = "text-xs text-muted-foreground"; // For the units part

		if (seconds < 60) {
			return `<span class="${valueClasses}">${seconds}</span><span class="${unitClasses}">seconds</span>`;
		} else if (seconds < 3600) {
			const minutes = Math.floor(seconds / 60);
			const unit = minutes === 1 ? "minute" : "minutes";
			return `<span class="${valueClasses}">${minutes}</span><span class="${unitClasses}">${unit}</span>`;
		} else {
			const hours = Math.floor(seconds / 3600);
			const unit = hours === 1 ? "hour" : "hours";
			return `<span class="${valueClasses}">${hours}</span><span class="${unitClasses}">${unit}</span>`;
		}
	};

	/**
	 * ===========================
	 * STYLING FUNCTIONS
	 * ===========================
	 */

	/**
	 * Get appropriate CSS class for log level
	 * @param {string} level - Log level
	 * @returns {string} CSS class name
	 */
	const getLogLevelClass = (level) => {
		if (!level) return "text-gray-500";

		switch (level.toLowerCase()) {
			case "critical":
			case "alert":
			case "emergency":
			case "error":
				return "text-red-400";
			case "warning":
			case "warn":
				return "text-amber-500";
			case "notice":
			case "info":
				return "text-blue-500";
			case "debug":
				return "text-indigo-500";
			default:
				return "text-gray-500";
		}
	};

	/**
	 * ===========================
	 * SPECIALIZED FORMATTING
	 * ===========================
	 */

	/**
	 * Convert cron expression to human-readable description
	 * @param {string} cronExpression - Valid cron expression
	 * @returns {string} Human-readable description
	 * @example "0 0 * * *" => "At 12:00 AM"
	 */
	const cronToHuman = (cronExpression) => {
		try {
			return cronstrue.toString(cronExpression);
		} catch (error) {
			console.error(`Invalid cron expression: ${cronExpression}`, error);
			return "Invalid cron expression";
		}
	};

	// Config_changes severityBadgeWithStyling
	const severityBadgeWithStyling = (severity) => {
		if (!severity) {
			return {
				text: "",
				classes: "px-2 py-1 rounded-full text-xs font-medium bg-gray-600 text-gray-200",
			};
		}

		const severityLower = severity.toLowerCase();
		const text = capitalize(severity); // Use the capitalize function from the same composable

		let colorClasses = "";
		switch (severityLower) {
			case "critical":
				colorClasses = "bg-red-600 text-white";
				break;
			case "high":
				colorClasses = "bg-orange-500 text-white";
				break;
			case "medium":
				colorClasses = "bg-yellow-400 text-black";
				break;
			case "low":
				colorClasses = "bg-green-500 text-white";
				break;
			default:
				colorClasses = "bg-gray-600 text-gray-200";
		}

		const classes = `px-2 py-1 rounded-full text-xs font-medium ${colorClasses}`;

		return { text, classes };
	};

	// Return all formatting functions organized by category
	return {
		// Text formatting
		uppercase,
		lowercase,
		capitalize,
		capitalizeFirstLetter,
		getFirstLetterCapitalized,
		removeOuterQuotationMarks,
		formatTextWithNewlines,
		arrayToPrettyString,
		formatKeyLabel,
		snakeToNormalUcFirst,

		// Time formatting
		formatTime,
		formatDateOnly,
		formatTimeOnly,
		formatDuration,
		formatSnippetCode,
		timeFrom,

		// Number formatting
		formatFileSize,
		formatSeconds,
		formatSecondsWithClass,

		// Styling
		getLogLevelClass,

		// Specialized formatting
		cronToHuman,
		severityBadgeWithStyling,
	};
}
