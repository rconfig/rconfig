<script setup>
import ActionsMenuDevices from "@/pages/Shared/Table/ActionsMenuDevices.vue";
import BadgeList from "@/pages/Shared/Table/BadgeList.vue";
import CategoryFilter from "@/pages/Inventory/Devices/Filters/CategoryFilter.vue";
import ClearFilters from "@/pages/Shared/Filters/ClearFilters.vue";
import ColumnChooser from "./Components/ColumnChooser.vue";
import DeviceAddEditDialog from "@/pages/Inventory/Devices/DeviceAddEditDialog.vue";
import DeviceBulkEditDialog from "@/pages/Inventory/Devices/Components/DeviceBulkEditDialog.vue";
import Loading from "@/pages/Shared/Table/Loading.vue";
import NoResults from "@/pages/Shared/Table/NoResults.vue";
import Pagination from "@/pages/Shared/Table/Pagination.vue";
import RcConfirmAlertDialog from "@/pages/Shared/ConfirmAlertDialog/RcConfirmAlertDialog.vue";
import Spinner from "@/pages/Shared/Icon/Spinner.vue";
import StatusFilter from "@/pages/Inventory/Devices/Filters/StatusFilter.vue";
import TagFilter from "@/pages/Inventory/Devices/Filters/TagFilter.vue";
import VendorFilter from "@/pages/Inventory/Devices/Filters/VendorFilter.vue";
import ModelFilter from "@/pages/Inventory/Devices/Filters/ModelFilter.vue";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuShortcut, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { FileStack, MoreVertical, CirclePlus, Shredder, FilePenLine, ChevronDown, MonitorOff, TvMinimalPlay, Power } from "lucide-vue-next";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { eventBus } from "@/composables/eventBus";
import { onMounted, onUnmounted, ref, watch, nextTick, computed } from "vue";
import { useDevices } from "@/pages/Inventory/Devices/useDevices";
import { useRoute, useRouter } from "vue-router";
import { useRowSelection } from "@/composables/useRowSelection";

const props = defineProps({
	statusId: {
		type: Number,
		required: false,
	},
});

const {
	// --- State ---
	devices,
	currentPage,
	perPage,
	lastPage,
	totalRecords,
	isLoading,
	isLoadingMore,
	hasMoreData,
	isLoadMoreMode,
	showConfirmDelete,
	editId,
	newDeviceModalKey,
	newBulkEditModalKey,
	searchTerm,
	filterStatus,
	filterCategories,
	filterTags,
	filterVendor,
	filterModel,
	sortParam,
	formatters,
	inventoryIsLoading,

	// --- Methods: CRUD & Actions ---
	createDevice,
	deleteDevice,
	deleteManyDevices,
	disableDevice,
	enableDevice,
	exportInventoryReport,
	handleKeyDown,
	handleSave,
	reload,
	viewDeviceDetailsPane,
	viewEditDialog,
	openBulkEditDialog,
	disableManyDevices,
	enableManyDevices,
	downloadManyDevices,

	// --- Methods: Table & Filtering ---
	fetchDevices,
	loadMoreDevices,
	clearFilters,
	toggleSort,
} = useDevices();
const { selectedRows, selectAll, toggleSelectAll, toggleSelectRow } = useRowSelection(devices);

const isClone = ref(false);
const route = useRoute();
const router = useRouter();

// Add column definitions
const columnDefs = computed(() => [
	{ key: "checkbox", label: "Select", mandatory: true, default: true },
	{ key: "id", label: "ID", default: true },
	{ key: "status", label: "Status", default: true },
	{ key: "device_name", label: "Name", mandatory: true, default: true },
	{ key: "device_ip", label: "IP Address", default: true },
	{ key: "category", label: "Command Group", default: true },
	{ key: "vendor", label: "Vendor", default: true },
	{ key: "device_model", label: "Model", default: true },
	{ key: "config_good_count", label: "Config Count", default: true },
	{ key: "config_bad_count", label: "Config Failures", default: true },
	{ key: "last_config", label: "Last Config", default: true },
	{ key: "tags", label: "Tags", default: true },
	{ key: "actions", label: "Actions", mandatory: true, default: true },
]);

