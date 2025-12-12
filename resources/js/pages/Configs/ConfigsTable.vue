<script setup>
import ActionsMenu from "@/pages/Shared/Table/ActionsMenu.vue";
import ClearFilters from "@/pages/Shared/Filters/ClearFilters.vue";
import CommandFilter from "@/pages/Configs/Filters/CommandFilter.vue";
import ConfigSearchFilterCardDateRangePicker from "@/pages/Configs/ConfigSearch/ConfigSearchFilterCardDateRangePicker.vue";
import Loading from "@/pages/Shared/Table/Loading.vue";
import NoResults from "@/pages/Shared/Table/NoResults.vue";
import Pagination from "@/pages/Shared/Table/Pagination.vue";
import PeekConfigDialog from "@/pages/Shared/Dialogs/PeekConfigDialog.vue";
import RcConfirmAlertDialog from "@/pages/Shared/ConfirmAlertDialog/RcConfirmAlertDialog.vue";
import StatusFilter from "@/pages/Configs/Filters/StatusFilter.vue";
import RcToolTip from "@/pages/Shared/Tooltips/RcToolTip.vue";
import { FileText as FileView, FileClock, PackageCheck } from "lucide-vue-next";
import { Switch } from "@/components/ui/switch";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { eventBus } from "@/composables/eventBus";
import { onMounted, onUnmounted } from "vue";
import { useConfigsTable } from "@/pages/Configs/useConfigsTable";
import { useRoute } from "vue-router";
import { useRowSelection } from "@/composables/useRowSelection";
import BadgeList from "@/pages/Shared/Table/BadgeList.vue";
import DataPurgeButton from "@/pages/Shared/Buttons/DataPurgeButton.vue";

const props = defineProps({
	configsId: {
		type: [String, Number],
		default: 0,
	},
	statusId: {
		type: [String, Number],
		default: 0,
	},
});
const route = useRoute();

const {
	// State
	configs,
	currentPage,
	perPage,
	lastPage,
	isLoading,
	isLoadingMore,
	hasMoreData,
	isLoadMoreMode,
	showConfirmDelete,
	searchTerm,
	filters,
	filterStatus,
	filterCommand,
	sortParam,
	showDownloadedConfigs,
	formatters,

	// Methods
	isDialogOpen,
	clearFilters,
	openDialog,
	deleteConfig,
	deleteManyConfigs,
	getTabledata,
	handleKeyDown,
	loadMoreConfigs,
	reload,
	setDates,
	toggleDownloaded,
	toggleLatestVersion,
	toggleSort,
	viewDetailsPane,
} = useConfigsTable(props);
const { selectedRows, selectAll, toggleSelectAll, toggleSelectRow } = useRowSelection(configs);

