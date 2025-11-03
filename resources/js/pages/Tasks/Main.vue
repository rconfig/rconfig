<script setup>
import ActionsMenu from "@/pages/Shared/Table/ActionsMenu.vue";
import BadgeList from "@/pages/Shared/Table/BadgeList.vue";
import ClearFilters from "@/pages/Shared/Filters/ClearFilters.vue";
import DialogViewHistory from "@/pages/Tasks/TasksHistoryDetails/TaskHistoryDialog.vue";
import Loading from "@/pages/Shared/Table/Loading.vue";
import NoResults from "@/pages/Shared/Table/NoResults.vue";
import Pagination from "@/pages/Shared/Table/Pagination.vue";
import RcConfirmAlertDialog from "@/pages/Shared/ConfirmAlertDialog/RcConfirmAlertDialog.vue";
import TaskAddEditDialog from "@/pages/Tasks/TaskAddEditDialog.vue";
import { Clock } from "lucide-vue-next";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { eventBus } from "@/composables/eventBus";
import { onMounted, onUnmounted, ref, watch } from "vue";
import { useRowSelection } from "@/composables/useRowSelection";
import { useTasks } from "@/pages/Tasks/useTasks";

const { reload, createTask, currentPage, deleteTask, editId, historyTaskId, fetchTasks, formatters, handleKeyDown, handleSave, isLoading, lastPage, newTaskModalKey, openDialog, pauseTask, perPage, proceedEditId, runTaskConfirm, runTaskNow, searchTerm, showConfirmDelete, deleteManyTasks, showConfirmConfirmProceedAlertAlert, sortParam, tasks, toggleSort, updateTask, viewEditDialog, viewHistoryDialog, filters } = useTasks();
const { selectedRows, selectAll, toggleSelectAll, toggleSelectRow } = useRowSelection(tasks);

// Task type filter - Core only has 3 download types
const taskTypeFilter = ref("all");
const taskTypeOptions = [
	{ value: "all", label: "All Tasks" },
	{ value: "rconfig:download-device", label: "Download by Device" },
	{ value: "rconfig:download-category", label: "Download by Category" },
	{ value: "rconfig:download-tag", label: "Download by Tag" },
];

