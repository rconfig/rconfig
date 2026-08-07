<script>
	(function () {
		var root = document.documentElement;
		var forceDark = root.getAttribute("data-force-dark") === "true";
		var storedTheme = null;

		try {
			storedTheme = localStorage.getItem("theme") || localStorage.getItem("vueuse-color-scheme");
		} catch (e) {
			storedTheme = null;
		}

		if (storedTheme !== "light" && storedTheme !== "dark") {
			storedTheme = null;
		}

		var resolvedTheme = forceDark ? "dark" : (storedTheme || "dark");
		var isDark = resolvedTheme === "dark";

		root.setAttribute("data-theme", resolvedTheme);
		root.classList.toggle("dark", isDark);
		root.style.colorScheme = isDark ? "dark" : "light";
	})();
</script>
