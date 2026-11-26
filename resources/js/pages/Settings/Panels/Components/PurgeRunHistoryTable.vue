<script setup>
import ActionsMenu from "@/pages/Shared/Table/ActionsMenu.vue";
import ClearFilters from "@/pages/Shared/Filters/ClearFilters.vue";
import Loading from "@/pages/Shared/Table/Loading.vue";
import NoResults from "@/pages/Shared/Table/NoResults.vue";
import Pagination from "@/pages/Shared/Table/Pagination.vue";
import RcConfirmAlertDialog from "@/pages/Shared/ConfirmAlertDialog/RcConfirmAlertDialog.vue";
import PurgeRunHistoryHoverCard from "@/pages/Settings/Panels/Components/PurgeRunHistoryHoverCard.vue";
import Spinner from "@/pages/Shared/Icon/Spinner.vue";
import { Input } from "@/components/ui/input";
import { Separator } from "@/components/ui/separator";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { eventBus } from "@/composables/eventBus";
import { computed, onMounted, onUnmounted } from "vue";
import { useRowSelection } from "@/composables/useRowSelection";
import { usePurgeRunHistory } from "./usePurgeRunHistory";

const { clearFilters, currentPage, deleteManyRuns, deleteRun, dryRunFilter, formatters, hasMoreData, isLoading, isLoadingMore, isLoadMoreMode, lastPage, loadMoreRuns, perPage, reload, runs, searchTerm, showConfirmDelete, sortParam, statusFilter, toggleSort } = usePurgeRunHistory();

const { selectedRows, selectAll, toggleSelectAll, toggleSelectRow } = useRowSelection(runs);

const statusOptions = [
	{ label: "All statuses", value: "all" },
	{ label: "Running", value: "running" },
	{ label: "Success", value: "success" },
	{ label: "Completed with errors", value: "completed_with_errors" },
	{ label: "Failed", value: "failed" },
	{ label: "Pending", value: "pending" },
];

const dryRunOptions = [
	{ label: "Any run type", value: "any" },
	{ label: "Dry run", value: 1 },
	{ label: "Live run", value: 0 },
];

const clearSelection = () => {
	selectedRows.value = [];
	selectAll.value = false;
};

const getStatusVariant = (status) => {
	switch (status) {
		case "success":
			return "success";
		case "failed":
		case "error":
			return "danger";
		case "completed_with_errors":
		case "running":
		case "pending":
			return "warning";
		default:
			return "secondary";
	}
};

const formatScope = (scope) => {
	if (!scope) return "-";
	if (typeof scope === "string") return scope;
	if (Array.isArray(scope)) return scope.join(", ");
	if (scope.table) return `Table: ${scope.table}`;
	if (scope.device_id) return `Device: ${scope.device_id}`;
	return JSON.stringify(scope);
};

const formatMode = (mode) => {
	if (!mode) return "-";
	return formatters.capitalize(mode);
};

const formatStatus = (status) => formatters.capitalize((status || "pending").replaceAll("_", " "));

const deleteSelectedDescription = computed(() => `This will permanently delete ${selectedRows.value.length} purge run history ${selectedRows.value.length === 1 ? "entry" : "entries"}. Only the history record is removed. This action cannot be undone.`);

const formatDryRun = (dryRun) => (dryRun ? "Yes" : "No");

onMounted(() => {
	eventBus.on("deleteManyPurgeRunsSuccess", clearSelection);
});

onUnmounted(() => {
	eventBus.off("deleteManyPurgeRunsSuccess", clearSelection);
});
</script>

