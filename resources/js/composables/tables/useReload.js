export function useReload(fetchFns) {
	// Normalize into an array so it works for single or multiple
	const fns = Array.isArray(fetchFns) ? fetchFns : [fetchFns];

	function reload() {
		fns.forEach((fn) => {
			if (typeof fn === "function") {
				fn();
			}
		});
	}

	return { reload };
}
