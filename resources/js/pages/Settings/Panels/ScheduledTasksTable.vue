<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import cronstrue from "cronstrue";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import RcBadge from "@/pages/Shared/Badges/RcBadge.vue";
import { CalendarX } from "lucide-vue-next";

// State
const tasks = ref([]);
const isLoading = ref(false);
const error = ref(null);

// Fetch scheduled tasks
const fetchScheduledTasks = async () => {
	isLoading.value = true;
	error.value = null;

	try {
		const response = await axios.get("/api/settings/schedule/list");

		if (response.data && response.data.success && Array.isArray(response.data.scheduled_tasks)) {
			tasks.value = response.data.scheduled_tasks.map((task) => ({
				...task,
				humanReadableSchedule: parseCronExpression(task.expression),
				taskType: getTaskType(task.command),
			}));
			// console.log("Schedules fetched successfully:", response.data.scheduled_tasks);
		} else {
			tasks.value = []; // Ensure it's always an array
			error.value = response.data?.error || "Failed to fetch scheduled tasks";
		}
	} catch (err) {
		tasks.value = []; // Ensure it's always an array on error
		error.value = "Network error occurred";
		console.error("Error fetching schedules:", err);
	} finally {
		isLoading.value = false;
	}
};

// Parse cron expression to human readable
const parseCronExpression = (expression) => {
	try {
		let exp = cronstrue.toString(expression, {
			throwExceptionOnParseError: false,
			verbose: false,
			dayOfWeekStartIndexZero: false,
		});

		// if expression is 0 0 * * 0 then Every Sunday at midnight
		if (expression === "0 0 * * 0") {
			exp = "Every Sunday at midnight";
		}

		// if exp begins with An error occurred when return null
		if (exp.startsWith("An error occurred")) {
			console.log(exp.startsWith("An error occurred"));
			return null; // Fallback to original expression
		}
		return exp;
	} catch (error) {
		console.warn("Failed to parse cron expression:", expression, error);
		return expression; // Fallback to original expression
	}
};

// Get task type from command
const getTaskType = (command) => {
	if (command.includes("rconfig:download-task")) return "Download Task";
	if (command.includes("backup:run")) return "Backup";
	if (command.includes("health:")) return "Health Check";
	if (command.includes("queue:")) return "Queue Management";
	if (command.includes("model:prune")) return "Model Pruning";
	if (command.includes("horizon:")) return "Horizon";
	if (command.includes("vector:")) return "Vector";
	if (command.includes("chown")) return "System Command";
	return "Artisan Command";
};

// Get badge variant based on task type
const getBadgeVariant = (taskType) => {
	const variants = {
		"Download Task": "info",
		Backup: "success",
		"Health Check": "warning",
		"Queue Management": "secondary",
		"Model Pruning": "outline",
		Horizon: "primary",
		Vector: "info",
		"System Command": "danger",
		"Artisan Command": "default",
	};
	return variants[taskType] || "default";
};

// Truncate command for display
const truncateCommand = (command, maxLength = 60) => {
	return command.length > maxLength ? `${command.substring(0, maxLength)}...` : command;
};

// Format next run date
const formatNextRun = (dateString) => {
	try {
		return new Date(dateString).toLocaleString();
	} catch {
		return dateString;
	}
};

// Load data on component mount
onMounted(() => {
	fetchScheduledTasks();
});
</script>

<template>
	<Card class="w-full">
		<CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
			<CardTitle class="text-lg font-semibold"> Scheduled Tasks ({{ tasks.length }}) </CardTitle>
			<Button variant="outline" size="sm" @click="fetchScheduledTasks" class="gap-2" :disabled="isLoading">
				<RcIcon name="refresh" class="h-4 w-4" :class="{ 'animate-spin': isLoading }" />
				Refresh
			</Button>
		</CardHeader>

		<CardContent>
			<!-- Loading State -->
			<div v-if="isLoading" class="flex justify-center items-center py-8">
				<div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
				<span class="ml-2">Loading scheduled tasks...</span>
			</div>

			<!-- Error State -->
			<div v-else-if="error" class="bg-destructive/10 border border-destructive/20 rounded-md p-4">
				<div class="flex items-center">
					<RcIcon name="alert-circle" class="mr-2 text-destructive" />
					<span class="text-destructive font-medium">Error Loading Tasks</span>
				</div>
				<p class="text-sm text-muted-foreground mt-1">{{ error }}</p>
				<button @click="fetchScheduledTasks" class="mt-2 text-sm text-primary hover:underline">
					Try Again
				</button>
			</div>

			<template v-else>
				<!-- Tasks Table -->
				<div v-if="Array.isArray(tasks) && tasks.length > 0" class="rounded-md border">
					<Table>
						<TableHeader>
							<TableRow>
								<TableHead>Task Type</TableHead>
								<TableHead>Command</TableHead>
								<TableHead>Schedule</TableHead>
								<TableHead>Next Run</TableHead>
							</TableRow>
						</TableHeader>
						<TableBody>
							<template v-for="(task, index) in tasks" :key="index">
								<!-- Main Row -->
								<TableRow class="group">
									<TableCell>
										<RcBadge :variant="getBadgeVariant(task.taskType)" size="small">
											{{ task.taskType }}
										</RcBadge>
									</TableCell>
									<TableCell>
										<code class="text-xs bg-muted px-2 py-1 rounded">
											{{ truncateCommand(task.command) }}
										</code>
									</TableCell>
									<TableCell>
										<div class="space-y-1">
											<div class="text-sm">{{ task.humanReadableSchedule }}</div>
											<code class="text-xs text-muted-foreground">{{ task.expression }}</code>
										</div>
									</TableCell>
									<TableCell class="text-sm">
										{{ formatNextRun(task.next_run) }}
									</TableCell>
								</TableRow>
							</template>
						</TableBody>
					</Table>
				</div>

				<!-- Empty State -->
				<div v-if="!tasks || !Array.isArray(tasks) || tasks.length === 0" class="text-center py-12">
					<CalendarX class="mx-auto h-12 w-12 text-muted-foreground mb-4" />
					<h3 class="text-lg font-semibold">No Scheduled Tasks</h3>
					<p class="text-muted-foreground">There are no scheduled tasks configured at this time.</p>
				</div>
			</template>
		</CardContent>
	</Card>
</template>