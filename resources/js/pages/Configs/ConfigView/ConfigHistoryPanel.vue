<script setup>
import { inject, watch } from "vue";
import { useRoute } from "vue-router";
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Skeleton } from "@/components/ui/skeleton";
import { GitCommit, Code, GitBranch, Box, ReplaceAll } from "lucide-vue-next";
import Pagination from "@/pages/Shared/Table/Pagination.vue";
import { useConfigHistory } from "./useConfigHistory";
import { useI18n } from "vue-i18n";

const props = defineProps({
	isLoading: Boolean,
	configData: Object,
});

const emit = defineEmits(["viewConfigChanges", "viewConfig"]);

// Injections
const formatters = inject("formatters");
const { t } = useI18n();

// Initialize the composable with all consolidated logic
const {
	// Data
	configsData,
	isLoading: isHistoryLoading,
	isRefreshing,

	// UI State
	expandedConfigs,
	viewingConfigId,
	viewingConfigVersion,
	viewingChangesConfigId,
	changeViewOn,

	// Pagination
	currentPage,
	perPage,
	lastPage,
	totalItems,

	// Computed
	shouldShowLoading,
	shouldShowRefreshSkeleton,

	// Methods
	initializeConfigHistory,
	toggleExpand,
	viewConfigChanges,
	viewConfig,
	isConfigBeingViewed,
	isConfigShowingChanges,
	handlePageChange,
	handlePerPageChange,
	handleReload,
} = useConfigHistory();

// Watch for configData changes and fetch history when available
watch(
	() => props.configData,
	(newConfigData) => {
		if (newConfigData && newConfigData.device_id && newConfigData.command) {
			initializeConfigHistory(newConfigData.device_id, newConfigData.command);
		}
	},
	{ immediate: true }
);

// Wrapper functions to pass emit to composable
function onViewConfigChanges(configId) {
	viewConfigChanges(configId, emit);
}

function onViewConfig(configId, configVersion) {
	viewConfig(configId, configVersion, emit);
}

function onPageChange(page) {
	handlePageChange(page, props.configData);
}

function onPerPageChange(itemsPerPage) {
	handlePerPageChange(itemsPerPage, props.configData);
}
</script>

