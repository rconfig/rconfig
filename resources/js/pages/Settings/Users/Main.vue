<script setup>
import ActionsMenu from "@/pages/Shared/Table/ActionsMenu.vue";
import ClearFilters from "@/pages/Shared/Filters/ClearFilters.vue";
import Loading from "@/pages/Shared/Table/Loading.vue";
import NoResults from "@/pages/Shared/Table/NoResults.vue";
import Pagination from "@/pages/Shared/Table/Pagination.vue";
import RcConfirmAlertDialog from "@/pages/Shared/ConfirmAlertDialog/RcConfirmAlertDialog.vue";
import RelatedDocumentationNav from "@/pages/Shared/RelatedDocumentationNavs/RelatedDocumentationNav.vue";
import UserAddEditDialog from "@/pages/Settings/Users/UserAddEditDialog.vue";
import { ClipboardList, User, Pyramid, ShieldUser } from "lucide-vue-next";
import { Switch } from "@/components/ui/switch";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { onMounted, onUnmounted } from "vue";
import { useRoute } from "vue-router";
import { useRowSelection } from "@/composables/useRowSelection";
import { useUsers } from "@/pages/Settings/Users/useUsers";

const { reload, editId, users, currentPage, perPage, searchTerm, lastPage, isLoading, navigateToActivityLog, fetchUsers, viewEditDialog, createUser, deleteUser, handleSave, handleKeyDown, newUserModalKey, toggleSort, sortParam, toggleNotification, toggleSocialiteApproved, relatedDocs, showConfirmDelete, deleteManyUsers, updateRelatedDocs } = useUsers();
const { selectedRows, selectAll, toggleSelectAll, toggleSelectRow } = useRowSelection(users);
const route = useRoute();

onMounted(() => {
	fetchUsers();
	updateRelatedDocs();
	window.addEventListener("keydown", handleKeyDown);

	// Check router param to open UserAddEditDialog
	if (route.params.userId && parseInt(route.params.userId, 10) > 0) {
		viewEditDialog(route.params.userId);
	}
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
				<Input class="max-w-sm ml-4 mr-2" autocomplete="off" data-1p-ignore data-lpignore="true" placeholder="Filter users..." v-model="searchTerm" />
				<ClearFilters v-if="searchTerm" @update:model-value="searchTerm = ''" />
			</div>
			<div class="flex items-center justify-end">
				<Button v-if="selectedRows.length" @click.prevent="showConfirmDelete = true" class="px-2 py-1 bg-red-600 hover:bg-red-700 hover:animate-pulse" size="md" variant="primary"> Delete Selected {{ selectedRows.length }} User(s) </Button>

				<!-- Activity Log Button -->
				<Button class="px-2 py-1 ml-4 mr-2 text-sm hover:animate-pulse flex items-center" size="sm" @click="navigateToActivityLog" variant="outline">
					<ClipboardList class="h-4 w-4 mr-1" />
					User Activity Log
				</Button>

				<Button type="submit" class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse" size="sm" @click.prevent="createUser" variant="primary">
					New User
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
						<TableHead class="w-[5%]">
							<Button class="flex justify-start w-full p-0 hover:bg-rcgray-800" variant="ghost" @click="toggleSort('id')">
								<RcIcon name="sort" :sortParam="sortParam" field="id" />
								<span class="ml-2">ID</span>
							</Button>
						</TableHead>
						<TableHead class="w-[8%]"> User Type </TableHead>
						<TableHead class="w-[10%]">
							<Button class="flex justify-start w-full p-0 hover:bg-rcgray-800" variant="ghost" @click="toggleSort('name')">
								<RcIcon name="sort" :sortParam="sortParam" field="name" />
								<span class="ml-2">Name</span>
							</Button>
						</TableHead>
						<TableHead class="w-[20%]">
							<Button class="flex justify-start w-full p-0 hover:bg-rcgray-800" variant="ghost" @click="toggleSort('email')">
								<RcIcon name="sort" :sortParam="sortParam" field="email" />
								<span class="ml-2">Email</span>
							</Button>
						</TableHead>
						<TableHead>Notifications</TableHead>
						<TableHead>SSO Approved</TableHead>
						<TableHead class="w-[20%]">
							<Button class="flex justify-start w-full p-0 hover:bg-rcgray-800" variant="ghost" @click="toggleSort('last_login')">
								<RcIcon name="sort" :sortParam="sortParam" field="last_login" />
								<span class="ml-2">Last Login</span>
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
						<TableRow v-for="row in users.data" :key="row.id">
							<TableCell class="text-start">
								<Checkbox class="cursor-pointer" :id="'select-' + row.id" :checked="selectedRows.includes(row.id) ? true : false" @click="toggleSelectRow(row.id)" />
							</TableCell>
							<TableCell class="text-start">
								{{ row.id }}
							</TableCell>
							<TableCell class="text-start">
								<RcBadge v-if="row.is_socialite" variant="secondary" class="ml-2 px-2 flex items-center gap-1" title="SSO User">
									<ShieldUser class="h-3 w-3" />
									<span class="text-xs">SSO</span>
								</RcBadge>
								<RcBadge v-else variant="outline" class="ml-2 px-2 flex items-center gap-1" title="Local User">
									<User class="h-3 w-3" />
									<span class="text-xs">Local</span>
								</RcBadge>
							</TableCell>
							<TableCell class="text-start">
								{{ row.name }}
							</TableCell>
							<TableCell class="text-start">
								{{ row.email }}
							</TableCell>
							<TableCell class="text-start">
								<Switch :id="`notif-${row.id}`" :checked="row.get_notifications === 1" @update:checked="toggleNotification(row.id, $event ? 1 : 0)" />
							</TableCell>
							<TableCell class="text-start">
								<Switch :id="`socialite-${row.id}`" v-model:checked="row.is_socialite_approved" @update:checked="toggleSocialiteApproved(row.id, $event ? 1 : 0)" />
							</TableCell>
							<TableCell class="text-start">
								{{ row.last_login }}
							</TableCell>
							<!-- ACTIONS MENU -->
							<TableCell class="text-start">
								<ActionsMenu :rowData="row" @onEdit="viewEditDialog(row.id)" @onDelete="deleteUser(row.id)" />
							</TableCell>
							<!-- ACTIONS MENU -->
						</TableRow>
					</template>
					<template v-else>
						<NoResults />
					</template>
				</TableBody>
			</Table>

			<Pagination :currentPage="currentPage" :lastPage="lastPage" :perPage="perPage" @update:currentPage="currentPage = $event" @update:perPage="perPage = $event" :totalRecords="users.total" :isLoading="isLoading" />
			<UserAddEditDialog @save="handleSave()" :key="newUserModalKey" :editId="editId" />
			<RcConfirmAlertDialog :ids="selectedRows" :showConfirmDelete="showConfirmDelete" @close="showConfirmDelete = false" @handleDelete="deleteManyUsers(selectedRows)" />
			<RelatedDocumentationNav :docs="relatedDocs" v-if="relatedDocs.length > 0" />
		</div>
	</div>
</template>