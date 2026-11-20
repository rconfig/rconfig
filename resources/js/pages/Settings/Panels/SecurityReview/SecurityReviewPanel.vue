<script setup>
import { computed, onMounted } from "vue";
import { ExternalLink, Play, RefreshCw, SearchX, ShieldCheck } from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Switch } from "@/components/ui/switch";
import Loading from "@/pages/Shared/Loaders/Loading.vue";
import { useSecurityReview } from "./useSecurityReview";
import { NEEDS_ACTION, relativeAge, runLabel, toggleStatus } from "./securityReview";
import SecurityPostureStrip from "./SecurityPostureStrip.vue";
import SecurityFindingRow from "./SecurityFindingRow.vue";
import SecurityAboutCard from "./SecurityAboutCard.vue";

/**
 * Settings > Security Review: replaces the old Settings > Security page.
 */
const review = useSecurityReview();
const { isLoading, loadError, data, report, counts, filtered, groupOptions, filters, task, taskError, isRunning, notifySaving, notifyError } = review;

const lastRun = computed(() => data.value?.run ?? null);
const advisoriesUrl = computed(() => data.value?.advisories_url ?? "https://www.rconfig.com/security-advisories");
const buttonLabel = computed(() => runLabel(isRunning.value, task.value?.status));
const showsNeedsAction = computed(() => filters.statuses.join() === NEEDS_ACTION.join() && filters.groups.length === 0);

onMounted(() => {
	review.load();
});

function selectStatus(status) {
	filters.statuses = toggleStatus(filters.statuses, status);
}

function showAll() {
	filters.statuses = [];
	filters.groups = [];
}

function setGroup(event) {
	filters.groups = event.target.value ? [event.target.value] : [];
}

function setStatus(event) {
	filters.statuses = event.target.value === "needs" ? [...NEEDS_ACTION] : event.target.value ? [event.target.value] : [];
}

const statusSelectValue = computed(() => {
	if (filters.statuses.length === 0) return "";
	if (filters.statuses.join() === NEEDS_ACTION.join()) return "needs";
	return filters.statuses[0];
});
</script>

