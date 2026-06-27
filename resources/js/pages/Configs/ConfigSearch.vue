<script setup>
import { computed, ref } from "vue";
import ConfigSearchResultsTable from "@/pages/Configs/ConfigSearch/ConfigSearchResultsTable.vue";
import ConfigSearchActiveFiltersBar from "@/pages/Configs/ConfigSearch/ConfigSearchActiveFiltersBar.vue";
import ConfigSearchFilterCard from "@/pages/Configs/ConfigSearch/ConfigSearchFilterCard.vue";
import AlertTip from "@/pages/Shared/Alerts/AlertTip.vue";
import { Button } from "@/components/ui/button";

const submittedFilters = ref(null);
const submittedModel = ref(null);
const isEditingFilters = ref(true);
const filterEditorKey = ref(0);
const hasSearched = computed(() => submittedFilters.value !== null);

const performSearch = ({ payload, model }) => {
	submittedFilters.value = { ...payload };
	submittedModel.value = model;
	isEditingFilters.value = false;
};

function editFilters() {
	isEditingFilters.value = true;
	filterEditorKey.value += 1;
}

function clearSearch() {
	submittedFilters.value = null;
	submittedModel.value = null;
	isEditingFilters.value = true;
	filterEditorKey.value += 1;
}

function cancelEdit() {
	if (hasSearched.value) {
		isEditingFilters.value = false;
	}
}
</script>

<template>
	<div>
		<div class="flex-1 overflow-y-auto">
			<div class="mx-auto flex w-full flex-col gap-6 p-4 md:p-6 xl:p-4">

				<section v-if="isEditingFilters" class="space-y-4">
					<AlertTip
						v-if="hasSearched"
						title="Editing Active Search"
						message="Refine the query, then submit again to update the results."
						small
					/>
					<div v-if="hasSearched" class="flex justify-end">
						<Button variant="outline" size="sm" class="rounded-full px-4 text-xs font-medium rc-btn-shadow hover:animate-pulse" @click="cancelEdit()">
							Keep Current Results
						</Button>
					</div>

					<div class="w-full">
						<ConfigSearchFilterCard
							:key="filterEditorKey"
							:initial-model="submittedModel"
							:search-button-label="hasSearched ? 'Update Search' : 'Search'"
							@search-completed="performSearch"
						/>
					</div>
				</section>

				<ConfigSearchActiveFiltersBar
					v-else-if="hasSearched"
					:model="submittedModel"
					@edit="editFilters"
					@clear="clearSearch"
				/>

				<section v-if="hasSearched" class="overflow-hidden rounded-[28px] border border-border/60 bg-card/95 py-4 shadow-sm backdrop-blur">
					<div class="border-b px-6 pb-4">
						<h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Results</h2>
						<p class="mt-1 text-xs text-muted-foreground">Results stay full width so the filters never fight the table for space.</p>
					</div>
					<ConfigSearchResultsTable :filters="submittedFilters" />
				</section>

			</div>
		</div>
	</div>
</template>
