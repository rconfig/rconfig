import { resolveThemePreference } from "./themePreference";

export function applyStoredTheme() {
	const root = document.documentElement;
	const forceDark = root.getAttribute("data-force-dark") === "true";

	let storedTheme;
	try {
		storedTheme = localStorage.getItem("theme") || localStorage.getItem("vueuse-color-scheme");
	} catch {
		storedTheme = null;
	}

	const resolvedTheme = forceDark ? "dark" : resolveThemePreference(storedTheme);
	const isDark = resolvedTheme === "dark";

	root.setAttribute("data-theme", resolvedTheme);
	root.classList.toggle("dark", isDark);
	root.style.colorScheme = isDark ? "dark" : "light";
}
