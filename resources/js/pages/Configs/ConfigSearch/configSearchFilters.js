let criterionSeed = 0;

export function createEmptySearchCriterion() {
	criterionSeed += 1;

	return {
		id: `criterion-${criterionSeed}`,
		term: "",
	};
}

export function createDefaultConfigSearchModel() {
	return {
		criteria_mode: "all",
		limit: 50,
		results_per_config: "first_match",
		criteria: [createEmptySearchCriterion()],
		devices: [],
		categories: [],
		commands: [],
		tags: [],
		latest_version_only: true,
		ignore_case: true,
		lines_before: 5,
		lines_after: 5,
		start_date: "",
		end_date: "",
	};
}

export function hydrateConfigSearchModel(initialModel = null) {
	const baseModel = createDefaultConfigSearchModel();

	if (!initialModel || typeof initialModel !== "object") {
		return baseModel;
	}

	const criteria = Array.isArray(initialModel.criteria)
		? initialModel.criteria.map((criterion) => ({
			id: criterion?.id ?? createEmptySearchCriterion().id,
			term: String(criterion?.term ?? ""),
		}))
		: [];

	return {
		...baseModel,
		criteria_mode: initialModel.criteria_mode ?? baseModel.criteria_mode,
		limit: Number.isFinite(Number(initialModel.limit)) ? Number(initialModel.limit) : baseModel.limit,
		results_per_config: initialModel.results_per_config ?? baseModel.results_per_config,
		criteria: criteria.length > 0 ? criteria : baseModel.criteria,
		devices: Array.isArray(initialModel.devices) ? initialModel.devices.map((device) => ({ ...device })) : [],
		categories: Array.isArray(initialModel.categories) ? initialModel.categories.map((category) => ({ ...category })) : [],
		commands: Array.isArray(initialModel.commands) ? initialModel.commands.map((command) => ({ ...command })) : [],
		tags: Array.isArray(initialModel.tags) ? initialModel.tags.map((tag) => ({ ...tag })) : [],
		latest_version_only: initialModel.latest_version_only ?? baseModel.latest_version_only,
		ignore_case: initialModel.ignore_case ?? baseModel.ignore_case,
		lines_before: Number.isFinite(Number(initialModel.lines_before)) ? Number(initialModel.lines_before) : baseModel.lines_before,
		lines_after: Number.isFinite(Number(initialModel.lines_after)) ? Number(initialModel.lines_after) : baseModel.lines_after,
		start_date: initialModel.start_date ?? baseModel.start_date,
		end_date: initialModel.end_date ?? baseModel.end_date,
	};
}

export function cloneConfigSearchModel(model) {
	return hydrateConfigSearchModel(model);
}

export function collectSelectedIds(items = []) {
	if (!Array.isArray(items)) {
		return [];
	}

	return items
		.map((item) => item?.id)
		.filter((itemId) => Number.isInteger(itemId));
}

export function normalizeSearchCriteria(criteria = []) {
	if (!Array.isArray(criteria)) {
		return [];
	}

	const seenTerms = new Set();

	return criteria.reduce((normalizedCriteria, criterion) => {
		const term = String(criterion?.term ?? "").trim();
		if (!term) {
			return normalizedCriteria;
		}

		const dedupeKey = term.toLowerCase();
		if (seenTerms.has(dedupeKey)) {
			return normalizedCriteria;
		}

		seenTerms.add(dedupeKey);
		normalizedCriteria.push({
			id: criterion?.id ?? createEmptySearchCriterion().id,
			term,
		});

		return normalizedCriteria;
	}, []);
}

export function getSearchTerms(criteria = []) {
	return normalizeSearchCriteria(criteria).map((criterion) => criterion.term);
}

export function hasSearchCriteria(criteria = []) {
	return normalizeSearchCriteria(criteria).length > 0;
}

export function buildConfigSearchPayload(model) {
	const criteria = normalizeSearchCriteria(model?.criteria ?? []);
	const searchTerms = criteria.map((criterion) => criterion.term);

	return {
		criteria,
		criteria_mode: model?.criteria_mode ?? "all",
		limit: Math.max(1, Number(model?.limit ?? 50)),
		results_per_config: model?.results_per_config ?? "first_match",
		devices: collectSelectedIds(model?.devices ?? []),
		categories: collectSelectedIds(model?.categories ?? []),
		commands: collectSelectedIds(model?.commands ?? []),
		tags: collectSelectedIds(model?.tags ?? []),
		latest_version_only: !!model?.latest_version_only,
		case_sensitive: !(model?.ignore_case ?? false),
		lines_before: Number(model?.lines_before ?? 0),
		lines_after: Number(model?.lines_after ?? 0),
		dateFrom: model?.start_date || undefined,
		dateTo: model?.end_date || undefined,
		search_terms: searchTerms,
	};
}
