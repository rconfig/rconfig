<script setup>
import ActionsMenu from "@/pages/Shared/Table/ActionsMenu.vue";
import BadgeList from "@/pages/Shared/Table/BadgeList.vue";
import CategoryCell from "@/pages/Shared/Table/CategoryCell.vue";
import ClearFilters from "@/pages/Shared/Filters/ClearFilters.vue";
import CommandGroupAddEditDialog from "@/pages/Inventory/CommandGroups/CommandGroupAddEditDialog.vue";
import Loading from "@/pages/Shared/Table/Loading.vue";
import NoResults from "@/pages/Shared/Table/NoResults.vue";
import Pagination from "@/pages/Shared/Table/Pagination.vue";
import RcConfirmAlertDialog from "@/pages/Shared/ConfirmAlertDialog/RcConfirmAlertDialog.vue";
import RcToolTip from "@/pages/Shared/Tooltips/RcToolTip.vue";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { eventBus } from "@/composables/eventBus";
import { onMounted, onUnmounted } from "vue";
import { useCommandGroups } from "@/pages/Inventory/CommandGroups/useCommandGroups";
import { useRouter } from "vue-router";
import { useRowSelection } from "@/composables/useRowSelection";
import RcBadge from "@/pages/Shared/Badges/RcBadge.vue";

const { reload, editId, categories, currentPage, perPage, searchTerm, lastPage, isLoading, fetchCommandGroups, viewEditDialog, createCommandGroup, deleteCommandGroup, deleteManyCommandGroups, handleSave, showConfirmDelete, handleKeyDown, newCommandGroupsModalKey, toggleSort, sortParam } = useCommandGroups();
const { selectedRows, selectAll, toggleSelectAll, toggleSelectRow } = useRowSelection(categories);
const router = useRouter();

onMounted(() => {
	fetchCommandGroups();

	if (router.currentRoute?.value.params?.id) {
		editId.value = parseInt(router.currentRoute.value.params.id);
		viewEditDialog(editId.value);
	}

	window.addEventListener("keydown", handleKeyDown);

	eventBus.on("deleteManyCommandGroupsSuccess", () => {
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
		<div class="hidden text-yellow-200 text-teal-100 bg-yellow-700 bg-teal-700 border-yellow-500 border-teal-500 bg-stone-700 text-stone-200 border-stone-500 bg-lime-700 text-lime-200 border-lime-500 bg-sky-700 text-sky-100 border-sky-500 bg-violet-700 text-violet-200 border-violet-500 bg-fuchsia-700 text-fuchsia-200 border-fuchsia-500"></div>

		<div class="flex items-center justify-between p-4">
			<div class="flex items-center">
				<Input
					v-model="searchTerm"
					class="max-w-sm ml-4 mr-2"
					autocomplete="off"
					data-1p-ignore
					data-lpignore="true"
					placeholder="Filter Command Groups..."
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
					Delete Selected {{ selectedRows.length }} Command Group(s)
				</Button>
				<Button
					type="submit"
					class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
					size="sm"
					variant="primary"
					@click.prevent="createCommandGroup"
				>
					New Command Group
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
						<TableHead class="w-[10%]">
							<Button
								class="flex justify-start w-full p-0 hover:bg-rcgray-800"
								variant="ghost"
								@click="toggleSort('categoryName')"
							>
								<RcIcon
									name="sort"
									:sort-param="sortParam"
									field="categoryName"
								/>
								<span class="ml-2">Name</span>
							</Button>
						</TableHead>
						<TableHead class="w-[20%]">
							Commands
						</TableHead>
						<TableHead class="w-[40%]">
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

					<template v-else-if="!isLoading">
						<TableRow
							v-for="row in categories.data"
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
							<TableCell class="text-start">
								<CategoryCell
									:category-name="row.categoryName"
									:category-description="row.categoryDescription"
									:badge-color="row.badgeColor"
									:word-limit="8"
								/>
							</TableCell>
							<TableCell class="text-start">
								<RcToolTip
									v-if="row.command.length == 0"
									:delay-duration="100"
									content="No commands attached to this command group"
									side="bottom"
								>
									<template #trigger>
										<RcBadge
											class="ml-1 px-2"
											variant="warning"
											size="sm"
										>
											0 Commands
										</RcBadge>
									</template>
								</RcToolTip>
								<BadgeList
									:items="row.command"
									display-field="command"
									link-field="view_url"
									:max-visible="8"
									:hover-card-fields="['id', 'command']"
									:show-empty-text="false"
								/>
							</TableCell>
							<TableCell class="text-start">
								<BadgeList
									:items="row.device"
									display-field="device_name"
									link-field="view_url"
									:max-visible="8"
									:hover-card-fields="['id', 'device_name', 'device_ip']"
								/>
							</TableCell>
							<!-- ACTIONS MENU -->
							<TableCell class="text-start">
								<ActionsMenu
									:row-data="row"
									@on-edit="viewEditDialog(row.id)"
									@on-delete="deleteCommandGroup(row.id)"
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
				:total-records="categories.total"
				:is-loading="isLoading"
				@update:current-page="currentPage = $event"
				@update:per-page="perPage = $event"
			/>
			<CommandGroupAddEditDialog
				:key="newCommandGroupsModalKey"
				:edit-id="editId"
				@save="handleSave()"
			/>
			<RcConfirmAlertDialog
				:ids="selectedRows"
				:show-confirm-delete="showConfirmDelete"
				@close="showConfirmDelete = false"
				@handle-delete="deleteManyCommandGroups(selectedRows)"
			/>
		</div>
	</div>
</template>