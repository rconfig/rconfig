<script setup>
import "monaco-editor/esm/vs/language/json/monaco.contribution";
import * as monaco from "monaco-editor/esm/vs/editor/editor.api";
import RcToolTip from "@/pages/Shared/Tooltips/RcToolTip.vue";
import useCodeEditor from "@/composables/codeEditorFunctions";
import useConfigViewMainPanel from "@/pages/Configs/ConfigView/useConfigViewMainPanel";
import { SunMoon, Hash, Map, RotateCcwSquare } from "lucide-vue-next";
import { ToggleGroup, ToggleGroupItem } from "@/components/ui/toggle-group";
import { TooltipProvider } from "@/components/ui/tooltip";
import { ref, onMounted, onUnmounted } from "vue";
import { useDialogStore } from "@/stores/dialogActions";
import { useSheetStore } from "@/stores/sheetActions";

const props = defineProps({
	configId: Number,
	selectedConfigVersion: Number,
	deviceId: Number,
	deviceName: String,
});

let meditor = null;

const dialogStore = useDialogStore();
const { openDialog, isDialogOpen } = dialogStore;

const sheetStore = useSheetStore();
const { openSheet, isSheetOpen } = sheetStore;

const {
	// state
	activeCopyIcon,
	isDownloaded,

	// functions
	checkDarkModeIsSet,
	checkLineNumbersIsSet,
	checkMiniMapIsSet,
	checkStickyScrollIsSet,
	copyContent,
	copyPath,
	download,
	initEditor,
	toggleEditorDarkMode,
	toggleEditorLineNumbers,
	toggleEditorMinimap,
} = useCodeEditor(monaco);

const {
	// State
	config_location,

	// Functions
	getDefaultEditorCode,
	showConfiguration,
} = useConfigViewMainPanel(props);

const isLoading = ref(false);
const toggleStateMultiple = ref([]); //'dark', 'lineNumbers', 'minimap', 'stickyscroll'

// Lifecycle Hooks
onMounted(() => {
	initializeToggleStates();
	initCodeEditor();
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
	if (checkStickyScrollIsSet()) toggleStateMultiple.value.push("stickyscroll");
}

function initCodeEditor() {
	meditor = initEditor("code-editor__code-pre", "rconfig");
	props.configId === 0 ? getDefaultEditorCode(meditor) : showConfiguration(props.configId, meditor);
}

</script>

<template>
	<div>
		<div class="p-0 overflow-none">
			<div class="mt-4 space-y-2" v-if="isLoading">
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

			<div class="space-y-4" v-if="!isLoading">
				<div class="p-0">
					<div class="flex flex-col">
						<div class="flex items-center px-2 mb-1">
							<div class="flex items-center gap-2"></div>

							<!-- LEFT BUTTONS -->
							<div class="flex items-center gap-1">
								<RcToolTip :delayDuration="100" :content="'Copy Config Path'" :side="'bottom'">
									<template #trigger>
										<Button variant="ghost" @click="copyPath(config_location)" class="px-2 py-1 rc-btn-shadow">
											<RcIcon name="copy-file-transition" :isActive="activeCopyIcon['getPath']" :size="16" />
										</Button>
									</template>
								</RcToolTip>

								<RcToolTip :delayDuration="100" :content="'Copy Config'" :side="'bottom'">
									<template #trigger>
										<Button variant="ghost" @click="copyContent(meditor.getValue())" class="px-2 py-1 rc-btn-shadow">
											<RcIcon name="copy-transition" :isActive="activeCopyIcon['getValue']" :size="16" />
										</Button>
									</template>
								</RcToolTip>

								<RcToolTip :delayDuration="100" :content="'Download Configs'" :side="'bottom'">
									<template #trigger>
										<Button variant="ghost" @click="download(config_location)" class="px-2 py-1 rc-btn-shadow">
											<RcIcon name="copy-download-transition" :isActive="isDownloaded" :size="16" />
										</Button>
									</template>
								</RcToolTip>
							</div>
							<!-- LEFT BUTTONS -->

							<Separator orientation="vertical" class="relative w-px h-6 mx-4 shrink-0 bg-border" />

							<div class="flex items-center">
								<span class="flex items-center gap-1 text-lg font-semibold rc-text-heading-gradient font-inter">
									Config ID: <RcBadge variant="info">{{ props.configId }}</RcBadge>
								</span>
								<Separator orientation="vertical" class="relative w-px h-6 mx-4 shrink-0 bg-border" />
							</div>

							<!-- RIGHT BUTTONS -->
							<div class="flex items-center gap-2 ml-auto">
								<TooltipProvider>
									<!--  is needed to manage the global tooltip state, timing, and positioning context that multiple tooltips within the same area need to share. -->
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
							<!-- RIGHT BUTTONS -->

							<Separator orientation="vertical" class="relative w-px h-6 mx-4 shrink-0 bg-border" />
							<span>CONFIG</span>
						</div>
						<Separator class="relative w-full h-px shrink-0 bg-border"></Separator>
					</div>

					<!-- EDITOR -->
					<div class="code-editor__code-pre" id="code-editor__code-pre" style="height: calc(100vh - 237px);"></div>
					<!-- EDITOR -->
				</div>
			</div>
		</div>
	</div>
</template>
