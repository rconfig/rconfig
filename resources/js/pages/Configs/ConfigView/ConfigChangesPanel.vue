<script setup>
import RcToolTip from "@/pages/Shared/Tooltips/RcToolTip.vue";
import axios from "axios";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { GitCompare, ChevronDown, HelpCircle, Cog } from "lucide-vue-next";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Separator } from "@/components/ui/separator";
import { ref, onMounted, onUnmounted } from "vue";
import { useCopy } from "@/composables/useCopy";
import { useToaster } from "@/composables/useToaster";
import ConfigCompareResults from "@/pages/Configs/ConfigCompare/ConfigCompareResults.vue";

const props = defineProps({
	configId: {
		type: [String, Number],
		required: true,
	},
});

const emit = defineEmits(["backToConfig"]);
const { copyItem, activeCopyIcon } = useCopy();
const { toastError } = useToaster();

// State
const isLoading = ref(true);
const viewToggle = ref("diff");
const showCodeBlock = ref(true);
const showSideBySide = ref(false);
const viewExclusionPolicyCode = ref(false);
const showComparisonSettings = ref(false);
const showExclusionRules = ref(false);
const isComponentMounted = ref(false);

const config_change_record = ref({});
const config_diff = ref("");
const left_device = ref({
	config: {},
	configOutput: { content: "" },
});
const right_device = ref({
	config: {},
	configOutput: { content: "" },
});

// Lifecycle
onMounted(() => {
	isComponentMounted.value = true;
	getConfigChanges();
});

onUnmounted(() => {
	isComponentMounted.value = false;
});

// Methods
function getConfigChanges() {
	if (!isComponentMounted.value) return;

	isLoading.value = true;
	axios
		.get(`/api/config-changes/current-config/${props.configId}`)
		.then((response) => {
			if (!isComponentMounted.value) return;

			config_change_record.value = response.data;
			config_diff.value = response.data.config_diff;

			// Set up device configs for comparison
			left_device.value.config.id = config_change_record.value.previous_config_id;
			right_device.value.config.id = config_change_record.value.current_config_id;

			// Load config files for side-by-side comparison
			getLeftConfigFile();
			getRightConfigFile();

			isLoading.value = false;
		})
		.catch((error) => {
			if (!isComponentMounted.value) return;
			console.error(error);
			toastError("Error", "Could not retrieve config changes!");
			isLoading.value = false;
		});
}

// Helper function to extract filename from path
function extractFilenameFromPath(path) {
	if (!path) return null;
	const pathParts = path.split("/");
	return pathParts[pathParts.length - 1];
}

function getLeftConfigFile() {
	if (!isComponentMounted.value || !left_device.value.config.id) return;

	axios
		.get(`/api/configs/view-config/${left_device.value.config.id}`)
		.then((response) => {
			if (!isComponentMounted.value) return;
			left_device.value.configOutput = response.data.data;

			// Extract filename from config_location path
			left_device.value.config.config_filename = extractFilenameFromPath(response.data.data.config_location) || `Previous Config ${left_device.value.config.id}`;
		})
		.catch((error) => {
			if (!isComponentMounted.value) return;
			console.error("Error loading left config:", error);
			toastError("Error", "Could not load left config file!");
		});
}

function getRightConfigFile() {
	if (!isComponentMounted.value || !right_device.value.config.id) return;

	axios
		.get(`/api/configs/view-config/${right_device.value.config.id}`)
		.then((response) => {
			if (!isComponentMounted.value) return;
			right_device.value.configOutput = response.data.data;

			// Extract filename from config_location path
			right_device.value.config.config_filename = extractFilenameFromPath(response.data.data.config_location) || `Current Config ${right_device.value.config.id}`;
		})
		.catch((error) => {
			if (!isComponentMounted.value) return;
			console.error("Error loading right config:", error);
			toastError("Error", "Could not load right config file!");
		});
}

function showDiffView() {
	if (!isComponentMounted.value) return;

	viewToggle.value = "diff";
	showCodeBlock.value = true;
	showSideBySide.value = false;
}

function showSideBySideView() {
	if (!isComponentMounted.value) return;

	viewToggle.value = "sidebyside";
	showCodeBlock.value = false;
	showSideBySide.value = true;
}
</script>

