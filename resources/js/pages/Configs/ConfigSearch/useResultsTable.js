import axios from 'axios';
import { inject, ref, reactive, watch } from 'vue';
import { useDialogStore } from '@/stores/dialogActions';
import { useRouter } from 'vue-router';
import { usePageSettingsStore } from "@/stores/pageSettings";

export function useResultsTable(props) {
	const formatters = inject('formatters');
	const dialogStore = useDialogStore();
	const { openDialog, closeDialog, isDialogOpen } = dialogStore;
	const router = useRouter();
	const currentPage = ref(0);
	const errors = ref([]);
	const isFetching = ref(false);
	const lastPage = ref(1);
	const page = ref(1);

	const pageSettings = usePageSettingsStore();
	const perPage = ref(pageSettings.get("useResultsTable", "perPage", 5));
	const results = ref([]);

	const searchModel = reactive({
		device_name: "",
		command: "",
		device_category: "",
		search_string: "",
		lines_before: 5,
		lines_after: 5,
		latest_version_only: true,
		ignore_case: true,
		start_date: "",
		end_date: ""
	});

	watch(
		() => props.filters,
		newFilters => {
		applyFilters(newFilters);
		fetchResults();
		}
	);

	watch(perPage, () => {
		pageSettings.set("useResultsTable", "perPage", perPage.value);
		fetchResults();
	});

	function applyFilters(newFilters) {
		results.value = [];
		errors.value = [];
		currentPage.value = 0;
		lastPage.value = 1;
		page.value = 1;

		Object.assign(searchModel, newFilters);
	}

	// Function to handle the API call to fetch more results
	const fetchResults = async () => {
		isFetching.value = true;

		try {
		const response = await axios.post('/api/configs/search', {
			device_name: searchModel.device_name,
			command: searchModel.command,
			device_category: searchModel.device_category,
			search_string: searchModel.search_string,
			lines_before: searchModel.lines_before,
			lines_after: searchModel.lines_after,
			latest_version_only: searchModel.latest_version_only,
			ignore_case: searchModel.ignore_case,
			start_date: searchModel.start_date + ' 00:00:00',
			end_date: searchModel.end_date + ' 23:59:59',
			page: page.value,
			per_page: perPage.value
		});

		if (response.data.data.length) {
			results.value = response.data.data;
			currentPage.value = response.data.current_page;
			lastPage.value = response.data.last_page;
		}
		} catch (error) {
			console.log(error);
			errors.value = error.response?.data?.errors || [];
		} finally {
			isFetching.value = false;
		}
	};

	function viewDetailsPane(configId) {
		router.push({ name: 'configs-view', params: { id: parseInt(configId) }, query: { ref: 'configsearch' } });
	}

	function updatePerpage(event) {
		perPage.value = event;
		fetchResults();
	}
	function changePage(event) {
		console.log(event);
		page.value = event;
		fetchResults();
	}

	return {
		changePage,
		currentPage,
		errors,
		formatters,
		isDialogOpen,
		isFetching,
		lastPage,
		openDialog,
		perPage,
		results,
		searchModel,
		updatePerpage,
		viewDetailsPane
	};
}