<template>
	<div class="flex flex-col h-full gap-1 text-center">
		<div class="flex flex-wrap items-center justify-between gap-3 p-4">
			<div class="flex items-center gap-3">
				<Input class="max-w-sm ml-4 mr-2" autocomplete="off" data-1p-ignore data-lpignore="true" placeholder="Filter scope/ table names..." v-model="searchTerm" />

				<Separator orientation="vertical" class="relative w-px h-6 mx-4 shrink-0 bg-border" />

				<span class="mr-2 text-muted-foreground">Filters:</span>

				<div class="flex gap-2 items-center">
					<Select v-model="statusFilter">
						<SelectTrigger class="w-[170px] focus-visible:ring-0 focus-visible:ring-offset-0 focus:outline-hidden focus:ring-0 focus:ring-offset-0 data-[state=open]:ring-0 data-[state=open]:ring-offset-0">
							<SelectValue placeholder="Status" />
						</SelectTrigger>
						<SelectContent>
							<SelectItem v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.label }}</SelectItem>
						</SelectContent>
					</Select>

					<Select v-model="dryRunFilter">
						<SelectTrigger class="w-[150px] focus-visible:ring-0 focus-visible:ring-offset-0 focus:outline-hidden focus:ring-0 focus:ring-offset-0 data-[state=open]:ring-0 data-[state=open]:ring-offset-0">
							<SelectValue placeholder="Dry run" />
						</SelectTrigger>
						<SelectContent>
							<SelectItem v-for="option in dryRunOptions" :key="option.value" :value="option.value">{{ option.label }}</SelectItem>
						</SelectContent>
					</Select>

					<ClearFilters v-if="searchTerm || statusFilter !== 'all' || dryRunFilter !== 'any'" @update:model-value="clearFilters" />
				</div>
			</div>

			<div class="flex items-center justify-end gap-2">
				<Button v-if="selectedRows.length" class="px-2 py-1 bg-red-600 dark:bg-red-500 text-white dark:bg-red-600 hover:bg-red-600 dark:hover:bg-red-700 dark:hover:bg-red-700 hover:animate-pulse" size="md" @click.prevent="showConfirmDelete = true" variant="primary"> Delete Selected {{ selectedRows.length }} Run(s) </Button>
				<RcIcon name="refresh" class="w-4 h-4 mx-4 text-muted-foreground cursor-pointer hover:text-rcgray-200" @click="reload()" />
			</div>
		</div>

		<div class="px-6">
			<Table>
				<TableHeader>
					<TableRow>
						<TableHead class="w-[2%]">
							<Checkbox id="selectAll" v-model="selectAll" :checked="selectAll" @click="toggleSelectAll()" />
						</TableHead>
						<TableHead class="w-[5%]">
							<Button class="flex justify-start w-full p-0 hover:bg-accent dark:" variant="ghost" @click="toggleSort('id')">
								<RcIcon name="sort" :sortParam="sortParam" field="id" />
								<span class="ml-2">ID</span>
							</Button>
						</TableHead>
						<TableHead class="w-[10%]">
							<Button class="flex justify-start w-full p-0 hover:bg-accent dark:" variant="ghost" @click="toggleSort('type')">
								<RcIcon name="sort" :sortParam="sortParam" field="type" />
								<span class="ml-2">Type</span>
							</Button>
						</TableHead>
						<TableHead class="w-[10%]">
							<Button class="flex justify-start w-full p-0 hover:bg-accent dark:" variant="ghost" @click="toggleSort('mode')">
								<RcIcon name="sort" :sortParam="sortParam" field="mode" />
								<span class="ml-2">Mode</span>
							</Button>
						</TableHead>
						<TableHead class="w-[10%]">
							<Button class="flex justify-start w-full p-0 hover:bg-accent dark:" variant="ghost" @click="toggleSort('status')">
								<RcIcon name="sort" :sortParam="sortParam" field="status" />
								<span class="ml-2">Status</span>
							</Button>
						</TableHead>
						<TableHead class="w-[8%]">
							<Button class="flex justify-start w-full p-0 hover:bg-accent dark:" variant="ghost" @click="toggleSort('dry_run')">
								<RcIcon name="sort" :sortParam="sortParam" field="dry_run" />
								<span class="ml-2">Dry Run</span>
							</Button>
						</TableHead>
						<TableHead class="w-[12%]">Source</TableHead>
						<TableHead class="w-[13%]">Scope/ Table</TableHead>
						<TableHead class="w-[10%]">
							<Button class="flex justify-start w-full p-0 hover:bg-accent dark:" variant="ghost" @click="toggleSort('started_at')">
								<RcIcon name="sort" :sortParam="sortParam" field="started_at" />
								<span class="ml-2">Started</span>
							</Button>
						</TableHead>
						<TableHead class="w-[10%]">
							<Button class="flex justify-start w-full p-0 hover:bg-accent dark:" variant="ghost" @click="toggleSort('finished_at')">
								<RcIcon name="sort" :sortParam="sortParam" field="finished_at" />
								<span class="ml-2">Finished</span>
							</Button>
						</TableHead>
						<TableHead class="w-[8%]">Actions</TableHead>
					</TableRow>
				</TableHeader>
				<TableBody>
					<template v-if="isLoading">
						<Loading />
					</template>

					<template v-else-if="runs?.data?.length">
						<TableRow v-for="row in runs.data" :key="row.id">
							<TableCell class="text-start">
								<Checkbox class="cursor-pointer" :id="'select-' + row.id" :checked="selectedRows.includes(row.id) ? true : false" @click="toggleSelectRow(row.id)" />
							</TableCell>
							<TableCell class="text-start">
								{{ row.id }}
							</TableCell>
							<TableCell class="text-start">
								<PurgeRunHistoryHoverCard :run="row">
									<Button variant="outline" class="h-7 px-2 py-1" title="Hover to view run details">
										<span class="text-xs font-semibold uppercase tracking-wide border-b border-muted-foreground/60 pb-[1px]">{{ row.type }}</span>
									</Button>
								</PurgeRunHistoryHoverCard>
							</TableCell>
							<TableCell class="text-start">
								{{ formatMode(row.mode) }}
							</TableCell>
							<TableCell class="text-start">
								<RcBadge :variant="getStatusVariant(row.status)" :interactive="false">
									{{ formatStatus(row.status) }}
								</RcBadge>
							</TableCell>
							<TableCell class="text-start">
								<RcBadge variant="secondary" :interactive="false" v-if="row.dry_run">
									{{ formatDryRun(row.dry_run) }}
								</RcBadge>
								<RcBadge variant="primary" v-else>{{ formatDryRun(row.dry_run) }}</RcBadge>
							</TableCell>
							<TableCell class="text-start">
								<div class="text-sm text-left">{{ row.source || "-" }}</div>
								<div v-if="row.message" class="text-xs text-muted-foreground truncate max-w-[220px]" :title="row.message">
									{{ row.message }}
								</div>
							</TableCell>
							<TableCell class="text-start">
								<span class="text-sm text-left truncate max-w-[200px]" :title="formatScope(row.scope)">
									{{
										(() => {
											const scope = row.scope;
											try {
												const decoded = typeof scope === "string" ? JSON.parse(scope) : scope;
												return decoded && decoded.table ? decoded.table : formatScope(scope);
											} catch {
												return formatScope(scope);
											}
										})()
									}}
								</span>
							</TableCell>
							<TableCell class="text-start">
								{{ row.started_at ? formatters.formatTime(row.started_at) : "-" }}
							</TableCell>
							<TableCell class="text-start">
								{{ row.finished_at ? formatters.formatTime(row.finished_at) : "-" }}
							</TableCell>
							<TableCell class="text-start">
								<ActionsMenu :showEditBtn="false" :rowData="row" @onDelete="deleteRun(row.id)" />
							</TableCell>
						</TableRow>
					</template>
					<template v-else>
						<NoResults />
					</template>
				</TableBody>
			</Table>

			<Pagination :currentPage="currentPage" :lastPage="lastPage" :perPage="perPage" @update:currentPage="currentPage = $event" @update:perPage="perPage = $event" :totalRecords="runs?.total || 0" :isLoading="isLoading" />

			<!-- Load More Button -->
			<div v-if="isLoadMoreMode && hasMoreData" class="flex justify-center mt-4">
				<Button @click="loadMoreRuns" :disabled="isLoadingMore" variant="outline" class="min-w-[200px]">
					<RcIcon v-if="!isLoadingMore" name="loadmore" class="w-4 h-4 mr-2" />
					<Spinner v-else name="loadmore" class="w-4 h-4 mr-2" />

					{{ isLoadingMore ? "Loading More..." : "Load More" }}
				</Button>
			</div>

			<RcConfirmAlertDialog :ids="selectedRows" title="Delete selected purge runs?" :description="deleteSelectedDescription" :showConfirmDelete="showConfirmDelete" @close="showConfirmDelete = false" @handleDelete="deleteManyRuns(selectedRows)" />
		</div>
	</div>
</template>


