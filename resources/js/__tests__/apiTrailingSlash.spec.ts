import { describe, it, expect } from "vitest";
import { readFileSync } from "node:fs";
import { join } from "node:path";

/**
 * Regression guard for GitHub issue #327.
 *
 * A trailing slash on these API paths (e.g. "/api/device-models/?perPage=...")
 * triggers Laravel's Apache .htaccess "Redirect Trailing Slashes" 301. Behind a
 * TLS-terminating reverse proxy that redirect is emitted with the http scheme of
 * the internal hop, downgrading the request and getting it blocked by the browser
 * as mixed content. The routes are registered without a trailing slash, so the
 * slash is always wrong. Keep these calls slash-free.
 */
const CASES: Array<{ file: string; badUrl: string }> = [
	{ file: "pages/Shared/FormFields/TemplateMultiSelect.vue", badUrl: "/api/templates/?" },
	{ file: "pages/Shared/FormFields/CredentialsMultiSelect.vue", badUrl: "/api/settings/credentials/?" },
	{ file: "pages/Shared/FormFields/DeviceModelMultiSelect.vue", badUrl: "/api/get-device-models/?" },
	{ file: "pages/Shared/FormFields/VendorMultiSelect.vue", badUrl: "/api/vendors/?" },
	{ file: "pages/Shared/FormFields/TagMultiSelect.vue", badUrl: "/api/tags/?" },
	{ file: "pages/Shared/FormFields/CommandMultiSelect.vue", badUrl: "/api/commands/?" },
	{ file: "pages/Inventory/Devices/Filters/ModelFilter.vue", badUrl: "/api/device-models/?" },
	{ file: "pages/Inventory/Devices/Filters/VendorFilter.vue", badUrl: "/api/vendors/?" },
	{ file: "pages/Inventory/Devices/Filters/TagFilter.vue", badUrl: "/api/tags/?" },
	{ file: "pages/Inventory/Devices/Filters/CategoryFilter.vue", badUrl: "/api/categories/?" },
	{ file: "pages/Configs/useConfigsTable.js", badUrl: "/api/configs/`" },
	{ file: "pages/Configs/Filters/CommandFilter.vue", badUrl: "}/?perPage=10000" },
];

describe("API paths have no mixed-content trailing slash (issue #327)", () => {
	for (const testCase of CASES) {
		it(`${testCase.file} does not request ${testCase.badUrl}`, () => {
			const path = join(process.cwd(), "resources/js", testCase.file);
			const source = readFileSync(path, "utf8");
			expect(source).not.toContain(testCase.badUrl);
		});
	}
});
