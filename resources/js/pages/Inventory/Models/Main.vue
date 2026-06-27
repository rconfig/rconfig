<script setup>
import ActionsMenu from "@/pages/Shared/Table/ActionsMenu.vue";
import ClearFilters from "@/pages/Shared/Filters/ClearFilters.vue";
import Loading from "@/pages/Shared/Table/Loading.vue";
import NoResults from "@/pages/Shared/Table/NoResults.vue";
import Pagination from "@/pages/Shared/Table/Pagination.vue";
import RcConfirmAlertDialog from "@/pages/Shared/ConfirmAlertDialog/RcConfirmAlertDialog.vue";
import DeviceModelAddEditDialog from "@/pages/Inventory/Models/DeviceModelAddEditDialog.vue";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { eventBus } from "@/composables/eventBus";
import { onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import { useRowSelection } from "@/composables/useRowSelection";
import { useDeviceModels } from "@/pages/Inventory/Models/useDeviceModels";
import { ExternalLink } from "lucide-vue-next";

const {
	// State
	reload,
	deviceModels,
	isLoading,
	currentPage,
	perPage,
	lastPage,
	editId,
	editModelName,
	newModelModalKey,
	searchTerm,
	showConfirmDelete,

	// Dialogs & Actions
	openDialog,
	fetchDeviceModels,
	createDeviceModel,
	updateDeviceModel,
	deleteDeviceModel,
	deleteManyDeviceModels,
	handleSave,
	handleKeyDown,
	viewEditDialog,
	viewDevicesDialog,
	toggleSort,
	sortParam,
} = useDeviceModels();

const { selectedRows, selectAll, toggleSelectAll, toggleSelectRow } = useRowSelection(deviceModels);
const router = useRouter();

onMounted(() => {
	if (router.currentRoute?.value.params?.modelName) {
		editModelName.value = decodeURIComponent(router.currentRoute.value.params.modelName);
		viewEditDialog(editModelName.value);
	}

	fetchDeviceModels();
	window.addEventListener("keydown", handleKeyDown);

	eventBus.on("deleteManyDeviceModelsSuccess", () => {
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
				<Input
					v-model="searchTerm"
					class="max-w-sm ml-4 mr-2"
					autocomplete="off"
					data-1p-ignore
					data-lpignore="true"
					:placeholder="`Filter Device Models...`"
				/>
				<ClearFilters
					v-if="searchTerm"
					@update:model-value="searchTerm = ''"
				/>
			</div>
			<div class="flex items-center justify-end">
				<Button
					v-if="selectedRows.length"
					class="px-2 py-1 bg-red-600 hover:bg-red-700 hover:animate-pulse"
					size="md"
					variant="primary"
					@click.prevent="showConfirmDelete = true"
				>
					Delete Selected {{ selectedRows.length }} Device(s) Model(s)
				</Button>

				<Button
					type="submit"
					class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
					size="sm"
					variant="primary"
					@click.prevent="createDeviceModel(0)"
				>
					Add Model
					<div class="pl-2 ml-auto">
						<kbd class="rc-kdb-class2">ALT N</kbd>
					</div>
				</Button>

				<RcIcon
					name="refresh"
					class="w-4 h-4 mx-4 text-muted-foreground cursor-pointer hover:text-rcgray-200"
					@click="reload()"
				/>
			</div>
		</div>

		<div class="px-6">
			<Table>
				<TableHeader>
					<TableRow>
						<TableHead class="w-[2%]">
							<Checkbox
								id="selectAll"
								v-model="selectAll"
								:checked="selectAll"
								@click="toggleSelectAll()"
							/>
						</TableHead>
						<TableHead class="w-[5%]">
							<Button
								class="flex justify-start w-full p-0 hover:bg-rcgray-800"
								variant="ghost"
								@click="toggleSort('id')"
							>
								<RcIcon
									name="sort"
									:sort-param="sortParam"
									field="id"
								/>
								<span class="ml-2">ID</span>
							</Button>
						</TableHead>
						<TableHead class="w-[30%]">
							<Button
								class="flex justify-start w-full p-0 hover:bg-rcgray-800"
								variant="ghost"
								@click="toggleSort('name')"
							>
								<RcIcon
									name="sort"
									:sort-param="sortParam"
									field="name"
								/>
								<span class="ml-2"> Name </span>
							</Button>
						</TableHead>
						<TableHead class="w-[15%]">
							<Button
								class="flex justify-start w-full p-0 hover:bg-rcgray-800"
								variant="ghost"
								@click="toggleSort('devices_count')"
							>
								<RcIcon
									name="sort"
									:sort-param="sortParam"
									field="devices_count"
								/>
								<span class="ml-2"> Device Count </span>
							</Button>
						</TableHead>
						<TableHead class="w-[35%]">
							Devices
						</TableHead>
						<TableHead class="w-[10%]">
							Actions
						</TableHead>
					</TableRow>
				</TableHeader>
				<TableBody>
					<template v-if="isLoading">
						<Loading />
					</template>

					<template v-else-if="!isLoading && deviceModels.data && deviceModels.data.length">
						<TableRow
							v-for="row in deviceModels.data"
							:key="row.id"
						>
							<TableCell class="text-start">
								<Checkbox
									:id="'select-' + row.id"
									class="cursor-pointer"
									:checked="selectedRows.includes(row.id) ? true : false"
									@click="toggleSelectRow(row.id)"
								/>
							</TableCell>

							<TableCell class="text-start">
								{{ row.id }}
							</TableCell>

							<TableCell class="text-start font-medium">
								{{ row.name }}
							</TableCell>

							<TableCell class="text-start">
								<RcBadge
									variant="info"
									class="px-3 py-1"
								>
									{{ row.devices_count }}
								</RcBadge>
							</TableCell>

							<TableCell class="text-start">
								<div class="flex items-center space-x-2">
									<Button
										v-if="row.devices_count > 0"
										variant="outline"
										size="sm"
										@click="viewDevicesDialog(row.name)"
									>
										View Devices ({{ row.devices_count }})
										<ExternalLink
											size="12"
											class="ml-2"
										/>
									</Button>
									<span
										v-else
										class="text-muted-foreground text-sm"
									>
										--
									</span>
								</div>
							</TableCell>

							<!-- ACTIONS MENU -->
							<TableCell class="text-start">
								<ActionsMenu
									:row-data="row"
									:show-roles-btn="false"
									:show-view-devices-btn="row.devices_count > 0"
									@on-edit="viewEditDialog(row.id)"
									@on-delete="deleteDeviceModel(row.name)"
									@on-view-devices="viewDevicesDialog(row.name)"
								/>
							</TableCell>
							<!-- ACTIONS MENU -->
						</TableRow>
					</template>
					<template v-else>
						<NoResults />
					</template>
				</TableBody>
			</Table>

			<Pagination
				:current-page="currentPage"
				:last-page="lastPage"
				:per-page="perPage"
				:total-records="deviceModels.total"
				:is-loading="isLoading"
				@update:current-page="currentPage = $event"
				@update:per-page="perPage = $event"
			/>

			<DeviceModelAddEditDialog
				:key="newModelModalKey"
				:edit-model-name="editModelName"
				:edit-id="editId"
				@save="handleSave()"
			/>

			<RcConfirmAlertDialog
				:ids="selectedRows"
				:show-confirm-delete="showConfirmDelete"
				@close="showConfirmDelete = false"
				@handle-delete="deleteManyDeviceModels(selectedRows)"
			/>
		</div>
	</div>
</template>
