<script setup>
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Button } from "@/components/ui/button";
import Loading from "@/pages/Shared/Table/Loading.vue";
import NoResults from "@/pages/Shared/Table/NoResults.vue";
import Pagination from "@/pages/Shared/Table/Pagination.vue";
import { useReload } from "@/composables/tables/useReload";
import { Play, CheckCircle, XCircle, Clock } from "lucide-vue-next";
import { ref, onMounted, watch, inject, onUnmounted } from "vue";
import { useDialogStore } from "@/stores/dialogActions";
import { useToaster } from "@/composables/useToaster";
import axios from "axios";

const dialogStore = useDialogStore();
const { isDialogOpen, closeDialog } = dialogStore;
const { toastError } = useToaster();
const formatters = inject("formatters");
const { reload } = useReload(fetchTaskHistory);

const props = defineProps({
	taskId: {
		type: Number,
		default: 0,
	},
});

// State
const taskHistory = ref([]);
const isLoading = ref(false);
const currentPage = ref(1);
const lastPage = ref(1);
const perPage = ref(10);
const total = ref(0);

// Fetch task history
async function fetchTaskHistory() {
	if (!props.taskId) return;

	isLoading.value = true;
	try {
		const response = await axios.get(`/api/tasks/monitored/${props.taskId}`, {
			params: {
				page: currentPage.value,
				per_page: perPage.value,
			},
		});

		taskHistory.value = response.data.data || [];
		lastPage.value = response.data.last_page || 1;
		total.value = response.data.total || 0;
	} catch (error) {
		console.error("Error fetching task history:", error);
		toastError("Error", "Failed to fetch task history.");
	} finally {
		isLoading.value = false;
	}
}

// Get status info with updated badge variants for RcBadge
function getStatusInfo(meta) {
	const metaLower = meta.toLowerCase();

	if (metaLower.includes("started")) {
		return {
			icon: Play,
			color: "text-blue-500",
			variant: "primary",
		};
	} else if (metaLower.includes("finished")) {
		return {
			icon: CheckCircle,
			color: "text-green-500",
			variant: "success",
		};
	} else if (metaLower.includes("failed") || metaLower.includes("error")) {
		return {
			icon: XCircle,
			color: "text-red-500",
			variant: "danger",
		};
	} else {
		return {
			icon: Clock,
			color: "text-gray-500",
			variant: "default",
		};
	}
}

// Handle keyboard shortcuts
function handleKeyDown(event) {
	if (event.key === "Escape") {
		closeDialog("DialogViewHistory");
	}
}

// Watchers
watch(currentPage, () => {
	fetchTaskHistory();
});

watch(
	() => props.taskId,
	(newVal) => {
		if (newVal) {
			fetchTaskHistory();
		}
	}
);

// Lifecycle
onMounted(() => {
	window.addEventListener("keydown", handleKeyDown);
});

onUnmounted(() => {
	window.removeEventListener("keydown", handleKeyDown);
});
</script>

