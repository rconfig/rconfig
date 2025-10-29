<script setup>
import ActionsMenu from "@/pages/Shared/Table/ActionsMenu.vue";
import ClearFilters from "@/pages/Shared/Filters/ClearFilters.vue";
import Loading from "@/pages/Shared/Table/Loading.vue";
import NoResults from "@/pages/Shared/Table/NoResults.vue";
import Pagination from "@/pages/Shared/Table/Pagination.vue";
import RcConfirmAlertDialog from "@/pages/Shared/ConfirmAlertDialog/RcConfirmAlertDialog.vue";
import SystemLogsTableHoverCard from "@/pages/Settings/Panels/Components/SystemLogsTableHoverCard.vue";
import SystemLogsTableSeverityFilter from "@/pages/Settings/Panels/Components/SystemLogsTableSeverityFilter.vue";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { eventBus } from "@/composables/eventBus";
import { onMounted } from "vue";
import { useRowSelection } from "@/composables/useRowSelection";
import { useSystemLogs } from "@/pages/Settings/Panels/Components/useSystemLogs";

const { checkLoadMoreMode, clearFilters, currentPage, deleteLog, deleteManyLogs, fetchLogs, filterSeverity, formatters, hasMoreData, isLoading, isLoadingMore, isLoadMoreMode, lastPage, loadMoreLogs, logs, perPage, reload, searchTerm, showConfirmDelete, sortParam, toggleSort } = useSystemLogs();
const { selectedRows, selectAll, toggleSelectAll, toggleSelectRow } = useRowSelection(logs);

const props = defineProps({});

onMounted(() => {
	eventBus.on("deleteManyLogsSuccess", () => {
		selectedRows.value = [];
		selectAll.value = false;
	});
});
</script>

