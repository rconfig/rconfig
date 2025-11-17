<script setup>
import ActionsMenu from "@/pages/Shared/Table/ActionsMenu.vue";
import ClearFilters from "@/pages/Shared/Filters/ClearFilters.vue";
import RcConfirmAlertDialog from "@/pages/Shared/ConfirmAlertDialog/RcConfirmAlertDialog.vue";
import Loading from "@/pages/Shared/Table/Loading.vue";
import NoResults from "@/pages/Shared/Table/NoResults.vue";
import Pagination from "@/pages/Shared/Table/Pagination.vue";
import ReportViewModal from "@/pages/Configs/ReportViewModal.vue";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { onMounted } from "vue";
import { useRowSelection } from "@/composables/useRowSelection";
import { useConfigReportsTable } from "@/pages/Configs/useConfigReportsTable";
import { eventBus } from "@/composables/eventBus";

const {
	// State
	reports,
	isLoading,
	currentPage,
	perPage,
	lastPage,
	totalRecords,
	searchTerm,
	showConfirmDelete,
	reload,
	sortParam,

	// Modal State
	isReportViewModalOpen,
	currentReportId,

	// Dialogs & Actions
	deleteReport,
	deleteManyReports,
	viewEditDialog,
	toggleSort,
	openReportViewModal,
} = useConfigReportsTable();
const { selectedRows, selectAll, toggleSelectAll, toggleSelectRow } = useRowSelection(reports);

onMounted(() => {
	eventBus.on("deleteManyReportsSuccess", () => {
		selectedRows.value = [];
		selectAll.value = false;
	});
});
</script>

<template>
	<div class="flex flex-col h-full gap-1 text-center">
		<div class="flex items-center justify-between p-4">
			<div class="flex items-center">
				<Input class="max-w-sm ml-4 mr-2" autocomplete="off" data-1p-ignore data-lpignore="true" placeholder="Filter reports" v-model="searchTerm" />
				<ClearFilters v-if="searchTerm" @update:model-value="searchTerm = ''" />
			</div>
			<div class="flex items-center justify-end">
				<Button v-if="selectedRows.length" class="px-2 py-1 bg-red-600 hover:bg-red-700 hover:animate-pulse" size="md" @click.prevent="showConfirmDelete = true" variant="primary">Delete Selected {{ selectedRows.length }} Report(s) </Button>

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
						<TableHead class="w-[20%]">
							<Button class="flex justify-start w-full p-0 hover:bg-rcgray-800" variant="ghost" @click="toggleSort('report_name')">
								<RcIcon name="sort" :sortParam="sortParam" field="report_name" />
								<span class="ml-2">Name</span>
							</Button>
						</TableHead>
						<TableHead class="w-[10%]">Task ID</TableHead>
						<TableHead class="w-[10%]">Task Name</TableHead>
						<TableHead class="w-[10%]">
							<Button class="flex justify-start w-full p-0 hover:bg-rcgray-800" variant="ghost" @click="toggleSort('created_at')">
								<RcIcon name="sort" :sortParam="sortParam" field="created_at" />
								<span class="ml-2">Created</span>
							</Button>
						</TableHead>
						<TableHead class="w-[10%]">Actions</TableHead>
					</TableRow>
				</TableHeader>
				<TableBody>
					<template v-if="isLoading">
						<Loading />
					</template>

					<template v-else-if="!isLoading">
						<TableRow v-for="row in reports.data" :key="row.id">
							<TableCell class="text-start">
								<Checkbox class="cursor-pointer" :id="'select-' + row.id" :checked="selectedRows.includes(row.id) ? true : false" @click="toggleSelectRow(row.id)" />
							</TableCell>
							<TableCell class="text-start">
								{{ row.id }}
							</TableCell>
							<TableCell class="text-start">
								<Button class="px-2 py-0 hover:bg-rcgray-800 rounded-xl" variant="ghost" @click="openReportViewModal(row.report_id)">
									<span class="border-b">{{ row.report_name }}</span>
								</Button>
							</TableCell>
							<TableCell class="text-start">
								{{ row.task_id }}
							</TableCell>
							<TableCell class="text-start">
								{{ row.task_name }}
							</TableCell>
							<TableCell class="text-start">
								{{ row.created_at }}
							</TableCell>
							<!-- ACTIONS MENU -->
							<TableCell class="text-start">
								<ActionsMenu :showEditBtn="false" :rowData="row" @onDelete="deleteReport(row.id)" />
							</TableCell>
							<!-- ACTIONS MENU -->
						</TableRow>
					</template>
					<template v-else>
						<NoResults />
					</template>
				</TableBody>
			</Table>

			<Pagination :currentPage="currentPage" :lastPage="lastPage" :perPage="perPage" @update:currentPage="currentPage = $event" @update:perPage="perPage = $event" :totalRecords="totalRecords" :isLoading="isLoading" />
			<RcConfirmAlertDialog :ids="selectedRows" :showConfirmDelete="showConfirmDelete" @close="showConfirmDelete = false" @handleDelete="deleteManyReports(selectedRows)" />
			<ReportViewModal v-if="currentReportId" :report_id="currentReportId" :isOpen="isReportViewModalOpen" @update:isOpen="isReportViewModalOpen = $event" />
		</div>
	</div>
</template>
