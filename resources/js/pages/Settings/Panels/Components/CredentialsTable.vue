<script setup>
import ActionsMenu from "@/pages/Shared/Table/ActionsMenu.vue";
import BadgeList from "@/pages/Shared/Table/BadgeList.vue";
import ClearFilters from "@/pages/Shared/Filters/ClearFilters.vue";
import CredentialsAddEditDialog from "@/pages/Settings/Panels/Components/CredentialsAddEditDialog.vue";
import Loading from "@/pages/Shared/Table/Loading.vue";
import NoResults from "@/pages/Shared/Table/NoResults.vue";
import Pagination from "@/pages/Shared/Table/Pagination.vue";
import RcConfirmAlertDialog from "@/pages/Shared/ConfirmAlertDialog/RcConfirmAlertDialog.vue";
import RcToolTip from "@/pages/Shared/Tooltips/RcToolTip.vue";
import { Key } from "lucide-vue-next";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { eventBus } from "@/composables/eventBus";
import { onMounted, ref } from "vue";
import { useCredentials } from "@/pages/Settings/Panels/Components/useCredentials";
import { useRowSelection } from "@/composables/useRowSelection";

const {
	// --- Methods / Actions ---
	reload,
	fetchCreds,
	createCred,
	editCred,
	deleteCredential,
	deleteManyCredentials,
	handleSave,
	toggleSort,

	// --- State / Data ---
	creds,
	currentPage,
	lastPage,
	perPage,
	searchTerm,
	sortParam,
	isLoading,
	editId,
	newCredModalKey,
	showConfirmDelete,
	formatters,
	dialogStore,

	// --- External Utilities ---
	pageSettings,
	router,
} = useCredentials();
const { selectedRows, selectAll, toggleSelectAll, toggleSelectRow } = useRowSelection(creds);

const props = defineProps({});

onMounted(() => {
	eventBus.on("deleteManyCredentialsSuccess", () => {
		selectedRows.value = [];
		selectAll.value = false;
	});
});
</script>

<template>
	<div class="flex flex-col h-full gap-1 text-center">
		<div class="flex items-center justify-between p-4">
			<div class="flex items-center">
				<Input class="max-w-sm ml-4 mr-2" autocomplete="off" data-1p-ignore data-lpignore="true" placeholder="Filter credentials..." v-model="searchTerm" />
				<ClearFilters v-if="searchTerm" @update:model-value="searchTerm = ''" />
			</div>
			<div class="flex items-center justify-end">
				<Button v-if="selectedRows.length" class="px-2 py-1 bg-red-600 hover:bg-red-700 hover:animate-pulse" size="md" @click.prevent="showConfirmDelete = true" variant="primary"> Delete Selected {{ selectedRows.length }} Credentials </Button>
				<Button type="submit" class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse" size="sm" @click.prevent="createCred()" variant="primary">
					<Key size="16" class="mr-2" />
					New Credentials
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
							<Button class="flex justify-start w-full p-0 hover:bg-rcgray-800" variant="ghost" @click="toggleSort('cred_name')">
								<RcIcon name="sort" :sortParam="sortParam" field="cred_name" />
								<span class="ml-2">Name</span>
							</Button>
						</TableHead>
						<TableHead class="w-[30%]">Description</TableHead>
						<TableHead class="w-[30%]">Devices</TableHead>
						<TableHead class="w-[10%]">Created</TableHead>
						<TableHead class="w-[10%]">Actions</TableHead>
					</TableRow>
				</TableHeader>
				<TableBody>
					<template v-if="isLoading">
						<Loading />
					</template>
					<template v-else-if="!isLoading">
						<TableRow v-for="row in creds.data" :key="row.id">
							<TableCell class="text-start">
								<Checkbox class="cursor-pointer" :id="'select-' + row.id" :checked="selectedRows.includes(row.id) ? true : false" @click="toggleSelectRow(row.id)" />
							</TableCell>
							<TableCell class="text-start">
								<div class="flex items-center">
									{{ row.id }}
									<RcIcon name="vault" v-if="row.vault_enabled === 1" class="ml-2 text-yellow-300" />
								</div>
							</TableCell>
							<TableCell class="text-start">
								<div class="flex items-center gap-2">
									<Button class="px-2 py-0 text-sm hover:bg-rcgray-800 rounded-xl" variant="ghost" @click="editCred(row)">
										<span class="border-b">{{ row.cred_name }}</span>
									</Button>
								</div>
							</TableCell>
							<TableCell class="text-start">
								{{ row.cred_description }}
							</TableCell>
							<TableCell class="text-start">
								<BadgeList :items="row.device" displayField="device_name" linkField="view_url" :maxVisible="4" :hoverCardFields="['id', 'device_name', 'device_ip']" />
							</TableCell>
							<TableCell class="text-start">
								{{ row.created_at }}
							</TableCell>
							<!-- ACTIONS MENU -->
							<TableCell class="text-start">
								<ActionsMenu
									:showEditBtn="true"
									@onEdit="
										editCred(row);
										showCredDialog = true;
									"
									:rowData="row"
									@onDelete="deleteCredential(row.id)"
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

			<Pagination :currentPage="currentPage" :lastPage="lastPage" :perPage="perPage" @update:currentPage="currentPage = $event" @update:perPage="perPage = $event" :totalRecords="creds.total" :isLoading="isLoading" />
			<CredentialsAddEditDialog @save="handleSave()" :key="newCredModalKey" :editId="editId" />
			<RcConfirmAlertDialog :ids="selectedRows" :showConfirmDelete="showConfirmDelete" @close="showConfirmDelete = false" @handleDelete="deleteManyCredentials(selectedRows)" />
		</div>
	</div>
</template>