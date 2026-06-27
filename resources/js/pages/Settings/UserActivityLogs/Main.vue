<script setup>
import ClearFilters from "@/pages/Shared/Filters/ClearFilters.vue";
import RcConfirmAlertDialog from "@/pages/Shared/ConfirmAlertDialog/RcConfirmAlertDialog.vue";

import Loading from "@/pages/Shared/Table/Loading.vue";
import NoResults from "@/pages/Shared/Table/NoResults.vue";
import Pagination from "@/pages/Shared/Table/Pagination.vue";
import UserActivityLogsTableHoverCard from "@/pages/Settings/UserActivityLogs/UserActivityLogsTableHoverCard.vue";

import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { useRowSelection } from "@/composables/useRowSelection";
import { useUserActivityLogs } from "@/pages/Settings/UserActivityLogs/useUserActivityLogs";

const { reload, logs, isLoading, currentPage, perPage, lastPage, searchTerm, toggleSort, sortParam, navigateToUsers, showConfirmDelete, deleteManyUserLogs } = useUserActivityLogs();
const { selectedRows, selectAll, toggleSelectAll, toggleSelectRow } = useRowSelection(logs);
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
					placeholder="Filter users..."
				/>
				<ClearFilters
					v-if="searchTerm"
					@update:model-value="searchTerm = ''"
				/>
			</div>
			<div class="flex items-center justify-end">
				<!-- Activity Log Button -->
				<Button
					class="px-2 py-1 ml-4 mr-2 text-sm hover:animate-pulse flex items-center"
					size="sm"
					variant="outline"
					@click="navigateToUsers"
				>
					<RcIcon
						name="user"
						class="h-4 w-4 mr-2"
					/>
					Back to Users
				</Button>

				<Button
					v-if="selectedRows.length"
					class="px-2 py-1 bg-red-600 hover:bg-red-700 hover:animate-pulse"
					size="md"
					variant="primary"
					@click.prevent="showConfirmDelete = true"
				>
					Delete Selected {{ selectedRows.length }} Log(s)
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
						<TableHead class="w-[20%]">
							<Button
								class="flex justify-start w-full p-0 hover:bg-rcgray-800"
								variant="ghost"
								@click="toggleSort('user')"
							>
								<RcIcon
									name="sort"
									:sort-param="sortParam"
									field="user"
								/>
								<span class="ml-2">User</span>
							</Button>
						</TableHead>
						<TableHead class="w-[20%]">
							<Button
								class="flex justify-start w-full p-0 hover:bg-rcgray-800"
								variant="ghost"
								@click="toggleSort('subject')"
							>
								<RcIcon
									name="sort"
									:sort-param="sortParam"
									field="subject"
								/>
								<span class="ml-2">Detail</span>
							</Button>
						</TableHead>
						<TableHead class="w-[20%]">
							<Button
								class="flex justify-start w-full p-0 hover:bg-rcgray-800"
								variant="ghost"
								@click="toggleSort('ip')"
							>
								<RcIcon
									name="sort"
									:sort-param="sortParam"
									field="ip"
								/>
								<span class="ml-2">IP Address</span>
							</Button>
						</TableHead>
						<TableHead class="w-[20%]">
							<Button
								class="flex justify-start w-full p-0 hover:bg-rcgray-800"
								variant="ghost"
								@click="toggleSort('created_at')"
							>
								<RcIcon
									name="sort"
									:sort-param="sortParam"
									field="created_at"
								/>
								<span class="ml-2">Activity Date</span>
							</Button>
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
							v-for="row in logs.data"
							:key="row.id"
						>
							<TableCell class="text-start">
								{{ row.id }}
							</TableCell>

							<TableCell class="text-start">
								<Checkbox
									:id="'select-' + row.id"
									class="cursor-pointer"
									:checked="selectedRows.includes(row.id) ? true : false"
									@click="toggleSelectRow(row.id)"
								/>
							</TableCell>

							<TableCell class="text-start">
								{{ row.user && row.user.name ? row.user.name : "Unknown User" }}
							</TableCell>

							<TableCell class="text-start">
								{{ row.subject }}
							</TableCell>
							<TableCell class="text-start">
								{{ row.ip }}
							</TableCell>
							<TableCell class="text-start">
								{{ row.created_at }}
							</TableCell>
							<TableCell>
								<UserActivityLogsTableHoverCard :log="row">
									<Button
										variant="outline"
										class="h-6 p-1"
									>
										<div class="flex items-center">
											<RcIcon
												name="log-severity"
												:severity="'info'"
												size="sm"
											/>
											View
										</div>
									</Button>
									<template #leftIcon>
										<RcIcon
											name="log-severity"
											:severity="'info'"
											size="md"
										/>
									</template>
								</UserActivityLogsTableHoverCard>
							</TableCell>
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
				:total-records="logs.total"
				:is-loading="isLoading"
				@update:current-page="currentPage = $event"
				@update:per-page="perPage = $event"
			/>
			<RcConfirmAlertDialog
				:ids="selectedRows"
				:show-confirm-delete="showConfirmDelete"
				@close="showConfirmDelete = false"
				@handle-delete="deleteManyUserLogs(selectedRows)"
			/>
		</div>
	</div>
</template>
