export function normalizeSettingsPath(path) {
	if (!path) {
		return path;
	}

	if (path.startsWith("/settings/credentials/")) {
		return "/settings/credentials";
	}

	if (path.startsWith("/settings/data-purge/")) {
		return "/settings/data-purge";
	}

	return path;
}
