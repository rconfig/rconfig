export function normalizeStoredTheme(storedTheme) {
	if (storedTheme === "light" || storedTheme === "dark") {
		return storedTheme;
	}

	return null;
}

export function resolveThemePreference(storedTheme) {
	return normalizeStoredTheme(storedTheme) ?? "dark";
}
