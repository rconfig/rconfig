<script setup>
import ActionsMenu from "@/pages/Shared/Table/ActionsMenu.vue";
import AlertTip from "@/pages/Shared/Alerts/AlertTip.vue";
import ClearFilters from "@/pages/Shared/Filters/ClearFilters.vue";
import Loading from "@/pages/Shared/Loaders/Loading.vue";
import NoResults from "@/pages/Shared/Table/NoResults.vue";
import Pagination from "@/pages/Shared/Table/Pagination.vue";
import TableLoading from "@/pages/Shared/Table/Loading.vue";

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { CircleX, Check, FileX, ClipboardList } from "lucide-vue-next";
import { ExternalLinkComponent } from "@/pages/Shared/Links";
import { Input } from "@/components/ui/input";
import { Select, SelectTrigger, SelectContent, SelectItem, SelectValue } from "@/components/ui/select";
import { Separator } from "@/components/ui/separator";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { useDataPurgePanel } from "./useDataPurgePanel";
import { useRouter } from "vue-router";

const {
	// state
	configsSetting,
	otherSettings,
	configsSaving,
	configsSaveError,
	loading,
	error,
	filters,
	searchTerm,
	editingId,
	editForm,
	savingEdit,
	cleanupFrequencyOptions,
	sortParam,
	totalRecords,
	lastPage,
	formatters,
	showTip,
	configsEditForm,

	// methods
	getOptionValue,
	toggleSort,
	handlePageChange,
	handlePerPageChange,
	startEdit,
	cancelEdit,
	saveEdit,
	toggleEnableDisable,
	applyEnabledFilter,
	refreshSettings,
	clearSearch,
	closeTip,
	purgeAllFailedConfigs,
	purgeAllFailedLoading,
} = useDataPurgePanel();

const router = useRouter();

function navToRunHistory() {
	router.push("/settings/data-purge/run-history");
}
</script>