<template>
	<Dialog :open="isDialogOpen('DialogViewHistory')">
		<DialogTrigger as-child>
			<!-- Dialog trigger handled externally -->
		</DialogTrigger>
		<DialogContent class="max-w-[95vw] md:max-w-7xl gap-0 p-0 m-2 md:m-4 bg-rcgray-900 h-[90vh] md:h-auto" @escapeKeyDown="closeDialog('DialogViewHistory')" @closeClicked="closeDialog('DialogViewHistory')">
			<DialogHeader class="rc-dialog-header">
				<DialogTitle class="text-sm text-rcgray-200">
					<div class="flex items-center">
						<RcIcon name="history" />
						<span class="ml-2">Task History - ID: {{ taskId }}</span>
					</div>
				</DialogTitle>
				<p class="text-xs text-rcgray-400 mt-1">
					View the execution history for this scheduled task
				</p>
			</DialogHeader>

			<!-- Match the exact structure of the main table -->
			<div class="flex flex-col h-[70vh] gap-1 text-center">
				<!-- Header bar matching main table style -->
				<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-2 md:p-4 gap-2 sm:gap-0">
					<div class="flex items-center justify-center sm:justify-start ml-0 sm:ml-2">
						<div class="text-sm text-rcgray-300">
							<span class="font-medium">Total Runs:</span>
							<span class="ml-1 font-mono">{{ total }}</span>
						</div>
					</div>
					<div class="flex items-center justify-center sm:justify-end">
						<RcIcon name="refresh" class="w-4 h-4 mx-2 md:mx-4 text-muted-foreground cursor-pointer hover:text-rcgray-200" @click="reload()" />
					</div>
				</div>

				<!-- Table container with exact same padding as main table -->
				<div class="px-2 md:px-6 overflow-auto">
					<!-- Empty state when no task ID -->
					<div v-if="!taskId" class="flex flex-col items-center justify-center p-4 md:p-8">
						<div class="w-12 h-12 md:w-16 md:h-16 mb-4 rounded-full bg-muted/50 flex items-center justify-center">
							<RcIcon name="history" class="w-6 h-6 md:w-8 md:h-8 text-muted-foreground" />
						</div>
						<h3 class="text-base md:text-lg font-semibold mb-2">No Task Selected</h3>
						<p class="text-muted-foreground text-sm text-center max-w-sm">
							Select a task from the list to view its execution history
						</p>
					</div>

					<!-- Table content -->
					<div v-else>
						<Table>
							<TableHeader>
								<TableRow>
									<TableHead class="w-[8%] min-w-[60px] text-xs md:text-sm">Task ID</TableHead>
									<TableHead class="w-[8%] min-w-[80px] text-xs md:text-sm">Monitored Task ID</TableHead>
									<TableHead class="w-[12%] min-w-[60px] text-xs md:text-sm">Type</TableHead>
									<TableHead class="w-[15%] min-w-[100px] text-xs md:text-sm">Status</TableHead>
									<TableHead class="w-[15%] min-w-[100px] text-xs md:text-sm">Timestamp</TableHead>
								</TableRow>
							</TableHeader>
							<TableBody>
								<!-- Loading state -->
								<template v-if="isLoading">
									<Loading />
								</template>

								<!-- Data rows -->
								<template v-else-if="taskHistory.length > 0">
									<TableRow v-for="item in taskHistory" :key="item.id">
										<TableCell class="text-start font-mono text-xs md:text-sm">
											{{ item.task_id }}
										</TableCell>
										<TableCell class="text-start font-mono text-xs md:text-sm">
											{{ item.monitored_scheduled_task_id }}
										</TableCell>
										<TableCell class="text-start">
											<!-- Type badge -->
											<RcBadge variant="outline">
												{{ item.type }}
											</RcBadge>
										</TableCell>
										<TableCell class="text-start">
											<div class="flex items-center space-x-1 md:space-x-2">
												<component :is="getStatusInfo(item.meta).icon" :class="getStatusInfo(item.meta).color" class="w-3 h-3 md:w-4 md:h-4" />
												<!-- Status badge with dynamic variant -->
												<RcBadge :variant="getStatusInfo(item.meta).variant">
													{{ item.meta }}
												</RcBadge>
											</div>
										</TableCell>
										<TableCell class="text-start text-xs md:text-sm">
											{{ formatters.formatTime(item.created_at) }}
										</TableCell>
									</TableRow>
								</template>

								<!-- No results -->
								<template v-else>
									<NoResults />
								</template>
							</TableBody>
						</Table>

						<Pagination :currentPage="currentPage" :lastPage="lastPage" :perPage="perPage" @update:currentPage="currentPage = $event" @update:perPage="perPage = $event" :totalRecords="total" :isLoading="isLoading" />
					</div>
				</div>
			</div>

			<!-- Footer -->
			<div class="flex items-center justify-end p-2 md:p-4 border-t border-rcgray-700">
				<Button variant="outline" size="sm" @click="closeDialog('DialogViewHistory')" class="px-3 py-1 md:px-4 text-sm">
					Close
					<div class="pl-2 ml-auto hidden sm:block">
						<kbd class="rc-kdb-class">ESC</kbd>
					</div>
				</Button>
			</div>
		</DialogContent>
	</Dialog>
</template>