onMounted(() => {
	if (props.statusId) {
		filterStatus.value = [{ id: props.statusId }];
	}
	getTabledata();

	window.addEventListener("keydown", handleKeyDown);

	eventBus.on("deleteManyConfigsSuccess", () => {
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
			<div class="flex items-center mr-2">
				<Input class="max-w-sm ml-4 hidden xl:inline-flex" autocomplete="off" data-1p-ignore data-lpignore="true" placeholder="Filter [id, device_name]" v-model="searchTerm" />
				<Separator orientation="vertical" class="relative w-px h-6 mx-4 shrink-0 bg-border hidden xl:inline-flex" />

				<div class="flex gap-2">
					<ConfigSearchFilterCardDateRangePicker :use-default-range="false" placeholder="Select date range" @date-change="setDates($event)" :width="'200px'" />
					<CommandFilter v-model="filterCommand" :deviceId="configsId" />
					<StatusFilter v-model="filterStatus" />
					<ClearFilters v-if="Object.keys(filters).length > 0 || searchTerm || filterStatus.length || filterCommand.length" @update:model-value="clearFilters" />

					<Separator orientation="vertical" class="relative w-px h-6 mx-4 shrink-0 bg-border" />

					<div class="flex items-center">
						<RcToolTip :delayDuration="100" content="Latest configs only" :side="'bottom'">
							<template #trigger>
								<FileClock size="16" class="mr-2" />
							</template>
						</RcToolTip>
						<Switch id="airplane-mode" @update:checked="toggleLatestVersion" />
					</div>

					<div class="flex items-center">
						<RcToolTip :delayDuration="100" content="Downloaded configs only" :side="'bottom'">
							<template #trigger>
								<PackageCheck size="16" class="mr-2" />
							</template>
						</RcToolTip>

						<Switch id="airplane-mode" @update:checked="toggleDownloaded" />
					</div>
				</div>
			</div>

			<div class="flex items-center justify-end">
				<Button v-if="selectedRows.length" class="px-2 py-1 bg-red-600 hover:bg-red-700 hover:animate-pulse" size="md" @click.prevent="showConfirmDelete = true" variant="primary">Delete Selected {{ selectedRows.length }} Config(s) </Button>

				<DataPurgeButton :srcName="'configs'" />

				<RcIcon name="refresh" class="w-4 h-4 mx-4 text-muted-foreground cursor-pointer hover:text-rcgray-200" @click="reload()" />
			</div>
		</div>

		<div class="px-6">
			<Table>
				<TableHeader>
					<TableRow>
						<TableHead class="w-[2%] rc-th-heading">
							<Checkbox id="selectAll" v-model="selectAll" :checked="selectAll" @click="toggleSelectAll()" />
						</TableHead>
						<TableHead class="w-[2%] rc-th-heading">
							<Button class="flex justify-start w-full p-0 hover:bg-rcgray-800" variant="ghost" @click="toggleSort('id')">
								<RcIcon name="sort" :sortParam="sortParam" field="id" />
								<span class="ml-2">ID</span>
							</Button>
						</TableHead>
						<TableHead class="w-[2%] rc-th-heading">
							<Button class="flex justify-start w-full p-0 hover:bg-rcgray-800" variant="ghost" @click="toggleSort('download_status')">
								<RcIcon name="sort" :sortParam="sortParam" field="download_status" />
								<span class="ml-2"></span>
							</Button>
						</TableHead>
						<TableHead class="w-[10%] rc-th-heading">
							<Button class="flex justify-start w-full p-0 hover:bg-rcgray-800" variant="ghost" @click="toggleSort('device_name')">
								<RcIcon name="sort" :sortParam="sortParam" field="device_name" />
								<span class="ml-2">Filename</span>
							</Button>
						</TableHead>
						<TableHead class="w-[15%] rc-th-heading">
							<Button class="flex justify-start w-full p-0 hover:bg-rcgray-800" variant="ghost" @click="toggleSort('command')">
								<RcIcon name="sort" :sortParam="sortParam" field="command" />
								<span class="ml-2">Command</span>
							</Button>
						</TableHead>
						<TableHead class="w-[10%] rc-th-heading">Device</TableHead>
						<TableHead class="w-[10%] rc-th-heading">File Size</TableHead>
						<TableHead class="w-[10%] rc-th-heading">Downloaded</TableHead>
						<TableHead class="w-[10%] rc-th-heading"></TableHead>
					</TableRow>
				</TableHeader>
				<TableBody>
					<template v-if="isLoading">
						<Loading />
					</template>

					<template v-else-if="!isLoading">
						<TableRow v-for="row in configs" :key="row.id" :class="{ 'rc-text-sm-muted': row.config_downloaded === 0 }" class="table-row-fixed">
							<TableCell class="text-start table-cell-fixed">
								<Checkbox class="cursor-pointer" :id="'select-' + row.id" :checked="selectedRows.includes(row.id) ? true : false" @click="toggleSelectRow(row.id)" />
							</TableCell>
							<TableCell class="text-start table-cell-fixed">
								{{ row.id }}
							</TableCell>
							<TableCell class="text-start table-cell-fixed">
								<RcIcon name="status-red" v-if="row.download_status === 0" />
								<RcIcon name="status-green" v-if="row.download_status === 1" />
								<RcIcon name="status-yellow" v-if="row.download_status === 2" />
								<RcIcon name="status-gray" v-if="row.download_status === 100" />
							</TableCell>
							<TableCell class="text-start table-cell-fixed">
								<Button v-if="row.config_filename" class="px-2 py-0 hover:bg-rcgray-800 rounded-xl button-cell-fixed" variant="ghost" @click="viewDetailsPane(row.id)">
									<span class="border-b text-truncate" :title="row.config_filename">{{ row.config_filename }}</span>
								</Button>
								<span v-else class="text-truncate">Not Downloaded - unchanged</span>
							</TableCell>
							<TableCell class="text-start table-cell-fixed">
								<span class="text-truncate" :title="row.command">{{ row.command }}</span>
							</TableCell>
							<TableCell class="text-start table-cell-fixed">
								<span class="text-truncate" :title="row.device_name"> <BadgeList :items="[{ id: row.device_id, device_name: row.device_name, view_url: 'device/view/' + row.device_id }]" displayField="device_name" linkField="view_url" :maxVisible="2" :hoverCardFields="['id', 'device_name']" /> </span>
							</TableCell>
							<TableCell class="text-start table-cell-fixed">
								<span class="text-truncate" :title="row.config_filesize ? formatters.formatFileSize(row.config_filesize) : ''"> {{ row.config_filesize ? formatters.formatFileSize(row.config_filesize) : "" }}</span>
							</TableCell>
							<TableCell class="text-start table-cell-fixed">
								<span class="text-truncate" :title="formatters.formatTime(row.created_at)">{{ formatters.formatTime(row.created_at) }}</span>
							</TableCell>
							<!-- ACTIONS MENU -->
							<TableCell class="text-start table-cell-fixed">
								<div class="flex items-center button-cell-fixed">
									<TooltipProvider>
										<Tooltip>
											<TooltipTrigger as-child>
												<Button variant="ghost" @click="openDialog('peek-config-dialog-' + row.id)" :disabled="row.config_downloaded == 0">
													<RcIcon name="peek-eye" />
												</Button>
											</TooltipTrigger>
											<TooltipContent class="text-white bg-rcgray-800">
												<p>Peek Config</p>
											</TooltipContent>
										</Tooltip>
									</TooltipProvider>

									<TooltipProvider>
										<Tooltip>
											<TooltipTrigger as-child>
												<Button variant="ghost" @click="viewDetailsPane(row.id)" :disabled="row.config_downloaded == 0">
													<FileView size="16" class="text-muted-foreground hover:text-blue-500" />
												</Button>
											</TooltipTrigger>
											<TooltipContent class="text-white bg-rcgray-800">
												<p>Open Config</p>
											</TooltipContent>
										</Tooltip>
									</TooltipProvider>

									<PeekConfigDialog :editId="row.id" v-if="isDialogOpen('peek-config-dialog-' + row.id)"></PeekConfigDialog>

									<ActionsMenu :rowData="row" :showEditBtn="false" :showViewDetailsBtn="row.config_downloaded == 1" @onViewDetails="viewDetailsPane(row.id)" @onDelete="deleteConfig(row.id)" />
								</div>
							</TableCell>
							<!-- ACTIONS MENU -->
						</TableRow>
					</template>
					<template v-else>
						<NoResults />
					</template>
				</TableBody>
			</Table>

			<!-- Load more button -->
			<Button v-if="isLoadMoreMode && hasMoreData" @click="loadMoreConfigs" :disabled="isLoadingMore" class="load-more-btn my-4" variant="outline">
				<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<path d="M12 8v8m0 0l4-4m-4 4l-4-4" />
					<path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
				</svg>
				{{ isLoadingMore ? "Loading..." : "Load More Results" }}
			</Button>

			<!-- PAGINATION -->
			<Pagination :currentPage="currentPage" :lastPage="lastPage" :perPage="perPage" @update:currentPage="currentPage = $event" @update:perPage="perPage = $event" />
			<!-- END PAGINATION -->

			<!-- FOR MULTIPLE DELETE -->
			<RcConfirmAlertDialog :ids="selectedRows" :showConfirmDelete="showConfirmDelete" @close="showConfirmDelete = false" @handleDelete="deleteManyConfigs(selectedRows)" />
			<!-- FOR MULTIPLE DELETE -->

			<Toaster />
		</div>
	</div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
	transition: opacity 0.3s;
}
.fade-enter-from,
.fade-leave-to {
	opacity: 0;
}
.fade-enter-to,
.fade-leave-from {
	opacity: 1;
}

.rc-th-heading {
	@apply text-left font-medium;
}

/* Fixed row height for consistent table appearance */
.table-row-fixed {
	@apply h-[60px] max-h-[60px] overflow-hidden;
}

/* Ensure table cells don't expand beyond fixed height */
.table-cell-fixed {
	@apply h-[60px] max-h-[60px] overflow-hidden align-middle leading-[1.4];
}

/* Text truncation for content that might overflow */
.text-truncate {
	@apply whitespace-nowrap overflow-hidden text-ellipsis max-w-full;
}

/* Multi-line text truncation for longer content */
.text-truncate-multiline {
	@apply overflow-hidden text-ellipsis leading-[1.4] max-h-[2.8em];
	display: -webkit-box;
	-webkit-line-clamp: 2;
	-webkit-box-orient: vertical;
}

/* Badge container with fixed height */
.badge-container-fixed {
	@apply h-full flex items-center overflow-hidden;
}

/* Ensure buttons in cells don't exceed row height */
.button-cell-fixed {
	@apply max-h-[50px] overflow-hidden;
}
</style>