<template>
	<div class="container max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="flex flex-col items-center w-full gap-4 w-full">
			<div class="grid w-full max-w-full items-center gap-1.5">
				<h3 class="rc-panel-heading">
					Data Purge and Management
				</h3>
				<p class="text-sm text-muted-foreground">
					Manage your data retention policies and ensure compliance with your organization's data management standards. This section provides tools to help you purge old data efficiently and securely.
				</p>

				<div class="flex justify-between">
					<RcBadge variant="secondary" class="my-2" :interactive="false"><b>Note: </b> &nbsp;The purge process runs nightly at 2:30 AM by default.</RcBadge>

					<div class="flex items-center gap-2">
						<Button size="sm" variant="outline" class="text-amber-500 border-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/20 hover:text-amber-600 flex items-center" :disabled="purgeAllFailedLoading" @click="purgeAllFailedConfigs()">
							<FileX class="h-4 w-4 mr-2" />
							{{ purgeAllFailedLoading ? "Queuing..." : "Purge All Failed Configs" }}
						</Button>
						<Button class="px-2 py-1 text-sm hover:animate-pulse flex items-center" size="sm" variant="outline" @click="navToRunHistory()">
							<ClipboardList class="h-4 w-4 mr-2" />
							Purge Run History
						</Button>
					</div>
				</div>

				<Separator class="my-6" />

				<!-- Special Configs Purge Card -->
				<Card v-if="configsSetting && configsEditForm" class="w-full mb-6" :class="!configsEditForm.enabled ? 'bg-red-50 dark:bg-red-950/10 border-red-300 dark:border-red-800' : 'border-green-500 dark:border-green-600'">
					<CardHeader>
						<CardTitle
							><div class="flex items-center justify-between">
								<div class="flex items-center justify-between">
									<span>Config Purge Settings</span>
									<div class="ml-2">
										<RcBadge variant="success" v-if="configsEditForm.enabled" :interactive="false">Enabled</RcBadge>
										<RcBadge variant="danger" v-else :interactive="false">Disabled</RcBadge>
									</div>
								</div>

								<div class="flex flex-col items-start space-y-2">
									<div v-if="configsSaveError" class="flex items-center space-x-2 text-sm text-red-500 dark:text-red-400">
										<div class="w-2 h-2 rounded-full bg-red-500"></div>
										<span>Not saved: {{ configsSaveError }}</span>
									</div>
									<div v-else class="flex items-center space-x-2 text-sm" :class="configsSaving ? 'text-blue-500 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400'">
										<div class="w-2 h-2 rounded-full" :class="configsSaving ? 'bg-blue-500 animate-pulse' : 'bg-gray-400'"></div>
										<span>{{ configsSaving ? "Saving changes…" : "Changes auto-saved" }}</span>
									</div>
								</div>
							</div></CardTitle
						>
						<CardDescription
							><div class="flex items-center justify-between">
								<span>Retention for downloaded configs and their change (diff) records. The latest version and the newest versions of each device command are always kept.</span>
								<ExternalLinkComponent :to="$rconfigDocsUrl + '/system-management/data-purge/'" text="Documentation" :active="true" class="mt-1 ml-8" /></div
						></CardDescription>
					</CardHeader>
					<CardContent>
						<div v-if="configsEditForm && typeof configsEditForm.retention_days !== 'undefined'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
							<div class="flex flex-col gap-4">
								<div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
									<Label>Retention Days:</Label>
									<Input v-model.number="configsEditForm.retention_days" type="number" min="1" class="w-full" />
								</div>

								<div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
									<Label>Batch Size:</Label>
									<Input v-model.number="configsEditForm.batch_size" type="number" min="1" class="w-full" />
								</div>
								<div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
									<Label>Cleanup Frequency:</Label>
									<Select v-model="configsEditForm.cleanup_frequency" class="w-full">
										<SelectTrigger>
											<SelectValue placeholder="Select frequency" />
										</SelectTrigger>
										<SelectContent>
											<SelectItem value="daily">Daily</SelectItem>
											<SelectItem value="weekly">Weekly</SelectItem>
											<SelectItem value="monthly">Monthly</SelectItem>
										</SelectContent>
									</Select>
								</div>
							</div>
							<div class="flex flex-col gap-4">
								<div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
									<Label>Failed Download Retention Days:</Label>
									<Input v-model.number="configsEditForm.non_downloaded_retention_days" type="number" min="1" class="w-full" />
								</div>
								<div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
									<Label>Min Versions Per Device Command:</Label>
									<Input v-model.number="configsEditForm.min_versions_per_device_command" type="number" min="1" class="w-full" />
								</div>
								<div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
									<Label for="enabled">Enabled:</Label>
									<Switch :checked="configsEditForm.enabled" @update:checked="(val) => (configsEditForm.enabled = val)" />
								</div>
							</div>
						</div>
					</CardContent>
				</Card>

				<!-- Purge Settings Table -->
				<Card class="w-full space-y-6">
					<CardHeader class="pb-0">
						<CardTitle>General Data Purge Settings</CardTitle>
						<div class="flex items-center justify-between">
							<span class="rc-text-sm-muted mb-4">Configure retention periods and purge settings for different data types. The 'enabled' flag controls whether automatic purging is active for each table.</span>
							<ExternalLinkComponent :to="$rconfigDocsUrl + '/system-management/data-purge/'" text="Documentation" :active="true" class="mt-1 ml-8" />
						</div>

						<AlertTip v-if="showTip" title="Data Purge Tip" message="rConfig will automatically purge data older than the specified retention period, once a table is enabled. Every table is disabled until you turn it on. Finished job batches are also pruned after 48 hours by the scheduler, so the job_batches setting mainly clears older leftovers." small showClose @closed="closeTip()" />
					</CardHeader>
					<CardContent class="pt-0 space-y-6">
						<Loading v-if="loading" class=" " />
						<template v-else>
							<div v-if="error" class="text-destructive">{{ error }}</div>
							<div v-else class="space-y-6">
								<div class="flex flex-wrap items-center gap-3 justify-between">
									<div class="flex items-center gap-2 flex-wrap">
										<div class="flex items-center gap-2">
											<Input v-model="searchTerm" placeholder="Search by table name" class="w-72 max-w-sm" autocomplete="off" data-1p-ignore data-lpignore="true" />
											<ClearFilters v-if="searchTerm" @update:model-value="clearSearch" />
										</div>
										<Label class="flex items-center gap-2 text-sm">
											<input type="checkbox" v-model="filters.enabledOnly" @change="applyEnabledFilter" class="h-4 w-4" />
											Enabled only
										</Label>
									</div>
									<RcIcon name="refresh" class="w-4 h-4 mx-4 text-muted-foreground cursor-pointer hover:text-rcgray-200" @click="refreshSettings()" />
								</div>

								<div class="px-0 overflow-x-auto">
									<Table>
										<TableHeader>
											<TableRow>
												<TableHead class="w-[20%]">
													<Button class="flex justify-start w-full p-0" variant="ghost" @click="toggleSort('target')">
														<RcIcon name="sort" :sortParam="sortParam" field="target" />
														<span class="ml-2">Table Name</span>
													</Button>
												</TableHead>
												<TableHead class="w-[10%]">Records</TableHead>
												<TableHead class="w-[10%]">Data Size</TableHead>
												<TableHead class="w-[12%]">
													<Button class="flex justify-start w-full p-0" variant="ghost" @click="toggleSort('retention_days')">
														<RcIcon name="sort" :sortParam="sortParam" field="retention_days" />
														<span class="ml-2">Retention (Days)</span>
													</Button>
												</TableHead>
												<TableHead class="w-[12%]">Batch Size</TableHead>
												<TableHead class="w-[12%]">Frequency</TableHead>
												<TableHead class="w-[10%]">
													<Button class="flex justify-start w-full p-0" variant="ghost" @click="toggleSort('enabled')">
														<RcIcon name="sort" :sortParam="sortParam" field="enabled" />
														<span class="ml-2">Enabled</span>
													</Button>
												</TableHead>
												<TableHead class="w-[14%] text-right">Actions</TableHead>
											</TableRow>
										</TableHeader>
										<TableBody>
											<template v-if="loading">
												<TableLoading />
											</template>
											<template v-else-if="otherSettings.length === 0">
												<TableRow>
													<TableCell :colspan="8" class="text-center text-muted-foreground py-8">
														<NoResults message="No purge settings found" />
													</TableCell>
												</TableRow>
											</template>
											<template v-else>
												<template v-for="setting in otherSettings" :key="setting.id">
													<TableRow class="align-top text-sm">
														<TableCell>
															<template v-if="editingId === setting.id">
																<Input v-model="editForm.target" disabled class="bg-muted" />
															</template>
															<template v-else>
																<code class="text-xs break-all">{{ setting.target }}</code>
															</template>
														</TableCell>
														<TableCell>
															<RcBadge variant="outline" :interactive="false">
																{{ formatters.formatCompactNumber(setting.total_rows) }}
															</RcBadge>
														</TableCell>
														<TableCell>
															<RcBadge variant="outline" :interactive="false">{{ setting.total_bytes_hum }}</RcBadge>
														</TableCell>
														<TableCell>
															<template v-if="editingId === setting.id">
																<Input v-model.number="editForm.retention_days" type="number" min="1" />
															</template>
															<template v-else>
																{{ setting.retention_days }}
															</template>
														</TableCell>
														<TableCell>
															<template v-if="editingId === setting.id">
																<Input v-model.number="editForm.batch_size" type="number" min="1" />
															</template>
															<template v-else>
																{{ getOptionValue(setting, "batch_size", 1000) }}
															</template>
														</TableCell>
														<TableCell>
															<template v-if="editingId === setting.id">
																<Select v-model="editForm.cleanup_frequency">
																	<SelectTrigger>
																		<SelectValue placeholder="Select frequency" />
																	</SelectTrigger>
																	<SelectContent>
																		<SelectItem v-for="option in cleanupFrequencyOptions" :key="option.value" :value="option.value">
																			{{ option.label }}
																		</SelectItem>
																	</SelectContent>
																</Select>
															</template>
															<template v-else>
																<RcBadge variant="outline" :interactive="false">
																	{{ getOptionValue(setting, "cleanup_frequency", "daily") }}
																</RcBadge>
															</template>
														</TableCell>
														<TableCell>
															<template v-if="editingId === setting.id">
																<label class="flex items-center gap-2 text-sm">
																	<input type="checkbox" v-model="editForm.enabled" class="h-4 w-4" />
																	<span>{{ editForm.enabled ? "Enabled" : "Disabled" }}</span>
																</label>
															</template>
															<template v-else>
																<RcBadge v-if="getOptionValue(setting, 'enabled', false)" variant="success" :interactive="false">Enabled</RcBadge>
																<RcBadge v-else variant="secondary" :interactive="false">Disabled</RcBadge>
															</template>
														</TableCell>
														<TableCell class="text-right">
															<div class="flex justify-end gap-2">
																<template v-if="editingId === setting.id">
																	<Button variant="ghost" :disabled="savingEdit" @click="saveEdit" class="px-2 py-1 rc-btn-shadow" title="Save changes">
																		<Check :class="['w-4 h-4 mr-1 text-green-400', savingEdit ? 'animate-pulse' : '']" />
																	</Button>
																	<Button variant="ghost" @click="cancelEdit()" class="px-2 py-1 rc-btn-shadow" title="Cancel changes">
																		<CircleX class="w-4 h-4 mr-1" />
																	</Button>
																</template>
																<template v-else>
																	<ActionsMenu :rowData="setting" :showEditBtn="true" @onEdit="startEdit(setting)" :canDelete="false" :showEnableDisableBtn="true" @onEnableDisable="toggleEnableDisable(setting)" :enableStatus="getOptionValue(setting, 'enabled', false)" />
																</template>
															</div>
														</TableCell>
													</TableRow>
												</template>
											</template>
										</TableBody>
									</Table>
								</div>

								<Pagination :currentPage="filters.page" :lastPage="lastPage" :perPage="filters.perPage" :totalRecords="totalRecords" :isLoading="loading" @update:currentPage="handlePageChange" @update:perPage="handlePerPageChange" />
							</div>
						</template>
					</CardContent>
				</Card>
			</div>
		</div>
	</div>
</template>
