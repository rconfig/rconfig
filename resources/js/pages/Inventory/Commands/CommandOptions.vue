<script setup>
import * as monaco from "monaco-editor";
import GenericPopover from "@/pages/Shared/Popover/GeneralPopover.vue";
import RcConfirmAlertDialog from "@/pages/Shared/ConfirmAlertDialog/RcConfirmAlertDialog.vue";
import RcToolTip from "@/pages/Shared/Tooltips/RcToolTip.vue";
import useCommandOptions from "./useCommandOptions";
import useCodeEditor from "@/composables/codeEditorFunctions";
import Spinner from "@/pages/Shared/Icon/Spinner.vue";
import { Info, SunMoon, Hash, Map, Loader2, MessageCircleQuestion } from "lucide-vue-next";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Skeleton } from "@/components/ui/skeleton";
import { ToggleGroup, ToggleGroupItem } from "@/components/ui/toggle-group";
import { TooltipProvider } from "@/components/ui/tooltip";
import { Checkbox } from "@/components/ui/checkbox";
import { ref, onMounted, onUnmounted, computed, inject } from "vue";
import { useRouter } from "vue-router";

const props = defineProps({
	editId: {
		type: [Number, String],
		default: 0,
	},
});

let meditor = null;
const emit = defineEmits(["save", "close"]);
const rconfigDocsUrl = inject("rconfigDocsUrl");

const parsedEditId = ref(parseInt(props.editId) || 0);
const isNew = computed(() => parsedEditId.value === 0);
const router = useRouter();

const {
	// state
	activeCopyIcon,
	isDownloaded,

	// functions
	checkDarkModeIsSet,
	checkLineNumbersIsSet,
	checkMiniMapIsSet,
	copyContent,
	download,
	initEditor,
	toggleEditorDarkMode,
	toggleEditorLineNumbers,
	toggleEditorMinimap,
} = useCodeEditor(monaco);

const {
	// State
	configCompareSettings,
	isLoading,
	isLoadingEditor,
	model,
	isSubmitting,
	showResetConfirmDialog,

	// Methods
	close,
	getCompareOptionsFromSettings,
	saveCompareSettings,
	setDefaultOptions,
	updateCategoryOptions,
	selectCommandRecord,
	handleSave,
	handleConfirmReset,
} = useCommandOptions(props, emit, meditor);

// Editor state
const toggleStateMultiple = ref([]);
const editorMounted = ref(false);

// Lifecycle Hooks
onMounted(() => {
	initializeToggleStates();

	setTimeout(() => {
		initCodeEditor();
		editorMounted.value = true;
	}, 100);

	const handleCtrlS = (event) => {
		if (event.ctrlKey && event.key === "s") {
			event.preventDefault();
			handleSave(meditor);
		}
	};
	window.addEventListener("keydown", handleCtrlS);
	window._handleCtrlS = handleCtrlS;
});

onUnmounted(() => {
	if (meditor) {
		meditor.dispose();
	}
});

// Methods
function initializeToggleStates() {
	if (checkDarkModeIsSet()) toggleStateMultiple.value.push("dark");
	if (checkLineNumbersIsSet()) toggleStateMultiple.value.push("lineNumbers");
	if (checkMiniMapIsSet()) toggleStateMultiple.value.push("minimap");
}

function initCodeEditor() {
	try {
		meditor = initEditor("code-editor__code-pre", "policy");
		getCompareOptionsFromSettings(meditor);
		isLoadingEditor.value = false;
	} catch (err) {
		console.error("Error initializing editor:", err);
	}
}

const showDocsPage = () => {
	window.open(rconfigDocsUrl + "/devices/commands/#compare-options---configuring-diff-exclusions", "_blank");
};
</script>

