<script setup>
import Loading from '@/pages/Shared/Table/Loading.vue';
import PeekConfigDialog from '@/pages/Shared/Dialogs/PeekConfigDialog.vue';
import PeekConfigSearchMatchesDialog from '@/pages/Shared/Dialogs/PeekConfigSearchMatchesDialog.vue';
import { Button } from "@/components/ui/button";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import Pagination from '@/pages/Shared/Table/Pagination.vue';
import { Eye, FileSearch } from "lucide-vue-next";
import { useResultsTable } from './useResultsTable';

const props = defineProps({
  filters: Object
});
const { changePage, currentPage, errors, formatters, isDialogOpen, isFetching, lastPage, openDialog, perPage, results, searchModel, updatePerpage, viewDetailsPane } = useResultsTable(props);
</script>

<template>
	<div class="px-6">
		<Table>
			<TableHeader>
				<TableRow>
					<TableHead class="w-[5%]">
						ID
					</TableHead>
					<TableHead class="w-[5%]">
						Device
					</TableHead>
					<TableHead class="w-[10%]">
						Command Group
					</TableHead>
					<TableHead class="w-[10%]">
						Command
					</TableHead>
					<TableHead class="w-[10%]">
						Filesize
					</TableHead>
					<TableHead class="w-[10%]">
						Config date
					</TableHead>
					<TableHead class="w-[10%]">
						Matches
					</TableHead>
					<TableHead class="w-[10%]">
						Actions
					</TableHead>
				</TableRow>
			</TableHeader>
			<TableBody>
				<Loading v-if="isFetching" />

				<template v-else>
					<TableRow
						v-for="row in results"
						:key="row.id"
					>
						<TableCell class="text-start">
							{{ row.id }}
						</TableCell>
						<TableCell class="text-start">
							{{ row.device_name }}
						</TableCell>
						<TableCell class="text-start">
							{{ row.device_category }}
						</TableCell>
						<TableCell class="text-start">
							{{ row.command }}
						</TableCell>
						<TableCell class="text-start">
							{{ formatters.formatFileSize(row.config_filesize) }}
						</TableCell>
						<TableCell class="text-start">
							{{ formatters.formatTime(row.config_date) }}
						</TableCell>
						<TableCell class="text-start">
							<TooltipProvider>
								<Tooltip>
									<TooltipTrigger as-child>
										<Button
											variant="ghost"
											class="px-2 py-0 hover:bg-rcgray-800 rounded-xl"
											@click="openDialog('peek-config-search-matches-dialog-' + row.id)"
										>
											<span class="flex items-center border-b">
												<Eye
													class="mr-2 text-muted-foreground hover:text-blue-500"
													size="16"
												/>
												{{ row.matches.length ? row.matches.length : 'No' }} match{{ row.matches.length > 1 ? 'es' : '' }}
											</span>
										</Button>
									</TooltipTrigger>
									<TooltipContent class="text-white bg-rcgray-800">
										<p>View Matches</p>
									</TooltipContent>
								</Tooltip>
							</TooltipProvider>
						</TableCell>
						<TableCell class="flex items-center">
							<TooltipProvider>
								<Tooltip>
									<TooltipTrigger as-child>
										<Button
											variant="ghost"
											@click="openDialog('peek-config-dialog-' + row.id)"
										>
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
										<Button
											variant="ghost"
											@click="viewDetailsPane(row.id)"
										>
											<FileSearch class="size-5 text-muted-foreground hover:text-blue-500" />
										</Button>
									</TooltipTrigger>
									<TooltipContent class="text-white bg-rcgray-800">
										<p>Open Config</p>
									</TooltipContent>
								</Tooltip>
							</TooltipProvider>
							<PeekConfigSearchMatchesDialog
								v-if="isDialogOpen('peek-config-search-matches-dialog-' + row.id)"
								:record="row"
								:edit-id="row.id"
								:search-string="searchModel.search_string"
							/>

							<PeekConfigDialog
								v-if="isDialogOpen('peek-config-dialog-' + row.id)"
								:edit-id="row.id"
							></PeekConfigDialog>
						</TableCell>
					</TableRow>
				</template>
			</TableBody>
		</Table>

		<div
			v-if="!isFetching && results.length === 0"
			class="flex items-center justify-center my-4"
		>
			<div v-if="Object.keys(errors).length === 0">
				No results found.
			</div>
			<div v-if="Object.keys(errors).length">
				<span
					v-for="error in errors"
					:key="error"
					class="col-span-3 col-start-2 text-sm text-red-400"
				>
					<br />
					{{ error[0] }}
				</span>
			</div>
		</div>

		<Pagination
			v-if="!isFetching && results.length > 0"
			:current-page="currentPage"
			:last-page="lastPage"
			:per-page="perPage"
			:total-records="results.total"
			:is-loading="isFetching"
			@update:current-page="currentPage = $event"
			@update:per-page="perPage = $event"
		/>

		<div
			v-if="!isFetching && currentPage >= lastPage"
			class="flex items-center justify-center pb-8"
		>
			<hr class="flex-grow mx-8" />
			<div class="text-muted-foreground">
				No more results to load.
			</div>
			<hr class="flex-grow mx-8" />
		</div>
	</div>
</template>
