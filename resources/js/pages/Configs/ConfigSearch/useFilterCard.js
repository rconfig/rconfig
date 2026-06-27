import { computed, ref } from "vue";
import {
	buildConfigSearchPayload,
	cloneConfigSearchModel,
	createDefaultConfigSearchModel,
	createEmptySearchCriterion,
	hasSearchCriteria,
	hydrateConfigSearchModel,
} from "./configSearchFilters";

export function useFilterCard(emit, initialModel = null) {
	const model = ref(initialModel ? hydrateConfigSearchModel(initialModel) : createDefaultConfigSearchModel());
	const datePickerKey = ref(0);
	const canSearch = computed(() => hasSearchCriteria(model.value.criteria));

	function clearAll() {
		model.value = createDefaultConfigSearchModel();
		datePickerKey.value += 1;
	}

	function addCriterion() {
		model.value.criteria.push(createEmptySearchCriterion());
	}

	function clearAllTerms() {
		model.value.criteria = [createEmptySearchCriterion()];
	}

	function removeCriterion(criterionId) {
		if (model.value.criteria.length === 1) {
			model.value.criteria = [createEmptySearchCriterion()];

			return;
		}

		model.value.criteria = model.value.criteria.filter((criterion) => criterion.id !== criterionId);
	}

	function setDates(dateRange) {
		if (dateRange.start && dateRange.end) {
			const startDate = `${dateRange.start.year}-${String(dateRange.start.month).padStart(2, "0")}-${String(dateRange.start.day).padStart(2, "0")}`;
			const endDate = `${dateRange.end.year}-${String(dateRange.end.month).padStart(2, "0")}-${String(dateRange.end.day).padStart(2, "0")}`;

			model.value.start_date = startDate;
			model.value.end_date = endDate;
		} else {
			model.value.start_date = "";
			model.value.end_date = "";
		}
	}

	function performSearch() {
		if (!canSearch.value) {
			return;
		}

		emit("searchCompleted", {
			payload: buildConfigSearchPayload(model.value),
			model: cloneConfigSearchModel(model.value),
		});
	}

	return {
		addCriterion,
		canSearch,
		clearAll,
		clearAllTerms,
		datePickerKey,
		model,
		performSearch,
		removeCriterion,
		setDates,
	};
}
