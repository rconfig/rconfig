import axios from "axios";
import { inject, reactive, ref, watch } from "vue";
import { useDialogStore } from "@/stores/dialogActions";
import { useRouter } from "vue-router";

export function useResultsTable(props) {
	const formatters = inject("formatters");
	const { openDialog, isDialogOpen } = useDialogStore();
	const router = useRouter();
	const errors = ref([]);
	const isFetching = ref(false);
	const results = ref([]);
	const currentPage = ref(1);
	const perPage = ref(10);
	const lastPage = ref(1);
	const totalRecords = ref(0);
	const resultMeta = ref({
		limit: 50,
		results_returned: 0,
		limit_reached: false,
	});

	const searchModel = reactive({
		criteria: [],
		criteria_mode: "all",
		limit: 50,
		results_per_config: "first_match",
		devices: [],
		categories: [],
		commands: [],
		tags: [],
		lines_before: 5,
		lines_after: 5,
		latest_version_only: true,
		case_sensitive: false,
		dateFrom: undefined,
		dateTo: undefined,
		search_terms: [],
	});

	function handleFiltersChange(newFilters) {
		applyFilters(newFilters);
		currentPage.value = 1;

		if (searchModel.criteria.length > 0) {
			fetchResults();
		}
	}

	watch(() => props.filters, handleFiltersChange, { deep: true, immediate: true });

	watch([currentPage, perPage], ([newPage, newPerPage], [oldPage, oldPerPage]) => {
		if (!searchModel.criteria.length) {
			return;
		}
		if (newPerPage !== oldPerPage && newPage !== 1) {
			currentPage.value = 1;

			return;
		}
		fetchResults();
	});

	function applyFilters(newFilters) {
		results.value = [];
		errors.value = [];
		resultMeta.value = {
			limit: 50,
			results_returned: 0,
			limit_reached: false,
		};
		totalRecords.value = 0;
		lastPage.value = 1;

		Object.assign(searchModel, {
			criteria: [],
			criteria_mode: "all",
			limit: 50,
			results_per_config: "first_match",
			devices: [],
			categories: [],
			commands: [],
			tags: [],
			lines_before: 5,
			lines_after: 5,
			latest_version_only: true,
			case_sensitive: false,
			dateFrom: undefined,
			dateTo: undefined,
			search_terms: [],
			...newFilters,
		});
	}

	async function fetchResults() {
		if (!searchModel.criteria.length) {
			return;
		}

		isFetching.value = true;

		try {
			const response = await axios.post("/api/configs/search", {
				...searchModel,
				page: currentPage.value,
				perPage: perPage.value,
			});
			const rows = Array.isArray(response.data.data) ? response.data.data : [];
			const meta = response?.data?.meta ?? {};

			results.value = rows.map((row) => {
				const matches = Array.isArray(row.matches) ? row.matches : [];
				const previewMatch = row.preview_match || matches[0] || null;
				const matchCount = typeof row.match_count === "number" ? row.match_count : matches.length;

				return {
					...row,
					matches,
					preview_match: previewMatch,
					match_count: matchCount,
					search_terms: searchModel.search_terms,
				};
			});

			resultMeta.value = {
				limit: Number(meta.limit ?? searchModel.limit ?? 50),
				results_returned: Number(meta.results_returned ?? rows.length),
				limit_reached: Boolean(meta.limit_reached),
			};
			totalRecords.value = Number(meta.total ?? rows.length);
			lastPage.value = Number(meta.last_page ?? 1);
			if (meta.current_page && Number(meta.current_page) !== currentPage.value) {
				currentPage.value = Number(meta.current_page);
			}
		} catch (error) {
			console.log(error);
			errors.value = error?.response?.data?.errors || [];
		} finally {
			isFetching.value = false;
		}
	}

	function viewDetailsPane(configId) {
		router.push({ name: "config-view", params: { id: parseInt(configId) }, query: { ref: "configsearch" } });
	}

	return {
		errors,
		formatters,
		isDialogOpen,
		isFetching,
		openDialog,
		results,
		resultMeta,
		searchModel,
		viewDetailsPane,
		currentPage,
		perPage,
		lastPage,
		totalRecords,
	};
}
