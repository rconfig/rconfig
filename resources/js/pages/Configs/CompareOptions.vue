<script setup>
import * as monaco from "monaco-editor/esm/vs/editor/editor.api";
import RcConfirmAlertDialog from "@/pages/Shared/ConfirmAlertDialog/RcConfirmAlertDialog.vue";
import RcToolTip from "@/pages/Shared/Tooltips/RcToolTip.vue";
import useCompareOptions from "./useCompareOptions";
import useCodeEditor from "@/composables/codeEditorFunctions";
import Spinner from "@/pages/Shared/Icon/Spinner.vue";
import { Info, SunMoon, Hash, Map, Loader2 } from "lucide-vue-next";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Skeleton } from "@/components/ui/skeleton";
import { ToggleGroup, ToggleGroupItem } from "@/components/ui/toggle-group";
import { TooltipProvider } from "@/components/ui/tooltip";
import { Checkbox } from "@/components/ui/checkbox";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { ref, onMounted, onUnmounted, inject } from "vue";

let meditor = null;
const rconfigDocsUrl = inject("rconfigDocsUrl");

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
	isSubmitting,
	showResetConfirmDialog,

	// Methods
	close,
	getCompareOptionsFromSettings,
	saveCompareSettings,
	handleConfirmReset,
} = useCompareOptions();

const toggleStateMultiple = ref([]);
const editorMounted = ref(false);

onMounted(() => {
	initializeToggleStates();

	setTimeout(() => {
		initCodeEditor();
		editorMounted.value = true;
	}, 100);

	const handleCtrlS = (event) => {
		if (event.ctrlKey && event.key === "s") {
			event.preventDefault();
			saveCompareSettings(meditor);
		}
	};
	window.addEventListener("keydown", handleCtrlS);
	window._handleCtrlS = handleCtrlS;
});

onUnmounted(() => {
	if (window._handleCtrlS) {
		window.removeEventListener("keydown", window._handleCtrlS);
		delete window._handleCtrlS;
	}
	if (meditor) {
		meditor.dispose();
	}
});

function initializeToggleStates() {
	if (checkDarkModeIsSet()) toggleStateMultiple.value.push("dark");
	if (checkLineNumbersIsSet()) toggleStateMultiple.value.push("lineNumbers");
	if (checkMiniMapIsSet()) toggleStateMultiple.value.push("minimap");
}

function initCodeEditor() {
	try {
		meditor = initEditor("code-editor__code-pre", "rconfig");
		getCompareOptionsFromSettings(meditor);
		isLoadingEditor.value = false;
	} catch (err) {
		console.error("Error initializing editor:", err);
	}
}

const showDocsPage = () => {
	window.open(rconfigDocsUrl + "/configtools/compare/", "_blank");
};
</script>

