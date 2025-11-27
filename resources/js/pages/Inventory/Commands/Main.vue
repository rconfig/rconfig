<script setup>
import ActionsMenu from "@/pages/Shared/Table/ActionsMenu.vue";
import BadgeList from "@/pages/Shared/Table/BadgeList.vue";
import ClearFilters from "@/pages/Shared/Filters/ClearFilters.vue";
import CommandAddEditDialog from "@/pages/Inventory/Commands/CommandAddEditDialog.vue";
import CommandsBulkUpdateModal from "@/pages/Inventory/Commands/CommandsBulkUpdateModal.vue";
import GenericPopover from "@/pages/Shared/Popover/GeneralPopover.vue";
import Loading from "@/pages/Shared/Table/Loading.vue";
import NoResults from "@/pages/Shared/Table/NoResults.vue";
import Pagination from "@/pages/Shared/Table/Pagination.vue";
import RcConfirmAlertDialog from "@/pages/Shared/ConfirmAlertDialog/RcConfirmAlertDialog.vue";
import RcToolTip from "@/pages/Shared/Tooltips/RcToolTip.vue";
import { SquareTerminal, FileJson2, Bell, MessageCircleQuestion, Shredder } from "lucide-vue-next";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { ToggleGroup, ToggleGroupItem } from "@/components/ui/toggle-group";
import { eventBus } from "@/composables/eventBus";
import { onMounted, onUnmounted, computed } from "vue";
import { useCommands } from "@/pages/Inventory/Commands/useCommands";
import { useRowSelection } from "@/composables/useRowSelection";

const {
	// state
	commands,
	currentPage,
	editId,
	isLoading,
	lastPage,
	newCommandModalKey,
	newBulkUpdateCommandsKey,
	perPage,
	searchTerm,
	showConfirmDelete,
	sortParam,

	// methods
	createCommand,
	deleteCommand,
	deleteManyCommands,
	fetchCommands,
	handleKeyDown,
	handleSave,
	reload,
	toggleSort,
	viewEditDialog,
	openBulkUpdateDialog,
	handleCloseBulkUpdate,
	handleUpdateBulkCommands,
	updateChangeNotification,
	updateSaveConfig,
} = useCommands();

const { selectedRows, selectAll, toggleSelectAll, toggleSelectRow } = useRowSelection(commands);

onMounted(() => {
	fetchCommands();
	window.addEventListener("keydown", handleKeyDown);

	eventBus.on("deleteManyCommandsSuccess", () => {
		selectedRows.value = [];
		selectAll.value = false;
	});
});

onUnmounted(() => {
	window.removeEventListener("keydown", handleKeyDown);
});

const selectedRecords = computed(() => {
	return (commands.value?.data || []).filter((row) => selectedRows.value.includes(row.id));
});
</script>

