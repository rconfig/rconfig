<script setup>
import { computed, reactive } from "vue";
import CommandMultiSelect from "@/pages/Shared/FormFields/CommandMultiSelect.vue";
import DeviceMultiSelect from "@/pages/Shared/FormFields/DeviceMultiSelect.vue";
import CategoryMultiSelect from "@/pages/Shared/FormFields/CategoryMultiSelect.vue";
import TagMultiSelect from "@/pages/Shared/FormFields/TagMultiSelect.vue";
import ConfigSearchFilterCardDateRangePicker from "@/pages/Configs/ConfigSearch/ConfigSearchFilterCardDateRangePicker.vue";
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from "@/components/ui/card";
import { Checkbox } from "@/components/ui/checkbox";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { Skeleton } from "@/components/ui/skeleton";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { ChevronDown, ChevronUp, Minus, Plus, RotateCcw } from "lucide-vue-next";
import { useFilterCard } from "./useFilterCard";
import RcBadge from "@/pages/Shared/Badges/RcBadge.vue";

const props = defineProps({
	isLoading: Boolean,
	initialModel: {
		type: Object,
		default: null,
	},
	fullHeight: {
		type: Boolean,
		default: false,
	},
	searchButtonLabel: {
		type: String,
		default: "Search",
	},
});
const emit = defineEmits(["searchCompleted"]);

const { addCriterion, canSearch, clearAll, clearAllTerms, datePickerKey, model, performSearch, removeCriterion, setDates } = useFilterCard(emit, props.initialModel);
const wrapperClass = computed(() => (props.fullHeight ? "h-full" : "w-full"));
const cardClass = computed(() => (props.fullHeight ? "h-[calc(100%-1rem)]" : ""));
const showSearchTermsHint = computed(() => !model.value.criteria.some((criterion) => String(criterion?.term ?? "").trim().length > 0));
const collapsedSections = reactive({
	scopeFilters: false,
	searchOptions: false,
	dateRange: false,
});

function toggleSection(sectionKey) {
	collapsedSections[sectionKey] = !collapsedSections[sectionKey];
}

function updateSearchLimit(amount) {
	const currentLimit = Number(model.value.limit ?? 50);
	model.value.limit = Math.max(10, currentLimit + amount);
}
</script>

