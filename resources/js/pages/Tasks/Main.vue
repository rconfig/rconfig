<script setup>
import ActionsMenu from "@/pages/Shared/Table/ActionsMenu.vue";
import ClearFilters from "@/pages/Shared/Filters/ClearFilters.vue";
import Loading from "@/pages/Shared/Table/Loading.vue";
import NoResults from "@/pages/Shared/Table/NoResults.vue";
import Pagination from "@/pages/Shared/Table/Pagination.vue";
import RcConfirmAlertDialog from "@/pages/Shared/ConfirmAlertDialog/RcConfirmAlertDialog.vue";
import TaskAddEditDialog from "@/pages/Tasks/TaskAddEditDialog.vue";
import DialogViewHistory from "@/pages/Tasks/TasksHistoryDetails/TaskHistoryDialog.vue";
import TasksMainI18N from "@/i18n/pages/Tasks/Main.i18n.js";
import { Pause, RotateCw, Clock } from "lucide-vue-next";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { eventBus } from "@/composables/eventBus";
import { onMounted, onUnmounted } from "vue";
import { useComponentTranslations } from "@/composables/useComponentTranslations";
import { useRowSelection } from "@/composables/useRowSelection";
import { useTasks } from "@/pages/Tasks/useTasks";

const { t } = useComponentTranslations(TasksMainI18N);
const { reload, createTask, currentPage, deleteTask, editId, historyTaskId, fetchTasks, formatters, handleKeyDown, handleSave, isLoading, lastPage, newTaskModalKey, openDialog, pauseTask, perPage, proceedEditId, runTaskConfirm, runTaskNow, searchTerm, showConfirmDelete, deleteManyTasks, showConfirmConfirmProceedAlertAlert, sortParam, tasks, toggleSort, updateTask, viewEditDialog, viewHistoryDialog } = useTasks();
const { selectedRows, selectAll, toggleSelectAll, toggleSelectRow } = useRowSelection(tasks);

onMounted(() => {
	fetchTasks();
	window.addEventListener("keydown", handleKeyDown);

	eventBus.on("deleteManyTagsSuccess", () => {
		selectedRows.value = [];
		selectAll.value = false;
	});
});

// Cleanup event listener on unmount
onUnmounted(() => {
	window.removeEventListener("keydown", handleKeyDown);
});
</script>