// Watch for filter changes and update task_command filter
watch(taskTypeFilter, (newValue) => {
	if (newValue === "all") {
		delete filters.value["filter[task_command]"];
	} else {
		filters.value["filter[task_command]"] = newValue;
	}
	fetchTasks();
});

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
			<div class="flex items-center gap-2">
				<Input class="max-w-sm ml-4 mr-2" autocomplete="off" data-1p-ignore data-lpignore="true" placeholder="Filter tasks..." v-model="searchTerm" />
				<Select v-model="taskTypeFilter">
					<SelectTrigger class="w-48 focus:ring-0 focus:ring-offset-0">
						<SelectValue placeholder="Filter task type" />
					</SelectTrigger>
					<SelectContent>
						<SelectItem v-for="option in taskTypeOptions" :key="option.value" :value="option.value">
							{{ option.label }}
						</SelectItem>
					</SelectContent>
				</Select>
				<ClearFilters
					v-if="searchTerm || taskTypeFilter !== 'all'"
					@update:model-value="
						searchTerm = '';
						taskTypeFilter = 'all';
						delete filters['filter[task_command]'];
						fetchTasks();
					"
				/>
			</div>
			<div class="flex items-center justify-end">
				<Button v-if="selectedRows.length" @click.prevent="showConfirmDelete = true" class="px-2 py-1 bg-red-600 hover:bg-red-700 hover:animate-pulse" size="md" variant="primary"> Delete Selected {{ selectedRows.length }} Task(s) </Button>

				<Button type="submit" class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse" size="sm" @click.prevent="createTask" variant="primary">
					New Task
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
						<TableHead class="w-[2%]">Status</TableHead>
						<TableHead class="w-[15%]">
							<Button class="flex justify-start w-full p-0 hover:bg-rcgray-800" variant="ghost" @click="toggleSort('task_name')">
								<RcIcon name="sort" :sortParam="sortParam" field="task_name" />
								<span class="ml-2">Name</span>
							</Button>
						</TableHead>
						<TableHead class="w-[15%]">Description</TableHead>
						<TableHead class="w-[10%]">Command</TableHead>
						<TableHead class="w-[8%]">Targets</TableHead>
						<TableHead class="w-[8%]">Target Values</TableHead>
						<TableHead class="w-[10%]">Frequency</TableHead>
						<TableHead class="w-[8%]">Notifications</TableHead>
						<TableHead class="w-[10%]">
							<Button class="flex justify-start w-full p-0 hover:bg-rcgray-800" variant="ghost" @click="toggleSort('created_at')">
								<RcIcon name="sort" :sortParam="sortParam" field="created_at" />
								<span class="ml-2">Created</span>
							</Button>
						</TableHead>
						<TableHead class="w-[5%]">Actions</TableHead>
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
							<TableCell class="text-start" :title="row.is_paused ? `Task ${row.id} is paused` : `Task ${row.id} is active`">
								<div class="flex items-center gap-2">
									<RcIcon name="pulsing" :size="16" color="#f59e0b" :animate="true" speed="3s" v-if="row.is_paused" />
									<RcIcon name="spinning-task" :size="16" color="#4ade80" :animate="true" speed="2s" v-else />
									<span class="text-xs text-muted-foreground" v-if="row.is_system">System</span>
								</div>
							</TableCell>
							<TableCell class="text-start">
								<div class="flex flex-col">
									<Button class="px-2 py-0 text-sm hover:bg-rcgray-800 rounded-xl justify-start" variant="ghost" @click="viewHistoryDialog(row.id)">
										<span class="border-b font-medium">{{ row.task_name }}</span>
									</Button>
									<span class="text-xs text-muted-foreground ml-2">ID: {{ row.id }}</span>
								</div>
							</TableCell>
							<TableCell class="text-start">
								<div class="max-w-xs">
									<span class="text-sm truncate block" :title="row.task_desc">{{ row.task_desc || "-" }} </span>
								</div>
							</TableCell>
							<TableCell class="text-start">
								<div class="flex items-center gap-1">
									<RcIcon name="commands" :width="16" class="w-4 h-4 text-muted-foreground flex-shrink-0 mr-1" />
									<span class="text-xs font-mono bg-muted px-2 py-1 rounded">{{ row.task_command?.replace("rconfig:", "") || "-" }}</span>
								</div>
							</TableCell>
							<TableCell class="text-start">
								<div class="flex flex-col gap-1">
									<div v-if="row.device && row.device.length > 0" class="flex items-center gap-1">
										<RcIcon name="device" :width="14" class="text-blue-400" />
										<span class="text-xs">{{ row.device.length }} device(s)</span>
									</div>
									<div v-if="row.category && row.category.length > 0" class="flex items-center gap-1">
										<RcIcon name="command-group" :width="14" class="mr-1" />
										<span class="text-xs">{{ row.category.length }} group(s)</span>
									</div>
									<div v-if="row.tag && row.tag.length > 0" class="flex items-center gap-1">
										<RcIcon name="tag" :width="14" />
										<span class="text-xs">{{ row.tag.length }} tag(s)</span>
									</div>
									<div v-if="row.task_command && row.task_command.includes('download')" class="flex items-center gap-1">
										<RcIcon name="download2" :width="14" class="text-teal-400" />
										<span class="text-xs">Config Backup</span>
									</div>
									<span v-if="(!row.device || row.device.length === 0) && !row.category && !row.tag" class="text-xs text-muted-foreground">No targets</span>
								</div>
							</TableCell>
							<TableCell class="text-start">
								<div v-if="row.device && row.device.length > 0" class="flex items-center gap-1 mb-1">
									<BadgeList :items="row.device" displayField="device_name" linkField="view_url" :maxVisible="4" :hoverCardFields="['id', 'device_name', 'device_ip']" :showIcon="true" :iconName="'device'" />
								</div>

								<div v-if="row.category && row.category.length > 0" class="flex items-center gap-1">
									<BadgeList :items="row.category" displayField="categoryName" linkField="" :maxVisible="4" :hoverCardFields="['id', 'categoryName']" :showIcon="true" :iconName="'command-group'" />
								</div>

								<div v-if="row.tag && row.tag.length > 0" class="flex items-center gap-1">
									<BadgeList :items="row.tag" displayField="tagname" linkField="" :maxVisible="4" :hoverCardFields="['id', 'tagname']" :showIcon="true" :iconName="'tag'" />
								</div>
								<div v-else class="text-xs text-muted-foreground"></div>
							</TableCell>
							<TableCell class="text-start">
								<div class="flex items-center gap-1">
									<Clock class="w-4 h-4 text-muted-foreground flex-shrink-0 mr-1" />
									<span class="text-sm">{{ row.cron_plain }}</span>
								</div>
							</TableCell>
							<TableCell class="text-start">
								<div class="flex flex-col gap-1">
									<div v-if="row.task_email_notify" class="flex items-center gap-1">
										<RcIcon name="email" :width="12" class="text-blue-400" />
										<span class="text-xs">Email</span>
									</div>
									<div v-if="row.download_report_notify" class="flex items-center gap-1">
										<RcIcon name="docs" :width="14" class="text-green-400" />
										<span class="text-xs">Report</span>
									</div>
									<div v-if="row.verbose_download_report_notify" class="flex items-center gap-1">
										<RcIcon name="docs" :width="14" class="text-yellow-400" />
										<span class="text-xs">Verbose</span>
									</div>
									<span v-if="!row.task_email_notify && !row.download_report_notify && !row.verbose_download_report_notify" class="text-xs text-muted-foreground">None</span>
								</div>
							</TableCell>
							<TableCell class="text-start">
								<div class="text-xs text-muted-foreground">
									{{ row.created_at ? formatters.formatTime(row.created_at) : "-" }}
								</div>
							</TableCell>
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
			<RcConfirmAlertDialog :showConfirmConfirmProceedAlertAlert="showConfirmConfirmProceedAlertAlert" :editId="proceedEditId" @handleClose="showConfirmConfirmProceedAlertAlert = false" @handleConfirm="runTaskNow(proceedEditId)" message="Are you sure you want to run this task manually?" />
			<RcConfirmAlertDialog :ids="selectedRows" :showConfirmDelete="showConfirmDelete" @close="showConfirmDelete = false" @handleDelete="deleteManyTasks(selectedRows)" />
		</div>
	</div>
</template>