<template>
	<div>
		<ResizablePanelGroup direction="horizontal">
			<ResizablePanel :default-size="25" :max-size="30" :min-size="10" collapsible :collapsed-size="0" ref="panelElement44" class="h-[90vh]">
				<div>
					<!-- Loading Skeleton -->
					<div v-if="isLoading" class="grid gap-4 px-4 py-4">
						<div class="grid items-center gap-4 hidden md:grid">
							<div class="col-span-1"></div>
							<div class="col-span-3">
								<div class="flex items-center mt-2">
									<Skeleton class="h-4 w-4 mr-2 rounded" />
									<Skeleton class="h-4 w-64" />
								</div>
							</div>
						</div>
						<Skeleton class="h-px w-full mb-4 mt-2" />
						<div class="space-y-4">
							<div class="grid items-center grid-cols-4 gap-2">
								<Skeleton class="h-4 w-16 justify-self-end" />
								<Skeleton class="h-10 col-span-3" />
							</div>
							<div class="grid items-center grid-cols-4 gap-4">
								<Skeleton class="h-4 w-20 justify-self-end" />
								<Skeleton class="h-10 col-span-3" />
							</div>
							<div class="grid items-center grid-cols-4 gap-4">
								<Skeleton class="h-4 w-24 justify-self-end" />
								<Skeleton class="h-10 col-span-3" />
							</div>
							<div class="grid items-center grid-cols-4 gap-4 mt-4">
								<Skeleton class="h-4 w-16 justify-self-end" />
								<Skeleton class="h-10 col-span-3" />
							</div>
						</div>
						<div class="flex justify-end pt-4 gap-2">
							<Skeleton class="h-8 w-16" />
							<Skeleton class="h-8 w-16" />
						</div>
					</div>

					<!-- Actual Form Content -->
					<ScrollArea class="h-full">
						<div class="pb-4 px-4" v-if="!isLoading">
							<!-- Help Link -->
							<div class="m-2">
								<div class="col-span-1"></div>
								<div class="col-span-3">
									<div class="flex items-center text-blue-500 mt-2">
										<Info class="mr-2" size="16" />
										<Button variant="link" class="px-0 py-0 text-sm font-normal" @click="showDocsPage">Compare Options Documentation</Button>
									</div>
								</div>
							</div>

							<Separator class="relative w-full h-px shrink-0 bg-border mb-4 mt-2" />

							<div class="space-y-6">
								<!-- Compare Settings Section -->
								<div class="space-y-4">
									<h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Compare Settings</h3>

									<div>
										<Label for="context" class="block mb-2">
											Context Lines
											<RcToolTip :delayDuration="100" :content="'Number of context lines to show around differences'" :side="'right'">
												<template #trigger>
													<Info class="inline ml-1 h-3 w-3 text-gray-400" />
												</template>
											</RcToolTip>
										</Label>
										<Input
											v-model.number="configCompareSettings.context"
											id="context"
											type="number"
											min="0"
											max="50"
											class="w-full bg-background border-input text-foreground placeholder:text-muted-foreground focus:border-ring focus:ring-ring focus:ring-2 focus:ring-offset-2 focus:ring-offset-background [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none [-moz-appearance:textfield]"
										/>
									</div>

									<div>
										<Label for="lengthLimit" class="block mb-2">
											Length Limit
											<RcToolTip :delayDuration="100" :content="'Maximum number of characters to compare'" :side="'right'">
												<template #trigger>
													<Info class="inline ml-1 h-3 w-3 text-gray-400" />
												</template>
											</RcToolTip>
										</Label>
										<Input
											v-model.number="configCompareSettings.lengthLimit"
											id="lengthLimit"
											type="number"
											min="1000"
											max="100000"
											step="1000"
											class="w-full bg-background border-input text-foreground placeholder:text-muted-foreground focus:border-ring focus:ring-ring focus:ring-2 focus:ring-offset-2 focus:ring-offset-background [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none [-moz-appearance:textfield]"
										/>
									</div>

									<!-- Compare Options Checkboxes -->
									<div class="space-y-3">
										<div class="flex items-center space-x-2">
											<Checkbox id="ignoreCase" v-model:checked="configCompareSettings.ignoreCase" />
											<Label for="ignoreCase" class="text-sm font-normal cursor-pointer">Ignore Case</Label>
											<RcToolTip :delayDuration="100" :content="'Ignore case differences when comparing'" :side="'right'">
												<template #trigger>
													<Info class="h-3 w-3 text-gray-400" />
												</template>
											</RcToolTip>
										</div>

										<div class="flex items-center space-x-2">
											<Checkbox id="ignoreLineEnding" v-model:checked="configCompareSettings.ignoreLineEnding" />
											<Label for="ignoreLineEnding" class="text-sm font-normal cursor-pointer">Ignore Line Endings</Label>
											<RcToolTip :delayDuration="100" :content="'Ignore differences in line ending characters (\\n vs \\r\\n)'" :side="'right'">
												<template #trigger>
													<Info class="h-3 w-3 text-gray-400" />
												</template>
											</RcToolTip>
										</div>

										<div class="flex items-center space-x-2">
											<Checkbox id="ignoreWhitespace" v-model:checked="configCompareSettings.ignoreWhitespace" />
											<Label for="ignoreWhitespace" class="text-sm font-normal cursor-pointer">Ignore Whitespace</Label>
											<RcToolTip :delayDuration="100" :content="'Ignore whitespace differences when comparing'" :side="'right'">
												<template #trigger>
													<Info class="h-3 w-3 text-gray-400" />
												</template>
											</RcToolTip>
										</div>
									</div>

									<!-- Reset to Defaults Button -->
									<div class="pt-2">
										<Button variant="outline" size="sm" @click="showResetConfirmDialog = true" class="text-xs">
											Reset to Defaults
										</Button>
									</div>
								</div>

								<!-- Action Buttons -->
								<div class="flex justify-end pt-6 space-x-2">
									<Button type="button" variant="outline" class="px-2 py-1 text-sm hover:bg-gray-700 hover:animate-pulse" @click="close()" size="sm">
										Close
										<div class="pl-2 ml-auto">
											<kbd class="rc-kdb-class">ESC</kbd>
										</div>
									</Button>

									<Button
										type="submit"
										class="px-2 py-1 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse flex items-center justify-between"
										size="sm"
										@click="saveCompareSettings(meditor)"
										variant="primary"
									>
										<div class="flex items-center">
											<span v-if="!isSubmitting">Update</span>
											<div v-else class="flex items-center">
												<Spinner class="w-4 h-4 mr-2" />
												Saving...
											</div>
										</div>

										<div class="pl-2 ml-auto">
											<kbd class="rc-kdb-class2">Ctrl&nbsp;+ S</kbd>
										</div>
									</Button>
								</div>
							</div>
						</div>
					</ScrollArea>
				</div>
			</ResizablePanel>

			<ResizableHandle with-handle />

			<ResizablePanel class="h-[90vh]">
				<ScrollArea class="border border-none rounded-md">
					<div class="flex flex-col">
						<div class="flex items-center p-2">
							<div class="flex items-center gap-2"></div>

							<!-- LEFT BUTTONS -->
							<div v-if="!isLoading" class="gap-1 flex items-center">
								<RcToolTip :delayDuration="100" :content="'Copy Content'" :side="'bottom'">
									<template #trigger>
										<Button variant="ghost" @click="copyContent(meditor.getValue())" class="px-2 py-1 rc-btn-shadow">
											<RcIcon name="copy-transition" :isActive="activeCopyIcon['getValue']" :size="16" />
										</Button>
									</template>
								</RcToolTip>

								<RcToolTip :delayDuration="100" :content="'Download Template'" :side="'bottom'">
									<template #trigger>
										<Button variant="ghost" @click="download(model?.filepath || 'policy-definition.txt')" class="px-2 py-1 rc-btn-shadow">
											<RcIcon name="copy-download-transition" :isActive="isDownloaded" :size="16" />
										</Button>
									</template>
								</RcToolTip>
							</div>

							<div v-else class="flex gap-1">
								<Skeleton class="h-8 w-8" />
								<Skeleton class="h-8 w-8" />
								<Skeleton class="h-8 w-8" />
								<Skeleton class="h-8 w-8" />
							</div>

							<Separator orientation="vertical" class="relative w-px h-6 mx-4 shrink-0 bg-border" />

							<GenericPopover
								:title="'Editor Tip'"
								:description="'You can auto-complete the policy definition by typing the first few characters of the policy method and pressing the Tab key or CTRL + Space . i.e. #[TAB]'"
								:hasLink="false"
								:align="'start'"
							>
								<template #trigger>
									<Button variant="link" size="sm" class="text-blue-400 hover:text-blue-300 p-0">
										<MessageCircleQuestion class="h-3.5 w-3.5" />
									</Button>
								</template>
							</GenericPopover>

							<Separator orientation="vertical" class="relative w-px h-6 mx-4 shrink-0 bg-border" />

							<!-- RIGHT BUTTONS -->
							<div class="flex items-center gap-2 ml-auto" v-if="!isLoading">
								<TooltipProvider>
									<ToggleGroup v-model="toggleStateMultiple" type="multiple">
										<ToggleGroupItem value="dark" @click="toggleEditorDarkMode()" class="px-2 py-1 rc-btn-shadow">
											<RcToolTip :delayDuration="100" :content="'Toggle Dark Mode'" :side="'bottom'">
												<template #trigger>
													<SunMoon size="16" class="focus:outline-none" />
												</template>
											</RcToolTip>
										</ToggleGroupItem>

										<ToggleGroupItem value="lineNumbers" @click="toggleEditorLineNumbers()" class="px-2 py-1 rc-btn-shadow">
											<RcToolTip :delayDuration="100" :content="'Toggle Line Numbers'" :side="'bottom'">
												<template #trigger>
													<Hash size="16" class="focus:outline-none" />
												</template>
											</RcToolTip>
										</ToggleGroupItem>

										<ToggleGroupItem value="minimap" @click="toggleEditorMinimap()" class="px-2 py-1 rc-btn-shadow">
											<RcToolTip :delayDuration="100" :content="'Toggle Map'" :side="'bottom'">
												<template #trigger>
													<Map size="16" class="focus:outline-none" />
												</template>
											</RcToolTip>
										</ToggleGroupItem>
									</ToggleGroup>
								</TooltipProvider>
							</div>

							<div v-else class="flex items-center gap-2 ml-auto">
								<div class="flex gap-1">
									<Skeleton class="h-8 w-8" />
									<Skeleton class="h-8 w-8" />
									<Skeleton class="h-8 w-8" />
								</div>
							</div>

							<Separator orientation="vertical" class="relative w-px h-6 mx-4 shrink-0 bg-border" />
							<span v-if="!isLoading">Policy</span>
							<Skeleton v-else class="h-4 w-12" />
						</div>
						<Separator class="relative w-full h-px shrink-0 bg-border"></Separator>
					</div>

					<!-- EDITOR -->
					<div v-if="!editorMounted || isLoading" class="flex items-center justify-center" style="height: calc(100vh - 190px);">
						<div v-if="isLoadingEditor" class="w-full h-full p-4">
							<Skeleton class="w-full h-full" />
						</div>
						<Loader2 v-else class="w-8 h-8 animate-spin" />
					</div>
					<div class="code-editor__code-pre" id="code-editor__code-pre" style="height: calc(100vh - 190px);"></div>
					<!-- EDITOR -->
				</ScrollArea>
			</ResizablePanel>
		</ResizablePanelGroup>

		<RcConfirmAlertDialog
			:showConfirmConfirmProceedAlertAlert="showResetConfirmDialog"
			title="Reset to Default Settings?"
			description="This will reset all compare settings to their default values. Any custom settings you've configured may be lost. This will only take effect when you click the update button, and the compare code in the editor will not change."
			@handleConfirmProceedAlert="handleConfirmReset"
			@close="showResetConfirmDialog = false"
		/>
	</div>
</template>