<template>
	<div class="flex flex-col h-full gap-1 text-center">
		<div class="flex items-center justify-between p-4">
			<div class="flex items-center">
				<Input class="max-w-sm ml-4 mr-2" autocomplete="off" data-1p-ignore data-lpignore="true" :placeholder="t('filterTasks')" v-model="searchTerm" />
				<ClearFilters v-if="searchTerm" @update:model-value="searchTerm = ''" />
			</div>
			<div class="flex items-center justify-end">
				<Button v-if="selectedRows.length" @click.prevent="showConfirmDelete = true" class="px-2 py-1 bg-red-600 hover:bg-red-700 hover:animate-pulse" size="md" variant="primary"> {{ t("common.delete") }} {{ t("common.selected") }} {{ selectedRows.length }} {{ t("task") }}(s) </Button>

				<Button type="submit" class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse" size="sm" @click.prevent="createTask" variant="primary">
					{{ t("newTask") }}
					<div class="pl-2 ml-auto">
						<kbd class="rc-kdb-class2">ALT N</kbd>
					</div>
				</Button>
				<RcIcon name="refresh" class="w-4 h-4 mx-4 text-muted-foreground cursor-pointer hover:text-rcgray-200" @click="reload()" />
			</div>
		</div>

		<div class="px-6">
			<Table>
				<TableHeader>
					<TableRow>
						<TableHead class="w-[2%]">
							<Checkbox id="selectAll" v-model="selectAll" @click="toggleSelectAll()" />
						</TableHead>
						<TableHead class="w-[2%]">{{ t("common.status") }}</TableHead>
						<TableHead class="w-[5%]">
							<Button class="flex justify-start w-full p-0 hover:bg-rcgray-800" variant="ghost" @click="toggleSort('id')">
								<RcIcon name="sort" :sortParam="sortParam" field="id" />
								<span class="ml-2">{{ t("common.id") }}</span>
							</Button>
						</TableHead>
						<TableHead class="w-[5%]">
							<Button class="flex justify-start w-full p-0 hover:bg-rcgray-800" variant="ghost" @click="toggleSort('task_name')">
								<RcIcon name="sort" :sortParam="sortParam" field="task_name" />
								<span class="ml-2">{{ t("common.name") }}</span>
							</Button>
						</TableHead>

						<TableHead class="w-[10%]">{{ t("frequency") }}</TableHead>
						<!-- <TableHead class="w-[10%]">Last Run</TableHead> -->
						<TableHead class="w-[2%]">{{ t("common.actions") }}</TableHead>
					</TableRow>
				</TableHeader>
				<TableBody>
					<template v-if="isLoading">
						<Loading />
					</template>

					<template v-else-if="!isLoading">
						<TableRow :class="row.is_paused ? 'bg-rcgray-800 hover:bg-rcgray-800' : ''" v-for="row in tasks.data" :key="row.id">
							<TableCell class="text-start">
								<Checkbox class="cursor-pointer" :id="'select-' + row.id" :checked="selectedRows.includes(row.id) ? true : false" @click="toggleSelectRow(row.id)" />
							</TableCell>
							<TableCell class="text-start" :title="row.is_paused ? t('taskIsPaused', { id: row.id }) : t('taskIsActive', { id: row.id })">
								<RcIcon name="pulsing" :size="16" color="#f59e0b" :animate="true" speed="3s" v-if="row.is_paused" />
								<RcIcon name="spinning-task" :size="16" color="#4ade80" :animate="true" speed="2s" v-else />
							</TableCell>
							<TableCell class="text-start">
								{{ row.id }}
							</TableCell>
							<TableCell class="text-start">
								<Button class="px-2 py-0 text-sm hover:bg-rcgray-800 rounded-xl" variant="ghost" @click="viewHistoryDialog(row.id)">
									<span class="border-b">{{ row.task_name }}</span>
								</Button>
							</TableCell>
							<TableCell class="text-start">
								{{ row.cron_plain }}
							</TableCell>
							<!-- <TableCell class="text-start">
                {{ formatters.formatTime(row.last_finished_at) }}
              </TableCell> -->
							<!-- ACTIONS MENU -->
							<TableCell class="text-start">
								<ActionsMenu :rowData="row" :showTaskPauseBtn="true" :showViewHistoryBtn="true" :taskPaused="Boolean(row.is_paused)" :showTaskRunNowBtn="true" @onRunManualTask="runTaskConfirm(row.id)" @onTaskPause="pauseTask(row.id)" @onViewHistory="viewHistoryDialog(row.id)" @onEdit="viewEditDialog(row.id)" @onDelete="deleteTask(row.id)" />
							</TableCell>
							<!-- ACTIONS MENU -->
						</TableRow>
					</template>
					<template v-else>
						<NoResults />
					</template>
				</TableBody>
			</Table>

			<Pagination :currentPage="currentPage" :lastPage="lastPage" :perPage="perPage" @update:currentPage="currentPage = $event" @update:perPage="perPage = $event" :totalRecords="tasks.total" :isLoading="isLoading" />
			<TaskAddEditDialog @save="handleSave()" :key="newTaskModalKey" :editId="editId" />
			<DialogViewHistory :taskId="historyTaskId" />
			<RcConfirmAlertDialog :showConfirmConfirmProceedAlertAlert="showConfirmConfirmProceedAlertAlert" :editId="proceedEditId" @handleClose="showConfirmConfirmProceedAlertAlert = false" @handleConfirm="runTaskNow(proceedEditId)" :message="t('runTaskManually')" />
			<RcConfirmAlertDialog :ids="selectedRows" :showConfirmDelete="showConfirmDelete" @close="showConfirmDelete = false" @handleDelete="deleteManyTasks(selectedRows)" />
		</div>
	</div>
</template>