<template>
	<div :class="wrapperClass">
		<Card class="flex flex-col overflow-hidden rounded-[26px] border border-border/70 bg-card shadow-sm shadow-black/5 backdrop-blur-xs dark:bg-card" :class="cardClass">
			<CardHeader class="border-b border-border/60 bg-muted/20 px-5 py-4">
				<div class="space-y-1.5">
					<CardTitle class="text-base font-semibold tracking-tight">Configuration Search</CardTitle>
					<CardDescription>
						Build one or more config text criteria, then narrow by devices, command groups, commands, and tags.
					</CardDescription>
				</div>
			</CardHeader>
			<CardContent class="flex-1 overflow-y-auto px-5 py-5 text-sm">
				<div class="space-y-2" v-if="isLoading">
					<Skeleton class="w-1/2 h-4" />
					<Skeleton class="w-full h-4" />
					<Skeleton class="w-full h-4" />
					<Skeleton class="w-full h-4" />
					<Skeleton class="w-full h-4" />
					<Skeleton class="w-full h-4" />
					<Skeleton class="w-full h-4" />
					<Skeleton class="w-full h-4" />
					<Skeleton class="w-full h-4" />
				</div>
				<transition name="fade">
					<div class="grid gap-4 xl:grid-cols-[minmax(320px,1fr)_minmax(0,2fr)] xl:items-start" v-if="!isLoading">
						<div class="space-y-4">
							<section class="space-y-3 rounded-2xl border border-border/60 bg-background/55 p-4 shadow-xs backdrop-blur-xs dark:bg-rcgray-900/35">
								<div class="flex items-start justify-between gap-3">
									<div>
										<h3 class="text-sm font-semibold text-foreground">Scope Filters</h3>
										<p class="text-xs text-muted-foreground">Optional filters that narrow which configs are searched before term matching is applied.</p>
									</div>
									<Button variant="ghost" size="icon" class="h-8 w-8 rounded-full" @click="toggleSection('scopeFilters')">
										<ChevronUp v-if="!collapsedSections.scopeFilters" class="h-4 w-4" />
										<ChevronDown v-else class="h-4 w-4" />
									</Button>
								</div>

								<div v-if="!collapsedSections.scopeFilters" class="grid gap-4">
									<div class="space-y-2">
										<TagMultiSelect v-model="model.tags" />
										<p class="text-xs text-muted-foreground">Filter the searched configs by device tags.</p>
									</div>

									<div class="space-y-2">
										<DeviceMultiSelect v-model="model.devices" />
										<p class="text-xs text-muted-foreground">Limit the search to specific devices.</p>
									</div>

									<div class="space-y-2">
										<CategoryMultiSelect v-model="model.categories" />
										<p class="text-xs text-muted-foreground">Limit the search to specific command groups.</p>
									</div>

									<div class="space-y-2">
										<CommandMultiSelect v-model="model.commands" />
										<p class="text-xs text-muted-foreground">You are advised to narrow the search scope by selecting specific commands to search against.</p>
									</div>
								</div>
							</section>

							<section class="space-y-3 rounded-2xl border border-border/60 bg-background/55 p-4 shadow-xs backdrop-blur-xs dark:bg-rcgray-900/35">
								<div class="flex items-start justify-between gap-3">
									<div>
										<h3 class="text-sm font-semibold text-foreground">Search Options</h3>
										<p class="text-xs text-muted-foreground">Control how results are grouped and how much context is included with each preview.</p>
									</div>
									<Button variant="ghost" size="icon" class="h-8 w-8 rounded-full" @click="toggleSection('searchOptions')">
										<ChevronUp v-if="!collapsedSections.searchOptions" class="h-4 w-4" />
										<ChevronDown v-else class="h-4 w-4" />
									</Button>
								</div>

								<div v-if="!collapsedSections.searchOptions" class="space-y-4">
									<div class="space-y-2">
										<Label for="results_per_config" class="text-xs font-medium uppercase tracking-wide text-muted-foreground">Results Per Config</Label>
										<Select v-model="model.results_per_config">
											<SelectTrigger id="results_per_config" class="w-full">
												<SelectValue placeholder="Select how previews should be shown" />
											</SelectTrigger>
											<SelectContent>
												<SelectGroup>
													<SelectItem value="first_match">First Match</SelectItem>
													<SelectItem value="all_matches">All Matches</SelectItem>
												</SelectGroup>
											</SelectContent>
										</Select>
										<p class="text-xs text-muted-foreground">Rows stay grouped by config. The preview can stay simple while the detail dialog still shows every matched excerpt.</p>
									</div>

									<div class="flex items-center space-x-4 rounded-xl border border-border/60 bg-muted/15 px-3 py-2">
										<Checkbox id="ignore_case" v-model:checked="model.ignore_case" />
										<label for="ignore_case" class="text-sm font-medium leading-none cursor-pointer peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
											Ignore Case
										</label>
									</div>

									<div class="flex items-center space-x-4 rounded-xl border border-border/60 bg-muted/15 px-3 py-2">
										<Checkbox id="latest_version_only" v-model:checked="model.latest_version_only" />
										<label for="latest_version_only" class="text-sm font-medium leading-none cursor-pointer peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
											{{ model.latest_version_only ? "Latest versions only" : "All versions included" }}
										</label>
									</div>

									<div class="space-y-3 rounded-xl border border-border/60 bg-muted/10 p-3">
										<h4 class="text-sm font-semibold text-foreground">Context</h4>
										<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
											<div class="space-y-2">
												<Label for="lines_before" class="text-xs font-medium uppercase tracking-wide text-muted-foreground">Lines Before</Label>
												<Input type="number" id="lines_before" v-model="model.lines_before" min="0" />
											</div>
											<div class="space-y-2">
												<Label for="lines_after" class="text-xs font-medium uppercase tracking-wide text-muted-foreground">Lines After</Label>
												<Input type="number" id="lines_after" v-model="model.lines_after" min="0" />
											</div>
										</div>
										<p class="text-xs text-muted-foreground">Context lines shape the excerpt preview and the match details dialog.</p>
									</div>
								</div>
							</section>

							<section class="space-y-3 rounded-2xl border border-border/60 bg-background/55 p-4 shadow-xs backdrop-blur-xs dark:bg-rcgray-900/35">
								<div class="flex items-start justify-between gap-3">
									<div>
										<h3 class="text-sm font-semibold text-foreground">Date Range</h3>
										<p class="text-xs text-muted-foreground">Last seven days is the default starting window. Clear it to search across all config dates.</p>
									</div>
									<Button variant="ghost" size="icon" class="h-8 w-8 rounded-full" @click="toggleSection('dateRange')">
										<ChevronUp v-if="!collapsedSections.dateRange" class="h-4 w-4" />
										<ChevronDown v-else class="h-4 w-4" />
									</Button>
								</div>

								<div v-if="!collapsedSections.dateRange" class="flex flex-col items-start gap-2">
									<ConfigSearchFilterCardDateRangePicker :key="datePickerKey" class="w-full" @date-change="setDates($event)" />
								</div>
							</section>
						</div>

						<section class="relative space-y-3 overflow-hidden rounded-2xl border border-border/60 bg-background/55 p-4 shadow-xs backdrop-blur-xs dark:bg-rcgray-900/35 xl:min-h-full">
							<div class="flex items-start justify-between gap-3">
								<div>
									<h3 class="text-sm font-semibold text-foreground">Search Terms</h3>
									<p class="text-xs text-muted-foreground">Add one or more config-text terms, then choose whether all terms or any term should match.</p>
								</div>
							</div>

							<div
								class="pointer-events-none absolute inset-x-8 top-1/2 z-0 hidden -translate-y-1/2 justify-center transition-opacity duration-300 md:flex"
								:class="showSearchTermsHint ? 'opacity-100' : 'opacity-0'">
								<p class="max-w-md text-center text-sm leading-6 text-muted-foreground/55">
									Start with tags, then narrow devices, then command groups, and finally commands. Each are optional. Command selection is recommended for more precise results.
								</p>
							</div>

							<div class="relative z-10 rounded-xl border border-border/70 bg-background/75 p-3 shadow-xs">
								<div class="flex flex-col gap-3 md:flex-row md:items-center">
									<div class="space-y-1 md:w-2/3">
										<Label for="criteria_mode" class="text-xs font-medium uppercase tracking-wide text-muted-foreground">Match Mode</Label>
										<p class="text-xs leading-5 text-muted-foreground">
											{{ model.criteria_mode === "all" ? "Every term must be present somewhere in a matching config." : "A config is returned when at least one term matches." }}
										</p>
									</div>
									<div class="md:w-1/3">
										<Select v-model="model.criteria_mode">
											<SelectTrigger id="criteria_mode" class="w-full">
												<SelectValue placeholder="Select term matching mode" />
											</SelectTrigger>
											<SelectContent>
												<SelectGroup>
													<SelectItem value="all">All Terms (AND)</SelectItem>
													<SelectItem value="any">Any Term (OR)</SelectItem>
												</SelectGroup>
											</SelectContent>
										</Select>
									</div>
								</div>
							</div>

							<div class="relative z-10 rounded-xl border border-border/70 bg-background/75 p-3 shadow-xs">
								<div class="flex flex-col gap-3 md:flex-row md:items-center">
									<div class="md:w-2/3">
										<Label for="search_limit" class="text-xs font-medium uppercase tracking-wide text-muted-foreground">Search Limit</Label>
										<p class="text-xs leading-5 text-muted-foreground">Default is 50 matching configs. Increase it for broader searches, or refine filters to keep results focused.</p>
									</div>
									<div class="md:w-1/3">
										<div class="flex items-center gap-2">
											<Button
												variant="outline"
												size="icon"
												class="h-9 w-9 shrink-0 rounded-lg border-border/70 bg-background/80 hover:bg-muted/20"
												@click="updateSearchLimit(-10)"
											>
												<Minus class="h-3.5 w-3.5" />
											</Button>
											<Input id="search_limit" type="number" v-model="model.limit" min="10" step="10" class="text-center" />
											<Button
												variant="outline"
												size="icon"
												class="h-9 w-9 shrink-0 rounded-lg border-border/70 bg-background/80 hover:bg-muted/20"
												@click="updateSearchLimit(10)"
											>
												<Plus class="h-3.5 w-3.5" />
											</Button>
										</div>
									</div>
								</div>
							</div>

							<div class="relative z-10 space-y-3">
								<div v-for="(criterion, index) in model.criteria" :key="criterion.id" class="space-y-2">
									<div class="rounded-xl border border-border/70 bg-background/75 p-3 shadow-xs">
										<div class="mb-2 flex items-center justify-between gap-2 text-xs text-muted-foreground">
											<RcBadge v-if="index === 0" variant="info" class="rounded-full border border-border/70 bg-muted/30 px-2.5 py-1 font-medium">Config text contains</RcBadge>
											<RcBadge v-else :variant="model.criteria_mode === 'all' ? 'primary' : 'secondary'" class="rounded-full border border-border/70 bg-muted/30 px-2.5 py-1 font-medium"><span>{{ model.criteria_mode === 'all' ? 'AND' : 'OR' }}&nbsp;</span> Config text contains</RcBadge>
											<Button variant="ghost" size="icon" class="h-7 w-7 rounded-full hover:bg-muted/30" :disabled="model.criteria.length === 1" @click="removeCriterion(criterion.id)">
												<Minus class="h-3.5 w-3.5" />
											</Button>
										</div>
										<Input
											v-model="criterion.term"
											autocomplete="off"
											placeholder="Enter a term, IP, hostname, ACL fragment, or command text"
											class="w-full focus:outline-hidden focus-visible:ring-0"
										/>
									</div>
								</div>
							</div>
							<div class="relative z-10 flex items-center gap-2">
								<Button variant="outline" size="sm" class="h-8 rounded-full border-border/70 bg-background/70 px-3 hover:bg-muted/20" @click="addCriterion()">
									<Plus class="mr-2 h-3.5 w-3.5" />
									Add Term
								</Button>
								<Button
									v-if="model.criteria.length > 1 || !showSearchTermsHint"
									variant="outline"
									size="sm"
									class="h-8 rounded-full border-border/70 bg-background/70 px-3 text-xs text-muted-foreground hover:bg-muted/20"
									@click="clearAllTerms()"
								>
									<RotateCcw class="mr-2 h-3.5 w-3.5" />
									Clear Terms
								</Button>
							</div>
						</section>
					</div>
				</transition>
			</CardContent>
			<CardFooter class="sticky bottom-0 flex flex-row items-center justify-between border-t border-border/60 bg-background/80 px-5 py-4 shadow-xs backdrop-blur-xs">
				<p class="text-xs text-muted-foreground">
					{{ canSearch ? "Ready to search" : "Add at least one term to run the search" }}
				</p>
				<div class="flex items-center gap-2">
					<Button variant="outline" @click="clearAll()" class="flex items-center rounded-full border-border/70 bg-background/70 text-xs text-muted-foreground hover:bg-muted/20" v-if="!isLoading">
						Clear
					</Button>
					<Button variant="default" @click="performSearch()" :disabled="!canSearch" class="rounded-full px-4 text-xs btn-primary-action" v-if="!isLoading">
						{{ props.searchButtonLabel }}
					</Button>
				</div>
			</CardFooter>
		</Card>
	</div>
</template>
