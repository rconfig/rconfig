<script setup>
import ActionsMenu from "@/pages/Shared/Table/ActionsMenu.vue";
import BadgeList from "@/pages/Shared/Table/BadgeList.vue";
import ClearFilters from "@/pages/Shared/Filters/ClearFilters.vue";
import Loading from "@/pages/Shared/Table/Loading.vue";
import NoResults from "@/pages/Shared/Table/NoResults.vue";
import Pagination from "@/pages/Shared/Table/Pagination.vue";
import RcConfirmAlertDialog from "@/pages/Shared/ConfirmAlertDialog/RcConfirmAlertDialog.vue";
import RcRoleDialog from "@/pages/Shared/Forms/RcRoleDialog.vue";
import TagAddEditDialog from "@/pages/Inventory/Tags/TagAddEditDialog.vue";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { eventBus } from "@/composables/eventBus";
import { onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import { useRowSelection } from "@/composables/useRowSelection";
import { useTags } from "@/pages/Inventory/Tags/useTags";

const {
	// State
	reload,
	tags,
	isLoading,
	currentPage,
	perPage,
	lastPage,
	editId,
	newTagModalKey,
	newRoleModalKey,
	searchTerm,
	showConfirmDelete,

	// Dialogs & Actions
	openDialog,
	fetchTags,
	createTag,
	updateTag,
	deleteTag,
	deleteManyTags,
	handleSave,
	handleKeyDown,
	handleRoleAssignment,
	viewEditDialog,
	toggleSort,
	sortParam,
} = useTags();
const { selectedRows, selectAll, toggleSelectAll, toggleSelectRow } = useRowSelection(tags);
const router = useRouter();

onMounted(() => {
	if (router.currentRoute?.value.params?.id) {
		editId.value = parseInt(router.currentRoute.value.params.id);
		viewEditDialog(editId.value);
	}

	fetchTags();
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
				<Input class="max-w-sm ml-4 mr-2" autocomplete="off" data-1p-ignore data-lpignore="true" placeholder="Filter tags..." v-model="searchTerm" />
				<ClearFilters v-if="searchTerm" @update:model-value="searchTerm = ''" />
			</div>
			<div class="flex items-center justify-end">
				<Button v-if="selectedRows.length" class="px-2 py-1 bg-red-600 hover:bg-red-700 hover:animate-pulse" size="md" @click.prevent="showConfirmDelete = true" variant="primary">Delete Selected {{ selectedRows.length }} Tag(s) </Button>
				<Button type="submit" class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse" size="sm" @click.prevent="createTag" variant="primary">
					New Tag
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
							<Button class="flex justify-start w-full p-0 hover:bg-rcgray-800" variant="ghost" @click="toggleSort('tagname')">
								<RcIcon name="sort" :sortParam="sortParam" field="tagname" />
								<span class="ml-2">Name</span>
							</Button>
						</TableHead>
						<TableHead class="w-[20%]">Description</TableHead>
						<TableHead class="w-[40%]">Devices</TableHead>
						<TableHead class="w-[20%]">Roles</TableHead>
						<TableHead class="w-[10%]">Actions</TableHead>
					</TableRow>
				</TableHeader>
				<TableBody>
					<template v-if="isLoading">
						<Loading />
					</template>

					<template v-else-if="!isLoading">
						<TableRow v-for="row in tags.data" :key="row.id">
							<TableCell class="text-start">
								<Checkbox class="cursor-pointer" :id="'select-' + row.id" :checked="selectedRows.includes(row.id) ? true : false" @click="toggleSelectRow(row.id)" />
							</TableCell>

							<TableCell class="text-start">
								{{ row.id }}
							</TableCell>
							<TableCell class="text-start">
								{{ row.tagname }}
							</TableCell>
							<TableCell class="text-start">
								{{ row.tagDescription }}
							</TableCell>
							<TableCell class="text-start">
								<BadgeList :items="row.device" displayField="device_name" linkField="view_url" :maxVisible="8" :hoverCardFields="['id', 'device_name', 'device_ip']" />
							</TableCell>

							<!-- Roles cell -->
							<TableCell class="text-start">
								<BadgeList :items="row.roles" displayField="name" linkString="/settings/roles" :maxVisible="3" :hoverCardFields="['id', 'name', 'description']" />
							</TableCell>

							<!-- ACTIONS MENU -->
							<TableCell class="text-start">
								<ActionsMenu :rowData="row" @onEdit="viewEditDialog(row.id)" @onDelete="deleteTag(row.id)" :showRolesBtn="true" @onRoleAssignment="handleRoleAssignment(row.id)" />
							</TableCell>
							<!-- ACTIONS MENU -->
						</TableRow>
					</template>
					<template v-else>
						<NoResults />
					</template>
				</TableBody>
			</Table>

			<Pagination :currentPage="currentPage" :lastPage="lastPage" :perPage="perPage" @update:currentPage="currentPage = $event" @update:perPage="perPage = $event" :totalRecords="tags.total" :isLoading="isLoading" />
			<TagAddEditDialog @save="handleSave()" :key="newTagModalKey" :editId="editId" />
			<RcConfirmAlertDialog :ids="selectedRows" :showConfirmDelete="showConfirmDelete" @close="showConfirmDelete = false" @handleDelete="deleteManyTags(selectedRows)" />
			<RcRoleDialog :itemId="editId" v-if="editId > 0" :key="newRoleModalKey" :type="2" @updateRoles="fetchTags()" />
		</div>
	</div>
</template>