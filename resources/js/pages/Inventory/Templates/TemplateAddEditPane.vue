<script setup>
import * as monaco from "monaco-editor";
import GenericPopover from "@/pages/Shared/Popover/GeneralPopover.vue";
import RcToolTip from "@/pages/Shared/Tooltips/RcToolTip.vue";
import TemplateImportDialog from "@/pages/Inventory/Templates/TemplateImportDialog.vue";
import jsYaml from "js-yaml";
import useCodeEditor from "@/composables/codeEditorFunctions";
import useTemplateAddEdit from "@/pages/Inventory/Templates/useTemplateAddEdit";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Skeleton } from "@/components/ui/skeleton";
import { ToggleGroup, ToggleGroupItem } from "@/components/ui/toggle-group";
import { TooltipProvider } from "@/components/ui/tooltip";
import { ref, onMounted, onUnmounted } from "vue";
import { useDebounceFn } from "@vueuse/core";
import { useDialogStore } from "@/stores/dialogActions";
import { useTemplatesGithub } from "@/pages/Inventory/Templates/useTemplatesGithub";
import { Github, X, Download, SunMoon, Map, Hash, MessageCircleQuestion } from "lucide-vue-next";
 
const props = defineProps({
	editId: Number,
});

let meditor = null;
const emit = defineEmits(["save", "close"]);

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
	download,
	initEditor,
	toggleEditorDarkMode,
	toggleEditorLineNumbers,
	toggleEditorMinimap,
	toggleStickyScroll,
} = useCodeEditor(monaco);
const { openImportDialog, getTemplateRepoFolders, hasVendorTemplateOptions } = useTemplatesGithub();
const { code, errors, isLoading, getDefaultTemplate, handleKeyDown, model, parseYaml, reformatTemplateCode, saveDialog, showTemplate } = useTemplateAddEdit(props, emit);
const dialogStore = useDialogStore();
const { isDialogOpen, closeDialog } = dialogStore;

const toggleStateMultiple = ref([]); //'dark', 'lineNumbers', 'minimap', 'stickyscroll'

// Lifecycle Hooks
onMounted(() => {
	initializeToggleStates();
	getTemplateRepoFolders();
	initCodeEditor();

	window.addEventListener("keydown", (e) => {
		if (e.key === "Escape") {
			onEsc();
		}
	});
});

onUnmounted(() => {
	window.removeEventListener("keydown", (e) => {
		if (e.key === "Escape") {
			onEsc();
		}
	});
});

// Methods
function initializeToggleStates() {
	if (checkDarkModeIsSet()) toggleStateMultiple.value.push("dark");
	if (checkLineNumbersIsSet()) toggleStateMultiple.value.push("lineNumbers");
	if (checkMiniMapIsSet()) toggleStateMultiple.value.push("minimap");
	if (checkStickyScrollIsSet()) toggleStateMultiple.value.push("stickyscroll");
}

// Add this to your initCodeEditor function
function initCodeEditor() {
	try {
		meditor = initEditor("code-editor__code-pre", "yaml");
		props.editId === 0 ? getDefaultTemplate(meditor) : showTemplate(props.editId, meditor, model);

		// Set up change event listener with debouncing
		meditor.onDidChangeModelContent(() => {
			const newContent = meditor.getValue();
			code.value = newContent;
			processEditorChanges(newContent);
		});
	} catch (err) {
		console.error("Error initializing editor:", err);
	}
}

const processEditorChanges = useDebounceFn((content) => {
	// Parse YAML to get updated template name and description
	try {
		const parsedYaml = parseYaml(content);
		if (parsedYaml && parsedYaml.main) {
			// Only update if values exist and have changed
			if (parsedYaml.main.name && parsedYaml.main.name !== model.templateName) {
				model.value.templateName = parsedYaml.main.name;
			}

			const description = parsedYaml.main.desc || parsedYaml.main.description;
			if (description && description !== model.description) {
				model.value.description = description;
			}
		}
	} catch (e) {
		console.log("Error parsing YAML while watching for changes:", e);
	}
}, 500); // 500ms debounce