// Define which columns are visible
const visibleColumns = ref(columnDefs.value.filter((col) => col.default).map((col) => col.key));

// Computed property to determine if a column is visible
const isColumnVisible = computed(() => {
	return (columnKey) => visibleColumns.value.includes(columnKey);
});

// Function to get visible columns in order
const orderedVisibleColumns = computed(() => {
	return columnDefs.value
		.filter((col) => visibleColumns.value.includes(col.key))
		.sort((a, b) => {
			const indexA = visibleColumns.value.indexOf(a.key);
			const indexB = visibleColumns.value.indexOf(b.key);
			return indexA - indexB;
		});
});

onMounted(() => {
	if (route.query.statusId) {
		filterStatus.value = [{ id: parseInt(route.query.statusId) }];
	}

	fetchDevices();

	window.addEventListener("keydown", handleKeyDown);

	eventBus.on("deleteManyDevicesSuccess", () => {
		selectedRows.value = [];
		selectAll.value = false;
	});
});

watch(
	() => route.query,
	async (newQuery) => {
		if (newQuery.openDeviceDialog) {
			createDevice();

			await nextTick();

			const { openDeviceDialog, ...remainingQuery } = newQuery;
			router.replace({ query: remainingQuery }, undefined, { replace: true });
		}
	},
	{ immediate: true }
);

// Cleanup event listener on unmount
onUnmounted(() => {
	window.removeEventListener("keydown", handleKeyDown);
});
</script>