<template>
	<div class=" ">
		<Card class="lg:col-span-6 border-0 shadow-sm rounded-2xl bg-card text-card-foreground dark:bg-[rgb(27,29,33)]">
			<CardHeader class="flex flex-row justify-between items-center py-1 px-4">
				<div class="flex items-center">
					<CardTitle class="text-lg font-semibold">Config Changes</CardTitle>
				</div>
				<div class="flex items-center gap-2">
					<Button class="px-2 py-1 text-sm hover:animate-pulse flex items-center" :class="{ 'bg-blue-600 hover:bg-blue-700 text-white': viewToggle === 'diff' }" size="sm" @click="showDiffView" variant="outline">
						<RcIcon name="diff" class="h-4 w-4 mr-1" />
						Diff View
					</Button>

					<Button class="px-2 py-1 text-sm hover:animate-pulse flex items-center" :class="{ 'bg-blue-600 hover:bg-blue-700 text-white': viewToggle === 'sidebyside' }" size="sm" @click="showSideBySideView" :variant="viewToggle === 'sidebyside' ? 'primary' : 'outline'">
						<GitCompare class="h-4 w-4 mr-1" />
						Side by Side
					</Button>
				</div>
			</CardHeader>

			<Separator class="mx-4" />

			<ScrollArea class="max-h-[83vh] w-full rounded-md border-none smooth-scroll overflow-y-auto">
				<CardContent class="p-4 space-y-4">
					<div v-if="isLoading" class="flex items-center justify-center py-12">
						<div class="flex flex-col items-center space-y-3">
							<div class="w-8 h-8 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
							<p class="rc-text-sm-muted">Loading configuration changes...</p>
						</div>
					</div>

					<template v-else>
						<div v-if="showCodeBlock" class="space-y-4">
							<div class="flex justify-between items-center">
								<div class="flex items-center justify-between bg-muted/40 rounded-lg">
									<h3 class="text-lg font-semibold text-card-foreground">
										Configuration Diff
									</h3>
									<div class="flex items-center gap-2 ml-4">
										<span class="px-2 py-1 text-xs rounded bg-blue-500/10 text-blue-400 border border-blue-500/30"> ID: {{ left_device.config.id }} </span>
										<span class="text-muted-foreground">→</span>
										<span class="px-2 py-1 text-xs rounded bg-green-500/10 text-green-400 border border-green-500/30"> ID: {{ right_device.config.id }} </span>
									</div>
								</div>

								<RcToolTip :delayDuration="100" :content="'Copy configuration diff'" :side="'bottom'">
									<template #trigger>
										<Button class="text-muted-foreground hover:text-foreground px-2 py-1 rc-btn-shadow" variant="ghost" size="sm" @click="copyItem('configDiff', config_diff)">
											<RcIcon name="copy-transition" :isActive="activeCopyIcon['configDiff']" :size="16" />
										</Button>
									</template>
								</RcToolTip>
							</div>

							<div class="bg-muted/30 border rounded-2xl p-4 overflow-auto max-h-[40vh]">
								<div class="text-sm font-mono whitespace-pre-wrap">
									<span v-if="config_diff === ''" class="text-muted-foreground italic">No changes detected</span>
									<span class="rc-text-sm-muted" v-html="config_diff" v-if="config_diff !== ''" />
								</div>
							</div>

							<!-- Comparison Settings Section -->
							<div v-if="config_change_record.compare_exclusion_settings" class="mt-6 space-y-4">
								<!-- Comparison Settings Section -->
								<div class="bg-muted/20 rounded-2xl border border-muted-foreground/20 overflow-hidden">
									<div :class="{ 'border-b border-muted-foreground/20': showComparisonSettings }" class="hover:bg-rcgray-800 bg-muted/30 px-4 py-3 cursor-pointer hover:bg-muted/40 transition-colors" @click="showComparisonSettings = !showComparisonSettings">
										<div class="flex items-center justify-between">
											<div class="flex items-start gap-3">
												<div class="flex-shrink-0 mt-0.5">
													<RcIcon name="config-compare" class="w-4 h-4" />
												</div>
												<div>
													<h4 class="font-semibold text-foreground text-sm">Comparison Settings for this Config</h4>
													<p class="text-xs text-muted-foreground mt-1">
														These comparison policy settings were applied specifically to this configuration comparison.
													</p>
												</div>
											</div>
											<div class="flex-shrink-0 ml-3">
												<ChevronDown class="w-5 h-5 text-muted-foreground transition-transform duration-200" :class="{ 'rotate-180': showComparisonSettings }" />
											</div>
										</div>
									</div>

									<transition name="expand">
										<div v-if="showComparisonSettings" class="p-4">
											<ScrollArea class="max-h-[40vh] w-full rounded-md">
												<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
													<!-- Comparison Settings Card -->
													<div class="bg-card rounded-xl p-4 border border-muted-foreground/20">
														<h5 class="font-medium text-foreground mb-3 flex items-center gap-2">
															Comparison Rules
														</h5>
														<div class="space-y-3">
															<div class="flex items-center justify-between py-2 border-b border-muted-foreground/10 last:border-b-0">
																<div class="flex items-center gap-2">
																	<span class="text-sm text-muted-foreground">Context Lines</span>
																	<div class="group relative">
																		<HelpCircle class="w-3 h-3 text-muted-foreground/60 cursor-help" />
																		<div class="invisible group-hover:visible absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-rcgray-900 rc-btn-shadow rounded whitespace-nowrap z-10">
																			Lines shown before/after changes
																		</div>
																	</div>
																</div>
																<span class="font-mono text-sm bg-muted px-2 py-1 rounded">
																	{{ config_change_record.compare_exclusion_settings.config_compare_settings.context }}
																</span>
															</div>

															<div class="flex items-center justify-between py-2 border-b border-muted-foreground/10 last:border-b-0">
																<span class="text-sm text-muted-foreground">Ignore Case</span>
																<div class="flex items-center gap-1">
																	<RcIcon :name="config_change_record.compare_exclusion_settings.config_compare_settings.ignoreCase ? 'status-green' : 'status-red'" class="w-4 h-4" />
																	<span class="text-sm font-medium">
																		{{ config_change_record.compare_exclusion_settings.config_compare_settings.ignoreCase ? "Yes" : "No" }}
																	</span>
																</div>
															</div>

															<div class="flex items-center justify-between py-2 border-b border-muted-foreground/10 last:border-b-0">
																<span class="text-sm text-muted-foreground">Ignore Line Endings</span>
																<div class="flex items-center gap-1">
																	<RcIcon :name="config_change_record.compare_exclusion_settings.config_compare_settings.ignoreLineEnding ? 'status-green' : 'status-red'" class="w-4 h-4" />
																	<span class="text-sm font-medium">
																		{{ config_change_record.compare_exclusion_settings.config_compare_settings.ignoreLineEnding ? "Yes" : "No" }}
																	</span>
																</div>
															</div>

															<div class="flex items-center justify-between py-2">
																<span class="text-sm text-muted-foreground">Ignore Whitespace</span>
																<div class="flex items-center gap-1">
																	<RcIcon :name="config_change_record.compare_exclusion_settings.config_compare_settings.ignoreWhitespace ? 'status-green' : 'status-red'" class="w-4 h-4" />
																	<span class="text-sm font-medium">
																		{{ config_change_record.compare_exclusion_settings.config_compare_settings.ignoreWhitespace ? "Yes" : "No" }}
																	</span>
																</div>
															</div>
														</div>
													</div>

													<!-- Technical Settings Card -->
													<div class="bg-card rounded-xl p-4 border border-muted-foreground/20">
														<h5 class="font-medium text-foreground mb-3 flex items-center gap-2">
															<Cog class="w-4 h-4" />
															Technical Limits
														</h5>
														<div class="space-y-3">
															<div class="flex items-center justify-between py-2">
																<div class="flex items-center gap-2">
																	<span class="text-sm text-muted-foreground">File Size Limit</span>
																	<div class="group relative">
																		<HelpCircle class="w-3 h-3 text-muted-foreground/60 cursor-help" />
																		<div class="invisible group-hover:visible absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-rcgray-900 rc-btn-shadow rounded whitespace-nowrap z-10">
																			Max config file size for comparison
																		</div>
																	</div>
																</div>
																<span class="font-mono text-sm bg-muted px-2 py-1 rounded">
																	{{ config_change_record.compare_exclusion_settings.config_compare_settings.lengthLimit }}
																</span>
															</div>
														</div>
													</div>
												</div>
											</ScrollArea>
										</div>
									</transition>
								</div>

								<!-- Exclusion Rules Section -->
								<div class="bg-muted/20 rounded-2xl border border-muted-foreground/20 overflow-hidden">
									<div :class="{ 'border-b border-muted-foreground/20': showExclusionRules }" class="hover:bg-rcgray-800 bg-muted/30 px-4 py-3 cursor-pointer hover:bg-muted/40 transition-colors" @click="showExclusionRules = !showExclusionRules">
										<div class="flex items-center justify-between">
											<div class="flex items-start gap-3">
												<div class="flex-shrink-0 mt-0.5">
													<RcIcon name="exclusion-rules" class="w-4 h-4" />
												</div>
												<div>
													<h4 class="font-semibold text-foreground text-sm">Exclusion Rules File</h4>
													<p class="text-xs text-muted-foreground mt-1">
														<span v-if="config_change_record.compare_exclusion_settings?.config_compare_exclusion_file">
															Exclusion rules that were applied during this configuration comparison.
														</span>
														<span v-else class="italic">
															No exclusion rules were applied to this comparison.
														</span>
													</p>
												</div>
											</div>
											<div class="flex-shrink-0 ml-3">
												<ChevronDown class="w-5 h-5 text-muted-foreground transition-transform duration-200" :class="{ 'rotate-180': showExclusionRules }" />
											</div>
										</div>
									</div>

									<transition name="expand">
										<div v-if="showExclusionRules" class="p-4">
											<div v-if="config_change_record.compare_exclusion_settings?.config_compare_exclusion_file">
												<div class="bg-muted/50 border rounded-xl p-4">
													<ScrollArea class="max-h-[30vh] w-full rounded-md">
														<div class="flex items-center justify-between mb-2">
															<span class="text-xs text-muted-foreground font-medium">Exclusion Rules Content</span>
															<Button variant="ghost" size="sm" class="h-6 w-6 p-0" @click="copyItem('exclusionFile', config_change_record.compare_exclusion_settings.config_compare_exclusion_file)">
																<RcIcon name="copy-transition" :isActive="activeCopyIcon['exclusionFile']" class="w-3 h-3" />
															</Button>
														</div>
														<pre class="text-xs font-mono text-foreground whitespace-pre-wrap">{{ config_change_record.compare_exclusion_settings.config_compare_exclusion_file }}</pre>
													</ScrollArea>
												</div>
											</div>
											<div v-else class="text-center py-8">
												<RcIcon name="file-x" class="w-12 h-12 text-muted-foreground/50 mx-auto mb-3" />
												<p class="text-sm text-muted-foreground">No exclusion rules file was applied</p>
												<p class="text-xs text-muted-foreground/70 mt-1">
													The comparison was performed without any exclusion rules.
												</p>
											</div>
										</div>
									</transition>
								</div>
							</div>
						</div>

						<div v-if="showSideBySide" class="rounded-2xl overflow-hidden bg-muted/10">
							<ConfigCompareResults :leftSelectedId="left_device.config.id" :rightSelectedId="right_device.config.id" />
						</div>
					</template>
				</CardContent>
			</ScrollArea>
		</Card>
	</div>
</template>

<style scoped>
/* Transition for exclusion file toggle */
.slide-fade-enter-active {
	transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
	transition: all 0.2s ease-in;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
	transform: translateY(-10px);
	opacity: 0;
}

/* Transition for expand/collapse section */
.expand-enter-active {
	transition: all 0.3s ease-out;
	max-height: 1000px;
}

.expand-leave-active {
	transition: all 0.3s ease-in;
	max-height: 1000px;
}

.expand-enter-from,
.expand-leave-to {
	max-height: 0;
	opacity: 0;
}
</style>