<template>
	<div class="w-screen h-[calc(100vh-72px)] border" style="display: flex; flex-direction: column; border-radius: 16px; margin: 4px 8px 8px; max-width: calc(100% - 16px); overflow: hidden;">
		<div class="flex justify-between w-full p-2 border-b">
			<h2 class="items-center content-center text-muted-foreground">Config Compare Options</h2>
		</div>

		<ResizablePanelGroup direction="horizontal" class="">
			<ResizablePanel :default-size="25" :max-size="35" :min-size="10" collapsible :collapsed-size="0" class="h-[90vh]">
				<ScrollArea class="h-full">
					<div class="pb-4 px-4">
						<!-- Help Link -->
						<div class="m-2">
							<div class="flex items-center text-blue-500 mt-2">
								<Info class="mr-2" size="16" />
								<Button variant="link" class="px-0 py-0 text-sm font-normal" @click="showDocsPage()">Compare Options Documentation</Button>
							</div>
						</div>

						<Separator class="relative w-full h-px shrink-0 bg-border mb-4 mt-2" />

						<div class="space-y-6">
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
									<Input v-model.number="configCompareSettings.context" id="context" type="number" min="0" max="50" class="w-full" />
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
									<Input v-model.number="configCompareSettings.lengthLimit" id="lengthLimit" type="number" min="1000" max="100000" step="1000" class="w-full" />
								</div>

								<div class="space-y-3">
									<div class="flex items-center space-x-2">
										<Checkbox id="ignoreCase" v-model:checked="configCompareSettings.ignoreCase" />
										<Label for="ignoreCase" class="text-sm font-normal cursor-pointer">Ignore Case</Label>
									</div>

									<div class="flex items-center space-x-2">
										<Checkbox id="ignoreLineEnding" v-model:checked="configCompareSettings.ignoreLineEnding" />
										<Label for="ignoreLineEnding" class="text-sm font-normal cursor-pointer">Ignore Line Endings</Label>
									</div>

									<div class="flex items-center space-x-2">
										<Checkbox id="ignoreWhitespace" v-model:checked="configCompareSettings.ignoreWhitespace" />
										<Label for="ignoreWhitespace" class="text-sm font-normal cursor-pointer">Ignore Whitespace</Label>
									</div>
								</div>

								<div class="pt-2">
									<Button variant="outline" size="sm" @click="showResetConfirmDialog = true" class="text-xs">
										Reset to Defaults
									</Button>
								</div>
							</div>

							<div class="flex justify-end pt-6 space-x-2">
								<Button type="button" variant="outline" class="px-2 py-1 text-sm hover:animate-pulse" @click="close()" size="sm">
									Close
								</Button>

								<Button type="submit" class="px-2 py-1 text-sm hover:animate-pulse btn-primary-action flex items-center justify-between" size="sm" @click="saveCompareSettings(meditor)" variant="default">
									<span v-if="!isSubmitting">Update</span>
									<div v-else class="flex items-center">
										<Spinner class="w-4 h-4 mr-2" />
										Saving...
									</div>
								</Button>
							</div>
						</div>
					</div>
				</ScrollArea>
			</ResizablePanel>
			<ResizableHandle with-handle />
			<ResizablePanel class="h-[90vh]">
				<ScrollArea class="border border-none rounded-md">
					<div class="flex flex-col">
						<div class="flex items-center p-2">
							<!-- LEFT BUTTONS -->
							<div class="gap-1 flex items-center">
								<RcToolTip :delayDuration="100" :content="'Copy Content'" :side="'bottom'">
									<template #trigger>
										<Button variant="ghost" @click="copyContent(meditor.getValue())" class="px-2 py-1 rc-btn-shadow">
											<RcIcon name="copy-transition" :isActive="activeCopyIcon['getValue']" :size="16" />
										</Button>
									</template>
								</RcToolTip>

								<RcToolTip :delayDuration="100" :content="'Download Exclusion File'" :side="'bottom'">
									<template #trigger>
										<Button variant="ghost" @click="download('config-compare-exclusions.txt')" class="px-2 py-1 rc-btn-shadow">
											<RcIcon name="copy-download-transition" :isActive="isDownloaded" :size="16" />
										</Button>
									</template>
								</RcToolTip>
							</div>

							<Separator orientation="vertical" class="relative w-px h-6 mx-4 shrink-0 bg-border" />

							<!-- RIGHT BUTTONS -->
							<div class="flex items-center gap-2 ml-auto">
								<TooltipProvider>
									<ToggleGroup v-model="toggleStateMultiple" type="multiple">
										<ToggleGroupItem value="dark" @click="toggleEditorDarkMode()" class="px-2 py-1 rc-btn-shadow">
											<RcToolTip :delayDuration="100" :content="'Toggle Dark Mode'" :side="'bottom'">
												<template #trigger>
													<SunMoon size="16" class="focus:outline-hidden" />
												</template>
											</RcToolTip>
										</ToggleGroupItem>

										<ToggleGroupItem value="lineNumbers" @click="toggleEditorLineNumbers()" class="px-2 py-1 rc-btn-shadow">
											<RcToolTip :delayDuration="100" :content="'Toggle Line Numbers'" :side="'bottom'">
												<template #trigger>
													<Hash size="16" class="focus:outline-hidden" />
												</template>
											</RcToolTip>
										</ToggleGroupItem>

										<ToggleGroupItem value="minimap" @click="toggleEditorMinimap()" class="px-2 py-1 rc-btn-shadow">
											<RcToolTip :delayDuration="100" :content="'Toggle Map'" :side="'bottom'">
												<template #trigger>
													<Map size="16" class="focus:outline-hidden" />
												</template>
											</RcToolTip>
										</ToggleGroupItem>
									</ToggleGroup>
								</TooltipProvider>
							</div>

							<Separator orientation="vertical" class="relative w-px h-6 mx-4 shrink-0 bg-border" />
							<span>Exclusion Rules</span>
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

		<RcConfirmAlertDialog :showConfirmConfirmProceedAlertAlert="showResetConfirmDialog" title="Reset to Default Settings?" description="This will reset all compare settings to their default values. This only takes effect when you click Update, and the exclusion code in the editor will not change." @handleConfirmProceedAlert="handleConfirmReset" @close="showResetConfirmDialog = false" />
	</div>
</template>