<template>
	<div class="flex flex-col h-full gap-1 text-center">
		<div class="flex items-center justify-between p-4">
			<div class="flex items-center">
				<Input class="ml-2 min-w-32 lg:min-w-60" autocomplete="off" placeholder="Filter [id, name, ip]..." v-model="searchTerm" />

				<Separator orientation="vertical" class="relative w-px h-6 mx-4 shrink-0 bg-border" />

				<span class="mr-2 text-muted-foreground flex-grow min-w-max">Filters:</span>
				<!-- FILTERS -->
				<div class="flex gap-2">
					<CategoryFilter v-model="filterCategories" />
					<TagFilter v-model="filterTags" />
					<VendorFilter v-model="filterVendor" />
					<ModelFilter v-model="filterModel" />
					<StatusFilter v-model="filterStatus" />
					<ClearFilters v-if="searchTerm || filterCategories.length || filterTags.length || filterVendor.length || filterModel.length || filterStatus.length" @update:model-value="clearFilters" />
				</div>
				<!-- FILTERS -->
			</div>

			<div class="flex items-center justify-end">
				<!-- Add column chooser -->
				<ColumnChooser :columns="columnDefs" v-model="visibleColumns" storageKey="rconfig.deviceTable.columns" />

				<DropdownMenu>
					<DropdownMenuTrigger as-child class="inline-flex xl:hidden">
						<Button variant="ghost" class="h-8 ml-2">
							<MoreVertical size="16" />
						</Button>
					</DropdownMenuTrigger>
					<DropdownMenuContent class="w-56" align="end" side="bottom">
						<DropdownMenuItem class="cursor-pointer hover:bg-rcgray-600 group" @click.prevent="createDevice()">
							New Device
							<DropdownMenuShortcut class="group">
								<CirclePlus size="16" class="text-rcgray-400 group-hover:text-rcgray-300" />
							</DropdownMenuShortcut>
						</DropdownMenuItem>
						<DropdownMenuItem class="cursor-pointer hover:bg-rcgray-600 group" @click.prevent="exportInventoryReport()">
							Inventory Report
							<DropdownMenuShortcut class="group">
								<FileStack v-if="!inventoryIsLoading" size="16" class="text-rcgray-400 group-hover:text-rcgray-300" />
								<Spinner :state="inventoryIsLoading" class="mr-2" />
							</DropdownMenuShortcut>
						</DropdownMenuItem>
					</DropdownMenuContent>
				</DropdownMenu>

				<Button type="submit" class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse hidden xl:inline-flex" size="sm" @click.prevent="createDevice()" variant="primary">
					New Device
					<div class="pl-2 py-1 px-2 ml-auto">
						<kbd class="rc-kdb-class2">ALT N</kbd>
					</div>
				</Button>
				<Button type="submit" class="px-2 py-1 ml-2 text-sm hidden xl:inline-flex" size="sm" @click.prevent="exportInventoryReport()" variant="outline">
					<FileStack v-if="!inventoryIsLoading" size="16" class="text-muted-foreground mr-2" />
					<Spinner :state="inventoryIsLoading" class="mr-2" />
					Inventory Report
				</Button>
				<RcIcon name="refresh" class="w-4 h-4 mx-4 text-muted-foreground cursor-pointer hover:text-rcgray-200" @click="reload()" />
			</div>
		</div>

		<div class="px-6">
			<Table>
				<TableHeader>
					<TableRow>
						<!-- Checkbox column -->
						<TableHead v-if="isColumnVisible('checkbox')" class="w-[2%]">
							<Checkbox id="selectAll" v-model="selectAll" :checked="selectAll" @click="toggleSelectAll()" />
						</TableHead>

						<!-- ID column -->
						<TableHead v-if="isColumnVisible('id')" class="w-[5%]">
							<Button class="flex justify-start w-full p-0 hover:bg-rcgray-800" variant="ghost" @click="toggleSort('id')">
								<RcIcon name="sort" :sortParam="sortParam" field="id" />
								<span class="ml-2">ID</span>
							</Button>
						</TableHead>

						<!-- Status column -->
						<TableHead v-if="isColumnVisible('status')" class="w-[2%]">
							<Button class="flex justify-start w-full p-0 hover:bg-rcgray-800" variant="ghost" @click="toggleSort('status')">
								<RcIcon name="sort" :sortParam="sortParam" field="status" />
								<span class="ml-2">Status</span>
							</Button>
						</TableHead>

						<!-- Device Name column -->
						<TableHead v-if="isColumnVisible('device_name')" class="w-[20%]">
							<Button class="flex justify-start w-full p-0 hover:bg-rcgray-800" variant="ghost" @click="toggleSort('device_name')">
								<RcIcon name="sort" :sortParam="sortParam" field="device_name" />
								<span class="ml-2">Name</span>
							</Button>
						</TableHead>

						<!-- IP Address column -->
						<TableHead v-if="isColumnVisible('device_ip')" class="w-[10%]">IP Address</TableHead>

						<!-- Category column -->
						<TableHead v-if="isColumnVisible('category')" class="w-[10%]">Command Group</TableHead>

						<!-- Vendor column -->
						<TableHead v-if="isColumnVisible('vendor')" class="w-[10%]">Vendor</TableHead>

						<!-- Model column -->
						<TableHead v-if="isColumnVisible('device_model')" class="w-[10%]">Model</TableHead>

						<!-- Config Count column -->
						<TableHead v-if="isColumnVisible('config_good_count')" class="w-[10%]">Config Count</TableHead>

						<!-- Config Failures column -->
						<TableHead v-if="isColumnVisible('config_bad_count')" class="w-[10%]">Config Failures</TableHead>

						<!-- Last Config column -->
						<TableHead v-if="isColumnVisible('last_config')" class="w-[10%]">Last Config</TableHead>

						<!-- Tags column -->
						<TableHead v-if="isColumnVisible('tags')" class="w-[10%]">Tags</TableHead>

						<!-- Actions column -->
						<TableHead v-if="isColumnVisible('actions')" class="w-[10%]">Actions</TableHead>
					</TableRow>
				</TableHeader>
				<TableBody>
					<template v-if="isLoading">
						<Loading />
					</template>

					<template v-else-if="!isLoading">
						<TableRow v-for="row in devices" :key="row.id" class="table-row-fixed">
							<!-- Checkbox cell -->
							<TableCell v-if="isColumnVisible('checkbox')" class="text-start table-cell-fixed">
								<Checkbox class="cursor-pointer" :id="'select-' + row.id" :checked="selectedRows.includes(row.id) ? true : false" @click="toggleSelectRow(row.id)" />
							</TableCell>

							<!-- ID cell -->
							<TableCell v-if="isColumnVisible('id')" class="text-start table-cell-fixed">
								<div class="flex items-center">
									<span
										:class="{
											'text-red-500': row.device_added_by == 9000,
											'text-blue-500': row.device_added_by == 9001,
										}"
										:title="row.device_added_by == 9000 ? 'Added by Zabbix' : row.device_added_by == 9001 ? 'Added by Netbox' : ''"
									>
										{{ row.id }}
									</span>
									<RcIcon name="imported-device" :addedBy="row.device_added_by" v-if="row.device_added_by && row.device_added_by >= 9000" :source="row.device_added_by === 9001 ? 'Netbox' : row.device_added_by === 9000 ? 'Zabbix' : 'Integration'" />
								</div>
							</TableCell>

							<!-- Status cell -->
							<TableCell v-if="isColumnVisible('status')" class="text-start table-cell-fixed">
								<RcIcon name="status-red" v-if="row.status === 0" />
								<RcIcon name="status-green" v-if="row.status === 1" />
								<RcIcon name="status-yellow" v-if="row.status === 2" />
								<RcIcon name="status-gray" v-if="row.status === 100" />
								<RcIcon name="status-red-agent" v-if="row.status === 300" size="16" />
								<RcIcon name="status-gray-agent" v-if="row.status === 301" />
							</TableCell>

							<!-- Device Name cell -->
							<TableCell v-if="isColumnVisible('device_name')" class="text-start">
								<div class="flex justify-between">
									<Button class="px-2 py-0 hover:bg-rcgray-800 rounded-xl" variant="ghost" @click="viewDeviceDetailsPane(row.id)">
										<div class="flex items-center">
											<span class="border-b">{{ row.device_name }}</span>
										</div>
									</Button>
								</div>
							</TableCell>

							<TableCell v-if="isColumnVisible('device_ip')" class="text-start table-cell-fixed">
								<span class="text-truncate" :title="row.device_ip">{{ row.device_ip }}</span>
							</TableCell>

							<TableCell v-if="isColumnVisible('category')" class="text-start table-cell-fixed">
								<span v-if="row.category.length > 0" class="text-truncate" :title="row.category[0].categoryName">{{ row.category[0].categoryName }}</span>
								<span v-else>--</span>
							</TableCell>

							<TableCell v-if="isColumnVisible('vendor')" class="text-start table-cell-fixed">
								<span v-if="row.vendor.length > 0" class="text-truncate" :title="row.vendor[0].vendorName">{{ row.vendor[0].vendorName }}</span>
								<span v-else>--</span>
							</TableCell>

							<TableCell v-if="isColumnVisible('device_model')" class="text-start table-cell-fixed">
								<span class="text-truncate" :title="row.device_model">{{ row.device_model }}</span>
							</TableCell>

							<TableCell v-if="isColumnVisible('config_good_count')" class="text-start table-cell-fixed">
								{{ row.config_summary?.total_count || "--" }}
							</TableCell>

							<TableCell v-if="isColumnVisible('config_bad_count')" class="text-start table-cell-fixed">
								{{ row.config_summary?.download_status_0_count || "--" }}
							</TableCell>

							<TableCell v-if="isColumnVisible('last_config')" class="text-start table-cell-fixed">
								<span v-if="row.last_config" class="text-truncate" :title="formatters.formatTime(row.last_config.created_at)">{{ formatters.formatTime(row.last_config.created_at) }}</span>
								<span v-else>--</span>
							</TableCell>

							<TableCell v-if="isColumnVisible('tags')" class="text-start table-cell-fixed">
								<div class="badge-container-fixed">
									<BadgeList :items="row.tag" displayField="tagname" linkField="view_url" :maxVisible="1" :hoverCardFields="['id', 'tagname', 'description']" />
								</div>
							</TableCell>

							<TableCell v-if="isColumnVisible('actions')" class="text-start table-cell-fixed">
								<div class="button-cell-fixed">
									<ActionsMenuDevices :rowData="row" @onEnable="enableDevice(row.id)" @onClone="viewEditDialog(row.id), (isClone = true)" @onDisable="disableDevice(row.id)" @onEdit="viewEditDialog(row.id), (isClone = false)" @onDelete="deleteDevice(row.id)" :showCopyDownloadDebug="true" />
								</div>
							</TableCell>
						</TableRow>
					</template>
					<template v-else>
						<NoResults />
					</template>
				</TableBody>
			</Table>

			<!-- FLOATING ACTION BAR -->
			<div v-if="selectedRows.length" class="fixed bottom-5 left-1/2 transform -translate-x-1/2 z-50 flex items-center gap-4 bg-rcgray-900 text-white border border-rcgray-600 shadow-xl px-4 py-1.5 rounded-lg">
				<span class="text-sm">
					<RcBadge variant="primary" class="mr-1">{{ selectedRows.length }} </RcBadge> Devices Selected
				</span>
				<Popover v-slot="{ open }">
					<PopoverTrigger class="col-span-3">
						<Button class="text-sm px-1.5 py-0 h-8 flex items-center gap-1 group" variant="outline">
							<Power class="inline-block mr-1 ml-0" size="14" />
							<span class="hidden md:inline-flex">Enable/Disable</span>
							<ChevronDown class="transition-transform duration-200 ease-in-out group-hover:rotate-180" :class="{ 'rotate-180': open }" size="14" />
						</Button>
					</PopoverTrigger>

					<PopoverContent side="bottom" align="start" class="col-span-3 p-1 text-left w-max">
						<div class="flex flex-col items-start gap-1">
							<Button class="text-sm px-1.5 py-0 h-8 hover:animate-pulse w-full" @click.prevent="enableManyDevices(selectedRows)" variant="ghost">
								<TvMinimalPlay size="16" class="text-green-300 mr-1" />
								Enable Selected
							</Button>
							<Button class="text-sm px-1.5 py-0 h-8 w-full" @click.prevent="disableManyDevices(selectedRows)" variant="ghost">
								<MonitorOff size="16" class="text-red-300 mr-1" />
								Disable Selected
							</Button>
						</div>
					</PopoverContent>
				</Popover>

				<Button class="text-sm px-1.5 py-0 hover:animate-pulse h-8" @click.prevent="downloadManyDevices(selectedRows)" variant="outline">
					<RcIcon name="folder-download-open" class="inline-block mr-0 md:mr-2 ml-0" size="14" />
					<span class="hidden md:inline-flex">Download Now</span>
				</Button>

				<Button class="text-sm px-1.5 py-0 hover:animate-pulse h-8" @click.prevent="openBulkEditDialog()" variant="outline">
					<FilePenLine class="inline-block mr-0 md:mr-2 ml-0" size="14" />
					<span class="hidden md:inline-flex">Bulk Edit</span>
				</Button>
				<Button class="text-sm px-1.5 py-0 bg-red-600 hover:bg-red-700 hover:animate-pulse h-8" @click.prevent="showConfirmDelete = true" variant="primary">
					<Shredder class="inline-block mr-0 md:mr-2 ml-0" size="14" />
					<span class="hidden md:inline-flex mr-1">Delete {{ selectedRows.length }}</span>
				</Button>
			</div>

			<!-- Load more button (only show in load more mode) -->
			<Button v-if="isLoadMoreMode && hasMoreData" @click="loadMoreDevices" :disabled="isLoadingMore" class="load-more-btn my-4" variant="outline">
				<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<path d="M12 8v8m0 0l4-4m-4 4l-4-4" />
					<path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
				</svg>
				{{ isLoadingMore ? "Loading..." : "Load More Results" }}
			</Button>

			<Pagination :currentPage="currentPage" :lastPage="lastPage" :perPage="perPage" @update:currentPage="currentPage = $event" @update:perPage="perPage = $event" :totalRecords="totalRecords" :isLoading="isLoading" />

			<DeviceAddEditDialog @save="handleSave()" @close="isClone = false" :key="newDeviceModalKey" :editId="editId" :isClone="isClone" />

			<DeviceBulkEditDialog v-if="selectedRows.length > 0" :checkedRows="selectedRows" :key="newBulkEditModalKey" @bulkUpdateSuccess="reload()" />

			<RcConfirmAlertDialog :ids="selectedRows" :showConfirmDelete="showConfirmDelete" @close="showConfirmDelete = false" @handleDelete="deleteManyDevices(selectedRows)" />

			<Toaster />
		</div>
	</div>
</template>