<template>
	<div
		class="flex justify-center w-full"
		data-testid="security-review-panel"
	>
		<div class="flex flex-col w-full gap-6">
			<div class="flex flex-wrap items-start justify-between gap-3">
				<div>
					<h3 class="rc-panel-heading">
						Security Review
					</h3>
					<p class="rc-panel-subheading">
						Checks this rConfig server's security settings and shows how to fix what fails.
						<span
							v-if="lastRun"
							class="block text-xs text-muted-foreground mt-1"
							data-testid="security-last-checked"
						>Last checked {{ relativeAge(lastRun.ran_at) }} ({{ lastRun.trigger === "schedule" ? "daily check" : lastRun.user || lastRun.trigger }}) · daily check at {{ data?.schedule?.time }}</span>
					</p>
				</div>
				<Button
					size="sm"
					:disabled="isRunning"
					data-testid="run-checks"
					@click="review.runChecks()"
				>
					<RefreshCw
						v-if="isRunning"
						class="h-4 w-4 mr-1 animate-spin"
					/>
					<Play
						v-else
						class="h-4 w-4 mr-1"
					/>
					{{ buttonLabel }}
				</Button>
			</div>

			<p
				v-if="taskError && report"
				class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800 dark:border-red-800 dark:bg-red-950/40 dark:text-red-200"
				role="alert"
				data-testid="run-error"
			>
				{{ taskError }}
			</p>

			<div
				v-if="isLoading"
				class="flex justify-center py-16"
			>
				<Loading />
			</div>

			<p
				v-else-if="loadError"
				class="text-center py-16 text-red-600 dark:text-red-400"
				data-testid="security-load-error"
			>
				{{ loadError }}
			</p>

			<template v-else>
				<Card
					v-if="!report"
					data-testid="security-empty"
				>
					<CardContent class="flex flex-col items-center gap-3 py-12 text-center">
						<ShieldCheck class="h-10 w-10 text-muted-foreground" />
						<p class="font-medium">
							No security check has run yet
						</p>
						<p class="text-sm text-muted-foreground max-w-md">
							Run the checks now, or wait for the daily run at {{ data?.schedule?.time }}. They take under a minute and change nothing.
						</p>
						<Button
							:disabled="isRunning"
							data-testid="run-checks-empty"
							@click="review.runChecks()"
						>
							<RefreshCw
								v-if="isRunning"
								class="h-4 w-4 mr-1 animate-spin"
							/>
							<Play
								v-else
								class="h-4 w-4 mr-1"
							/>
							{{ buttonLabel }}
						</Button>
						<p
							v-if="taskError"
							class="text-sm text-red-600 dark:text-red-400"
							role="alert"
							data-testid="run-error-empty"
						>
							{{ taskError }}
						</p>
					</CardContent>
				</Card>

				<template v-else>
					<div class="flex flex-col gap-2">
						<SecurityPostureStrip
							:counts="counts"
							:active="filters.statuses"
							@select="selectStatus"
						/>
						<a
							:href="advisoriesUrl"
							target="_blank"
							rel="noopener noreferrer"
							class="inline-flex items-center gap-1 self-start text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
							data-testid="security-advisories-link"
						>
							See all rConfig security advisories <ExternalLink class="h-3 w-3" />
						</a>
					</div>

					<Card>
						<div class="flex flex-wrap items-center gap-2 border-b border-border p-3">
							<select
								class="h-8 rounded-md border border-input bg-background px-2 text-xs"
								aria-label="Result"
								data-testid="security-status-filter"
								:value="statusSelectValue"
								@change="setStatus"
							>
								<option value="needs">
									Needs attention
								</option>
								<option value="">
									Any result
								</option>
								<option value="fail">
									Failed
								</option>
								<option value="warn">
									Warning
								</option>
								<option value="pass">
									Passed
								</option>
								<option value="skip">
									Skipped
								</option>
							</select>
							<select
								class="h-8 rounded-md border border-input bg-background px-2 text-xs"
								aria-label="Group"
								data-testid="security-group-filter"
								:value="filters.groups[0] ?? ''"
								@change="setGroup"
							>
								<option value="">
									All groups
								</option>
								<option
									v-for="group in groupOptions"
									:key="group.value"
									:value="group.value"
								>
									{{ group.label }}
								</option>
							</select>
							<span class="ml-auto flex items-center gap-2 text-xs">
								<button
									type="button"
									class="text-blue-600 dark:text-blue-400 hover:underline"
									data-testid="show-all-checks"
									@click="showAll"
								>Show all {{ report.checks.length }} checks</button>
								<button
									v-if="!showsNeedsAction"
									type="button"
									class="text-muted-foreground hover:underline"
									@click="review.resetFilters()"
								>Reset filters</button>
							</span>
						</div>

						<div
							v-if="filtered.length === 0"
							class="flex flex-col items-center gap-2 py-10 text-sm text-muted-foreground"
							data-testid="security-no-matches"
						>
							<SearchX class="h-6 w-6" />
							<p>{{ showsNeedsAction ? "Nothing needs attention. Every check passed or was skipped." : "No check matches these filters." }}</p>
						</div>

						<SecurityFindingRow
							v-for="row in filtered"
							:key="row.id"
							:row="row"
						/>
					</Card>
				</template>

				<Card>
					<CardContent class="flex items-start justify-between gap-4 pt-6">
						<div>
							<label
								for="security-notify"
								class="text-sm font-medium"
							>Email me when a check starts failing</label>
							<p class="text-xs text-muted-foreground mt-1">
								One email when a check starts failing and one when it passes again, sent to users who get notifications. Warnings never email.
							</p>
							<p
								v-if="notifyError"
								class="text-xs text-red-600 dark:text-red-400 mt-1"
								role="alert"
							>
								{{ notifyError }}
							</p>
						</div>
						<Switch
							id="security-notify"
							:checked="Boolean(data?.notify)"
							:disabled="notifySaving"
							data-testid="security-notify-toggle"
							@update:checked="review.setNotify($event)"
						/>
					</CardContent>
				</Card>

				<SecurityAboutCard />
			</template>
		</div>
	</div>
</template>
