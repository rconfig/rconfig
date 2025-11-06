<script setup>
import ActionsMenu from "@/pages/Shared/Table/ActionsMenu.vue";
import BadgeList from "@/pages/Shared/Table/BadgeList.vue";
import ClearFilters from "@/pages/Shared/Filters/ClearFilters.vue";
import RcConfirmAlertDialog from "@/pages/Shared/ConfirmAlertDialog/RcConfirmAlertDialog.vue";
import Loading from "@/pages/Shared/Table/Loading.vue";
import NoResults from "@/pages/Shared/Table/NoResults.vue";
import Pagination from "@/pages/Shared/Table/Pagination.vue";
import VendorAddEditDialog from "@/pages/Inventory/Vendors/VendorAddEditDialog.vue";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { eventBus } from "@/composables/eventBus";
import { onMounted, onUnmounted } from "vue";
import { useRowSelection } from "@/composables/useRowSelection";
import { useVendors } from "@/pages/Inventory/Vendors/useVendors";
import { useRouter } from "vue-router";

const { reload, editId, vendors, currentPage, perPage, searchTerm, lastPage, isLoading, fetchVendors, viewEditDialog, createVendor, deleteVendor, deleteManyVendors, handleSave, handleKeyDown, newVendorModalKey, toggleSort, sortParam, showConfirmDelete } = useVendors();
const { selectedRows, selectAll, toggleSelectAll, toggleSelectRow } = useRowSelection(vendors);
const router = useRouter();

onMounted(() => {
	fetchVendors();

	if (router.currentRoute?.value.params?.id) {
		editId.value = parseInt(router.currentRoute.value.params.id);
		viewEditDialog(editId.value);
	}

	window.addEventListener("keydown", handleKeyDown);

	eventBus.on("deleteManyVendorsSuccess", () => {
		selectedRows.value = [];
		selectAll.value = false;
		document.getElementById("selectAll").checked = false;
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
				<Input class="max-w-sm ml-4 mr-2" autocomplete="off" data-1p-ignore data-lpignore="true" placeholder="Filter vendors..." v-model="searchTerm" />
				<ClearFilters v-if="searchTerm" @update:model-value="searchTerm = ''" />
			</div>
			<div class="flex items-center justify-end">
				<Button v-if="selectedRows.length" class="px-2 py-1 bg-red-600 hover:bg-red-700 hover:animate-pulse" size="md" @click.prevent="showConfirmDelete = true" variant="primary">Delete Selected {{ selectedRows.length }} Vendor(s) </Button>

				<Button type="submit" class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse" size="sm" @click.prevent="createVendor" variant="primary">
					New Vendor
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
							<Checkbox id="selectAll" v-model="selectAll" :checked="selectAll" @click="toggleSelectAll()" />
						</TableHead>
						<TableHead class="w-[5%]">
							<Button class="flex justify-start w-full p-0 hover:bg-rcgray-800" variant="ghost" @click="toggleSort('id')">
								<RcIcon name="sort" :sortParam="sortParam" field="id" />
								<span class="ml-2">ID</span>
							</Button>
						</TableHead>
						<TableHead class="w-[20%]">
							<Button class="flex justify-start w-full p-0 hover:bg-rcgray-800" variant="ghost" @click="toggleSort('vendorName')">
								<RcIcon name="sort" :sortParam="sortParam" field="vendorName" />
								<span class="ml-2">Name</span>
							</Button>
						</TableHead>
						<TableHead class="w-[50%]">Devices</TableHead>
						<TableHead class="w-[10%]">Actions</TableHead>
					</TableRow>
				</TableHeader>
				<TableBody>
					<template v-if="isLoading">
						<Loading />
					</template>

					<template v-else-if="!isLoading">
						<TableRow v-for="row in vendors.data" :key="row.id">
							<TableCell class="text-start">
								<Checkbox class="cursor-pointer" :id="'select-' + row.id" :checked="selectedRows.includes(row.id) ? true : false" @click="toggleSelectRow(row.id)" />
							</TableCell>
							<TableCell class="text-start">
								{{ row.id }}
							</TableCell>
							<TableCell class="text-start">
								{{ row.vendorName }}
							</TableCell>

							<TableCell class="text-start">
								<BadgeList :items="row.device" displayField="device_name" linkField="view_url" :maxVisible="8" :hoverCardFields="['id', 'device_name', 'device_ip']" />
							</TableCell>
							<!-- ACTIONS MENU -->
							<TableCell class="text-start">
								<ActionsMenu :rowData="row" @onEdit="viewEditDialog(row.id)" @onDelete="deleteVendor(row.id)" />
							</TableCell>
							<!-- ACTIONS MENU -->
						</TableRow>
					</template>
					<template v-else>
						<NoResults />
					</template>
				</TableBody>
			</Table>

			<Pagination :currentPage="currentPage" :lastPage="lastPage" :perPage="perPage" @update:currentPage="currentPage = $event" @update:perPage="perPage = $event" :totalRecords="vendors.total" :isLoading="isLoading" />
			<VendorAddEditDialog @save="handleSave()" :key="newVendorModalKey" :editId="editId" />
			<RcConfirmAlertDialog :ids="selectedRows" :showConfirmDelete="showConfirmDelete" @close="showConfirmDelete = false" @handleDelete="deleteManyVendors(selectedRows)" />
		</div>
	</div>
</template>