<template>
	<div class="flex flex-col h-full gap-1 text-center">
		<div class="flex items-center justify-between p-4">
			<div class="flex items-center">
				<Input class="max-w-sm ml-4 mr-2" autocomplete="off" data-1p-ignore data-lpignore="true" placeholder="Filter logs..." v-model="searchTerm" />

				<Separator orientation="vertical" class="relative w-px h-6 mx-4 shrink-0 bg-border" />

				<span class="mr-2 text-muted-foreground">Filters</span>
				<!-- FILTERS -->

				<div class="flex gap-2">
					<SystemLogsTableSeverityFilter v-model="filterSeverity" />

					<ClearFilters v-if="searchTerm || filterSeverity.length" @update:model-value="clearFilters" />
				</div>
			</div>
			<div class="flex items-center justify-end">
				<Button v-if="selectedRows.length" class="px-2 py-1 bg-red-600 hover:bg-red-700 hover:animate-pulse" size="md" @click.prevent="showConfirmDelete = true" variant="primary"> Delete Selected {{ selectedRows.length }} Logs </Button>
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
							<Button class="flex justify-start w-full p-0 hover:bg-rcgray-800" variant="ghost" @click="toggleSort('id')">
								<RcIcon name="sort" :sortParam="sortParam" field="id" />
								<span class="ml-2">ID</span>
							</Button>
						</TableHead>
						<TableHead class="w-[5%]">
							<Button class="flex justify-start w-full p-0 hover:bg-rcgray-800" variant="ghost" @click="toggleSort('log_name')">
								<RcIcon name="sort" :sortParam="sortParam" field="log_name" />
								<span class="ml-2">Severity</span>
							</Button>
						</TableHead>
						<TableHead class="w-[50%]">Description</TableHead>
						<TableHead class="w-[10%]">Event Time</TableHead>
						<TableHead class="w-[10%]">Actions</TableHead>
					</TableRow>
				</TableHeader>
				<TableBody>
					<template v-if="isLoading">
						<Loading />
					</template>

					<template v-else-if="!isLoading">
						<TableRow v-for="row in logs.data" :key="row.id">
							<TableCell class="text-start">
								<Checkbox class="cursor-pointer" :id="'select-' + row.id" :checked="selectedRows.includes(row.id) ? true : false" @click="toggleSelectRow(row.id)" />
							</TableCell>
							<TableCell class="text-start">
								{{ row.id }}
							</TableCell>
							<TableCell>
								<SystemLogsTableHoverCard :log="row">
									<Button variant="outline" class="h-6 p-1">
										<div class="flex items-center" :class="formatters.getLogLevelClass(row.log_name)" v-if="row.log_name === 'warn'">
											<RcIcon name="log-severity" :severity="'info'" size="sm" />
											{{ formatters.capitalizeFirstLetter(row.log_name) }}
										</div>
										<div class="flex items-center" :class="formatters.getLogLevelClass(row.log_name)" v-if="row.log_name === 'info'">
											<RcIcon name="log-severity" :severity="row.log_name" size="sm" />
											{{ formatters.capitalizeFirstLetter(row.log_name) }}
										</div>
										<div class="flex items-center" :class="formatters.getLogLevelClass(row.log_name)" v-else-if="row.log_name === 'error'">
											<RcIcon name="log-severity" :severity="row.log_name" size="sm" />
											{{ formatters.capitalizeFirstLetter(row.log_name) }}
										</div>
										<div class="flex items-center" :class="formatters.getLogLevelClass(row.log_name)" v-else-if="row.log_name === 'critical' || row.log_name === 'crit'">
											<RcIcon name="log-severity" :severity="row.log_name" size="sm" />
											{{ formatters.capitalizeFirstLetter(row.log_name) }}
										</div>
										<div class="flex items-center" :class="formatters.getLogLevelClass(row.log_name)" v-else-if="row.log_name === 'debug'">
											<RcIcon name="log-severity" :severity="row.log_name" size="sm" />
											{{ formatters.capitalizeFirstLetter(row.log_name) }}
										</div>
									</Button>
									<template v-slot:leftIcon>
										<template v-if="row.log_name === 'warn'">
											<RcIcon name="log-severity" :severity="row.log_name" size="md" />
										</template>
										<template v-else-if="row.log_name === 'info'">
											<RcIcon name="log-severity" :severity="row.log_name" size="md" />
										</template>
										<template v-else-if="row.log_name === 'error'">
											<RcIcon name="log-severity" :severity="row.log_name" size="md" />
										</template>
										<template v-else-if="row.log_name === 'critical' || row.log_name === 'crit'">
											<RcIcon name="log-severity" :severity="row.log_name" size="md" />
										</template>
										<template v-else-if="row.log_name === 'debug'">
											<RcIcon name="log-severity" :severity="row.log_name" size="md" />
										</template>
									</template>
								</SystemLogsTableHoverCard>
							</TableCell>
							<TableCell class="text-start">
								{{ row.description }}
							</TableCell>
							<TableCell class="text-start">
								{{ formatters.formatTime(row.created_at) }}
							</TableCell>
							<!-- ACTIONS MENU -->
							<TableCell class="text-start">
								<ActionsMenu :showEditBtn="false" :rowData="row" @onDelete="deleteLog(row.id)" />
							</TableCell>
							<!-- ACTIONS MENU -->
						</TableRow>
					</template>
					<template v-else>
						<NoResults />
					</template>
				</TableBody>
			</Table>

			<Pagination :currentPage="currentPage" :lastPage="lastPage" :perPage="perPage" @update:currentPage="currentPage = $event" @update:perPage="perPage = $event" :totalRecords="logs.total" :isLoading="isLoading" />

			<!-- Load More Button -->
			<div v-if="isLoadMoreMode && hasMoreData" class="flex justify-center mt-4">
				<Button @click="loadMoreLogs" :disabled="isLoadingMore" variant="outline" class="min-w-[200px]">
					<RcIcon v-if="isLoadingMore" name="spinner" class="w-4 h-4 mr-2 animate-spin" />
					{{ isLoadingMore ? "Loading more..." : "Load More" }}
				</Button>
			</div>

			<RcConfirmAlertDialog :ids="selectedRows" :showConfirmDelete="showConfirmDelete" @close="showConfirmDelete = false" @handleDelete="deleteManyLogs(selectedRows)" />
		</div>
	</div>
</template>