<template>
	<div>
		<Card class="lg:col-span-6 border-0 shadow-none rounded-2xl bg-transparent text-card-foreground">
			<CardHeader class="flex flex-row items-start p-4 bg-transparent">
				<div class="grid gap-0.5 w-full">
					<CardTitle class="gap-2 text-lg group">
						<div class="flex justify-between">
							<div>Config History</div>
							<div class="flex items-center gap-2">
								<RcIcon name="refresh" class="w-4 h-4 text-muted-foreground cursor-pointer hover:text-rcgray-200 transition-transform" :class="{ 'animate-spin': isRefreshing }" @click="handleReload()" />
							</div>
						</div>
					</CardTitle>
				</div>
			</CardHeader>

			<CardContent class="p-4 pt-0 text-sm">
				<!-- Loading/Refresh Skeleton -->
				<div class="space-y-4" v-if="shouldShowRefreshSkeleton">
					<div class="space-y-2" v-for="i in 3" :key="i">
						<Skeleton class="w-1/2 h-4" />
						<Skeleton class="w-full h-4" />
						<Skeleton class="w-3/4 h-4" />
						<Skeleton class="w-1/2 h-4" />
					</div>
				</div>

				<!-- Empty State -->
				<div v-else-if="!shouldShowRefreshSkeleton && (!configsData || !configsData.data || configsData.data.length === 0)" class="text-center py-8 text-muted-foreground">
					<Code class="w-8 h-8 mx-auto mb-2 opacity-50" />
					<p>No configuration history found</p>
				</div>

				<!-- Content with smooth transition -->
				<transition>
					<div v-if="!shouldShowRefreshSkeleton && configsData && configsData.data && configsData.data.length > 0" class="space-y-2" key="content">
						<div
							v-for="(config, index) in configsData.data"
							:key="`${config.id}-${isRefreshing ? 'refreshing' : 'normal'}`"
							class="border rounded-lg overflow-hidden transition-all duration-200 hover:shadow-md"
							:class="[config.latest_version === 1 ? 'border-green-500/50 bg-green-500/10 dark:border-green-500/40 dark:bg-green-500/10' : 'border-muted-foreground/20 bg-card dark:bg-[rgb(27,29,33)]', isConfigBeingViewed(config.id, config.config_version) ? 'ring-2 ring-blue-500/50 border-blue-500/70 shadow-blue-500/25' : '', isConfigShowingChanges(config.id) ? 'ring-2 ring-orange-500/50 border-orange-500/70 shadow-orange-500/25' : '']"
						>
							<!-- Collapsed Header -->
							<div class="flex items-center justify-between p-3 cursor-pointer hover:bg-muted/10 dark:hover:bg-muted/5 transition-colors duration-150" @click="toggleExpand(config.id)">
								<div class="flex items-center gap-3">
									<div class="w-2 h-2 rounded-full" :class="[config.latest_version === 1 ? 'bg-green-500' : 'bg-muted-foreground/40', isConfigBeingViewed(config.id, config.config_version) ? 'bg-blue-500' : '', isConfigShowingChanges(config.id) ? 'bg-orange-500' : '']"></div>
									<div>
										<div class="flex items-center gap-2">
											<span class="font-medium text-sm" :class="[isConfigBeingViewed(config.id, config.config_version) ? 'text-blue-600 dark:text-blue-400' : '', isConfigShowingChanges(config.id) ? 'text-orange-600 dark:text-orange-400' : '']">v{{ config.config_version }}</span>
											<span class="text-xs text-muted-foreground">ID: {{ config.id }}</span>
											<RcBadge v-if="config.latest_version === 1" variant="success" class="text-xs leading-none px-2 py-0.5 h-6 flex items-center">
												CURRENT
											</RcBadge>
											<RcBadge v-if="isConfigShowingChanges(config.id)" variant="default" class="text-xs leading-none px-2 py-0.5 h-6 flex items-center gap-1 bg-orange-500 text-white">
												<ReplaceAll class="w-3 h-3" />
												CHANGES
											</RcBadge>
										</div>
										<div class="text-xs text-muted-foreground">{{ formatters.formatTime(config.created_at) }} • {{ formatters.formatFileSize(config.config_filesize) }}</div>
									</div>
								</div>

								<div class="flex items-center">
									<GitCommit class="w-4 h-4 text-muted-foreground transform transition-transform" :class="expandedConfigs.includes(config.id) ? 'rotate-180' : ''" />
								</div>
							</div>

							<!-- Expanded Details -->
							<div v-if="expandedConfigs.includes(config.id)" class="px-3 pb-3">
								<div class="border rounded-lg p-3 bg-muted/50 dark:bg-[rgb(27,29,33)]">
									<div class="grid grid-cols-1 gap-4 text-sm mb-3">
										<div class="grid grid-cols-2 gap-4">
											<div><span class="font-medium">Device:</span> {{ config.device_name }}</div>
											<div><span class="font-medium">Command:</span> {{ config.command }}</div>
										</div>
										<div class="grid grid-cols-2 gap-4">
											<div class="min-w-0">
												<span class="font-medium">Filename:</span>
												<span class="block truncate text-xs mt-1" :title="config.config_filename">{{ config.config_filename }}</span>
											</div>
											<div>
												<span class="font-medium">Status: </span>
												<span
													:class="{
														'text-red-500': config.download_status === 0,
														'text-green-500': config.download_status === 1,
														'text-yellow-500': config.download_status === 2,
													}"
												>
													{{ config.download_status === 0 ? "Error" : config.download_status === 1 ? "Success" : "Unknown" }}
												</span>
											</div>
										</div>
										<div class="grid grid-cols-2 gap-4">
											<div v-if="config.duration"><span class="font-medium">Duration:</span> {{ config.duration }}s</div>
											<div v-if="config.connection_type"><span class="font-medium">Connection:</span> {{ config.connection_type }}</div>
										</div>
									</div>

									<div class="flex gap-2">
										<Button size="sm" variant="outline" @click="onViewConfig(config.id, config.config_version)" class="text-xs" :class="isConfigBeingViewed(config.id, config.config_version) && !isConfigShowingChanges(config.id) ? 'border-blue-500 text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/10' : ''" :disabled="isConfigBeingViewed(config.id, config.config_version) && !isConfigShowingChanges(config.id)">
											<Code class="w-3 h-3 mr-1" />
											View Config
										</Button>

										<Button size="sm" variant="outline" @click="onViewConfigChanges(config.id)" class="text-xs" :class="isConfigShowingChanges(config.id) ? 'border-orange-500 text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-500/10' : ''" :disabled="isConfigShowingChanges(config.id)">
											<GitBranch v-if="!isConfigShowingChanges(config.id)" class="w-3 h-3 mr-1" />
											<Box v-if="isConfigShowingChanges(config.id)" class="w-3 h-3 mr-1" />
											{{ isConfigShowingChanges(config.id) ? "Viewing Changes" : "View Changes" }}
										</Button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</transition>
			</CardContent>

			<CardFooter v-if="!shouldShowRefreshSkeleton && configsData && configsData.total > 0" class="px-2 py-2 border-t bg-muted/50">
				<div class="w-full">
					<Pagination :currentPage="currentPage" :lastPage="lastPage" :perPage="perPage" :total="totalItems" :from="configsData.from" :to="configsData.to" @update:currentPage="onPageChange" @update:perPage="onPerPageChange" class="scale-90 origin-center" />
				</div>
			</CardFooter>
		</Card>
	</div>
</template>

<style scoped>
/* Add subtle elevation instead of borders */
.shadow-md {
	box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.dark .shadow-md {
	box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.18);
}
/* Smooth spin animation for refresh icon */
@keyframes spin {
	from {
		transform: rotate(0deg);
	}
	to {
		transform: rotate(360deg);
	}
}

.animate-spin {
	animation: spin 1s linear infinite;
}
</style>