<template>
	<div class="flex flex-col h-full gap-1 text-center">
		<div class="flex items-center justify-between p-4">
			<div class="flex items-center">
				<Input
					class="max-w-sm ml-4 mr-2"
					autocomplete="off"
					data-1p-ignore
					data-lpignore="true"
					placeholder="Filter commands..."
					v-model="searchTerm"
				/>
				<ClearFilters v-if="searchTerm" @update:model-value="searchTerm = ''" />
			</div>

			<div class="flex items-center justify-end">
				<Button
					type="submit"
					class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
					size="sm"
					@click.prevent="createCommand"
					variant="primary"
				>
					New Command
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
							<Button class="flex justify-start w-full p-0 hover:bg-rcgray-800" variant="ghost" @click="toggleSort('command')">
								<RcIcon name="sort" :sortParam="sortParam" field="command" />
								<span class="ml-2">Command</span>
							</Button>
						</TableHead>
						<TableHead class="w-[20%]">Description</TableHead>
						<TableHead class="w-[20%]">Command Groups</TableHead>
						<TableHead class="w-[20%]">
							Options
							<GenericPopover
								:title="'Command Options'"
								:description="'Enable or Disable change notifications.'"
								:href="$rconfigDocsUrl + '/devices/commands/'"
								:linkText="'Command Docs'"
								:align="'end'"
							>
								<template #trigger>
									<Button variant="link" size="sm" class="text-blue-400 hover:text-blue-300 p-0">
										<MessageCircleQuestion class="h-3.5 w-3.5 ml-1" />
									</Button>
								</template>
							</GenericPopover>
						</TableHead>
						<TableHead class="w-[10%]">Actions</TableHead>
					</TableRow>
				</TableHeader>

				<TableBody>
					<template v-if="isLoading">
						<Loading />
					</template>

					<template v-else-if="!isLoading">
						<TableRow v-for="(row, index) in commands.data" :key="row.id">
							<TableCell class="text-start">
								<Checkbox
									class="cursor-pointer"
									:id="'select-' + row.id"
									:checked="selectedRows.includes(row.id) ? true : false"
									@click="toggleSelectRow(row.id)"
								/>
							</TableCell>

							<!-- ID column (replacing previous CmLockTooltip) -->
							<TableCell class="text-start">
								{{ row.id }}
							</TableCell>

							<TableCell class="text-start">
								{{ row.command }}
							</TableCell>

							<TableCell class="text-start">
								{{ row.description }}
							</TableCell>

							<TableCell class="text-start">
								<BadgeList :items="row.category" displayField="categoryName" linkField="view_url" :maxVisible="8" :hoverCardFields="['id', 'categoryName']" />
							</TableCell>

							<TableCell class="text-start">
								<ToggleGroup type="multiple">
									<!-- Change Notifications -->
									<ToggleGroupItem
										value="notify"
										:data-state="row.change_notification ? 'on' : 'off'"
										@click="updateChangeNotification(row, row.id, row.change_notification)"
										class="rounded p-1 px-2 transition-colors rc-btn-shadow data-[state=on]:bg-amber-400 data-[state=off]:bg-gray-800 hover:data-[state=off]:bg-gray-700 hover:data-[state=on]:bg-amber-300"
									>
										<RcToolTip :delayDuration="100" :content="row.change_notification ? 'Change Notifications: On' : 'Change Notifications: Off'" side="bottom">
											<template #trigger>
												<Bell class="h-4 w-4 focus:outline-none" :class="row.change_notification ? 'text-black' : 'text-amber-300'" />
											</template>
										</RcToolTip>
									</ToggleGroupItem>
								</ToggleGroup>
							</TableCell>

							<!-- ACTIONS MENU -->
							<TableCell class="text-start">
								<ActionsMenu :rowData="row" @onEdit="viewEditDialog(row.id)" @onDelete="deleteCommand(row.id)" />
							</TableCell>
							<!-- ACTIONS MENU -->
						</TableRow>
					</template>

					<template v-else>
						<NoResults />
					</template>
				</TableBody>
			</Table>

			<!-- FLOATING ACTION BAR -->
			<div
				v-if="selectedRows.length"
				class="fixed bottom-5 left-1/2 transform -translate-x-1/2 z-50 flex items-center gap-4 bg-rcgray-900 text-white border border-rcgray-600 shadow-xl px-4 py-1.5 rounded-lg"
			>
				<span class="text-sm">
					<RcBadge variant="primary" class="mr-1">{{ selectedRows.length }}</RcBadge> command(s) selected
				</span>

				<Button class="text-sm px-1.5 py-0 hover:animate-pulse h-8" variant="outline" @click="openBulkUpdateDialog">
					<RcIcon name="command-group" class="inline-block mr-0 md:mr-2 ml-0" size="14" />
					<span class="hidden md:inline-flex">Bulk assign command group</span>
				</Button>

				<Button class="text-sm px-1.5 py-0 bg-red-600 hover:bg-red-700 hover:animate-pulse h-8" @click.prevent="showConfirmDelete = true" variant="primary">
					<Shredder class="inline-block mr-0 md:mr-2 ml-0" size="14" />
					<span class="hidden md:inline-flex mr-1">Delete {{ selectedRows.length }}</span>
				</Button>
			</div>

			<Pagination
				:currentPage="currentPage"
				:lastPage="lastPage"
				:perPage="perPage"
				@update:currentPage="currentPage = $event"
				@update:perPage="perPage = $event"
				:totalRecords="commands.total"
				:isLoading="isLoading"
			/>
			<CommandAddEditDialog @save="handleSave()" :key="newCommandModalKey" :editId="editId" />
			<CommandsBulkUpdateModal :key="newBulkUpdateCommandsKey" :editId="editId" :data="selectedRecords" @close="handleCloseBulkUpdate" @updated="handleUpdateBulkCommands" />
			<RcConfirmAlertDialog :ids="selectedRows" :showConfirmDelete="showConfirmDelete" @close="showConfirmDelete = false" @handleDelete="deleteManyCommands(selectedRows)" />
		</div>
	</div>
</template>