function close() {
	emit("close");
	if (meditor) {
		meditor.dispose();
		meditor = null;
	}
}

function onEsc() {
	close();
}

function handleReformat() {
	reformatTemplateCode(meditor);
}

function handleSave() {
	saveDialog(props.editId, model, meditor, emit, close);
}

function setTemplateCode(code) {
	meditor.getModel().setValue(code.value);
	closeDialog("DialogTemplateImport");
}
</script>

<template>
	<div>
		<div class="flex justify-between p-2 border-b">
			<Button @click="close()" size="sm" variant="outline" class="gap-1 border-none hover:bg-rcgray-600"> <X size="16" class="hover:animate-pulse" /> </Button>
			<h2 class="items-center content-center text-muted-foreground">{{ editId === 0 ? "Add Template" : "Edit Template" }} {{ editId === 0 ? "" : "(" + editId + ")" }}</h2>
			<div class="flex justify-end">
				<!-- EMPTY -->
			</div>
		</div>

		<ResizablePanelGroup direction="horizontal" class="">
			<ResizablePanel :default-size="25" :max-size="30" :min-size="10" collapsible :collapsed-size="0" ref="panelElement44" class="h-[90vh]">
				<div class="">
					<!-- Loading Skeleton -->
					<div v-if="isLoading" class="grid gap-4 px-4 py-4">
						<!-- Form Fields Skeleton -->
						<div class="grid items-center grid-cols-4 gap-4">
							<Skeleton class="h-4 w-24 justify-self-end" />
							<Skeleton class="h-10 col-span-3" />
						</div>

						<div class="grid items-center grid-cols-4 gap-4">
							<Skeleton class="h-4 w-20 justify-self-end" />
							<Skeleton class="h-10 col-span-3" />
						</div>

						<div class="grid items-center grid-cols-4 gap-4 mb-2">
							<div class="justify-self-end"></div>
							<Skeleton class="h-3 w-40" />
						</div>

						<div class="grid items-center grid-cols-4 gap-4">
							<Skeleton class="h-4 w-32 justify-self-end" />
							<Skeleton class="h-10 w-40" />
						</div>

						<!-- Action Buttons Skeleton -->
						<div class="flex justify-end pt-4 gap-2">
							<Skeleton class="h-8 w-16" />
							<Skeleton class="h-8 w-16" />
						</div>
					</div>

					<!-- Actual Form Content -->
					<div class="pb-4 px-4 mt-4" v-else>
						<div v-if="hasVendorTemplateOptions">
							<Label for="description" class="block mb-2">Imported Templates</Label>
							<Button type="close" @click="openImportDialog()" class="px-2 py-1 text-sm hover:bg-gray-700" variant="outline">
								<Github size="16" class="mr-2" />
								Choose Template to Import
							</Button>
						</div>

						<Separator class="relative w-full h-px shrink-0 bg-border mb-4 my-4" v-if="hasVendorTemplateOptions" />

						<div class="grid gap-4">
							<div>
								<Label for="templateName" class="block mb-2">
									Template Name
									<span class="text-red-600">*</span>
								</Label>
								<Input v-model="model.templateName" id="templateName" autocomplete="off" class="w-full" disabled />
								<span class="text-sm text-red-400 mt-1 block" v-if="errors.templateName">
									{{ errors.templateName[0] }}
								</span>
							</div>

							<div>
								<Label for="description" class="block mb-2">Description</Label>
								<Input v-model="model.description" id="description" autocomplete="off" class="w-full" disabled />
								<span class="text-sm text-red-400 mt-1 block" v-if="errors.description">
									{{ errors.description[0] }}
								</span>
							</div>

							<div class="mb-2">
								<span class="rc-text-xs-muted">Template name and description will be updated from YAML after editing</span>
							</div>

							<span class="text-sm text-red-400" v-if="errors.code">
								{{ errors.code[0] }}
							</span>
						</div>

						<!-- Action Buttons -->
						<div class="flex justify-end my-4 gap-2 hidden md:flex">
							<Button type="close" variant="outline" class="px-2 py-1 text-sm hover:bg-gray-700 hover:animate-pulse" @click="close()" size="sm">
								Cancel
								<div class="pl-2 ml-auto">
									<kbd class="rc-kdb-class">ESC</kbd>
								</div>
							</Button>
							<Button v-if="props.editId === 0" type="submit" class="px-2 py-1 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse" size="sm" @click="handleSave" variant="primary">
								Save
								<div class="pl-2 ml-auto">
									<kbd class="rc-kdb-class2">
										Ctrl&nbsp;
										<RcIcon name="enter" class="ml-1" />
									</kbd>
								</div>
							</Button>
							<Button v-else type="submit" class="px-2 py-1 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse" size="sm" @click="handleSave" variant="primary">
								Update
								<div class="pl-2 ml-auto">
									<kbd class="rc-kdb-class2">
										Ctrl&nbsp;
										<RcIcon name="enter" class="ml-1" />
									</kbd>
								</div>
							</Button>
						</div>

						<!-- Reformat Button -->
						<Separator class="relative w-full h-px shrink-0 bg-border mb-4 mt-2" />

						<div class="pt-2 hidden md:block">
							<Button type="close" variant="outline" class="px-2 py-1 text-sm hover:bg-gray-700 hover:animate-pulse mr-2" @click="handleReformat()" size="sm">
								Reformat Code
							</Button>
							<GenericPopover :title="'Updated Template Format'" :description="'From V8.0.0, templates use a cleaner layout. This format is backward-compatible with older versions. Use the Reformat button to update the template in the editor if it hasn’t been reformatted yet.'" :hasLink="false" :align="'start'">
								<template #trigger>
									<Button variant="link" size="sm" class="text-blue-400 hover:text-blue-300 p-0">
										<MessageCircleQuestion class="h-3.5 w-3.5" />
									</Button>
								</template>
							</GenericPopover>
						</div>
					</div>
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
										<Button variant="ghost" @click="download(model.fileName)" class="px-2 py-1 ml-1 rc-btn-shadow">
											<Download size="16" />
										</Button>
									</template>
								</RcToolTip>
							</div>
							<!-- Skeleton for LEFT BUTTONS -->
							<div v-else class="flex gap-1">
								<Skeleton class="h-8 w-8" />
								<Skeleton class="h-8 w-8" />
							</div>

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
							<!-- Skeleton for RIGHT BUTTONS -->
							<div v-else class="flex items-center gap-2 ml-auto">
								<div class="flex gap-1">
									<Skeleton class="h-8 w-8" />
									<Skeleton class="h-8 w-8" />
									<Skeleton class="h-8 w-8" />
									<Skeleton class="h-8 w-8" />
								</div>
							</div>

							<Separator orientation="vertical" class="relative w-px h-6 mx-4 shrink-0 bg-border" />
							<span v-if="!isLoading">YAML</span>
							<Skeleton v-else class="h-4 w-8" />
						</div>
						<Separator class="relative w-full h-px shrink-0 bg-border"></Separator>
					</div>

					<!-- EDITOR - Always present in DOM for Monaco -->
					<div class="relative">
						<!-- Editor Skeleton Overlay -->
						<div v-if="isLoading" class="absolute inset-0 z-10 w-full p-4" style="height: calc(100vh - 190px);">
							<Skeleton class="w-full h-full" />
						</div>
						<!-- Actual Editor -->
						<div class="code-editor__code-pre" id="code-editor__code-pre" style="height: calc(100vh - 190px);"></div>
					</div>
				</ScrollArea>
			</ResizablePanel>
		</ResizablePanelGroup>

		<TemplateImportDialog v-if="isDialogOpen('DialogTemplateImport')" @setTemplateCode="setTemplateCode($event)" />
	</div>
</template>