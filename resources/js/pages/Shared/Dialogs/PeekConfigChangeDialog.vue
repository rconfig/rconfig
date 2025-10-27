<script setup lang="ts">
import Loading from "@/pages/Shared/Loaders/Loading.vue";
import axios from "axios";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { ref, onUnmounted, onMounted, computed } from "vue";
import { useDialogStore } from "@/stores/dialogActions";
import { useToaster } from "@/composables/useToaster";
import { useClipboard } from "@vueuse/core";

const dialogStore = useDialogStore();
const fileLocation = ref("");
const isLoading = ref(true);
const processedCode = ref("");
const fontSize = ref(parseInt(localStorage.getItem("peekFontSize") || "14", 10)); // Default 14px

const { closeDialog, isDialogOpen } = dialogStore;
const { toastError } = useToaster();
const { copy, copied } = useClipboard();

const props = defineProps({
	editId: Number,
});

// ✅ Computed style for dynamic font size using CSS custom properties
const codeStyle = computed(() => ({
	"--dynamic-font-size": `${fontSize.value}px`,
	"--dynamic-line-height": `${fontSize.value * 1.4}px`,
}));

onMounted(() => {
	showConfig();
	window.addEventListener("keydown", handleKeyDown);
});

function showConfig() {
	processedCode.value = "";
	isLoading.value = true;
	axios
		.get("/api/config-changes/" + props.editId)
		.then((response) => {
			processedCode.value = response.data.config_diff;
			fileLocation.value = response.data.current_config.config_location;
			isLoading.value = false;
		})
		.catch((error) => {
			console.log(error);
			processedCode.value = "Something went wrong - could not retrieve the template from the file system!";
			toastError("Error", "Could not retrieve the template from the file system!");
			isLoading.value = false;
		});
}

function handleKeyDown(event: KeyboardEvent) {
	if (event.key === "Escape") {
		handleClose();
	}
}

onUnmounted(() => {
	window.removeEventListener("keydown", handleKeyDown);
});

function handleClose() {
	closeDialog("peek-config-change-dialog-" + props.editId);
}

// ✅ Font size controls
function increaseFont() {
	fontSize.value = Math.min(fontSize.value + 2, 32);
	localStorage.setItem("peekFontSize", fontSize.value.toString());
}

function decreaseFont() {
	fontSize.value = Math.max(fontSize.value - 2, 10);
	localStorage.setItem("peekFontSize", fontSize.value.toString());
}

function resetFont() {
	fontSize.value = 14;
	localStorage.setItem("peekFontSize", fontSize.value.toString());
}
</script>

<template>
	<Dialog :open="isDialogOpen('peek-config-change-dialog-' + editId)">
		<DialogTrigger as-child>
			<slot />
		</DialogTrigger>
		<DialogContent class="sm:max-w-[70dvw] max-h-[90dvh] grid grid-rows-[auto_minmax(0,1fr)_auto] p-0" @escapeKeyDown="closeDialog('peek-config-change-dialog-' + editId)" @pointerDownOutside="closeDialog('peek-config-change-dialog-' + editId)" @closeClicked="closeDialog('peek-config-change-dialog-' + editId)">
			<DialogHeader class="p-6 pb-0">
				<DialogTitle>Configuration Change Quick Peek (Config Change ID: {{ editId }})</DialogTitle>
				<DialogDescription>
					<div class="flex justify-between items-center mt-2">
						<div class="flex items-center gap-2">
							<!-- ✅ Font size controls -->
							<div class="flex items-center gap-2">
								<Button variant="outline" size="sm" @click="decreaseFont">A-</Button>
								<Button variant="outline" size="sm" @click="resetFont">A</Button>
								<Button variant="outline" size="sm" @click="increaseFont">A+</Button>
							</div>
							<!-- ✅ Display current font size -->
							<!-- <span class="text-xs text-muted-foreground">{{ fontSize }}px</span> -->
						</div>

						<!-- <div class="flex items-center">
							<Button variant="outline" size="sm" @click="copy(fileLocation)" class="flex items-center gap-2">
								<RcIcon name="copy-transition" :isActive="copied" />
								{{ copied ? "Copied" : "Copy path" }}
							</Button>
						</div> -->
					</div>
				</DialogDescription>
			</DialogHeader>

			<!-- Scrollable region -->
			<div class="overflow-y-auto px-6 py-2">
				<Loading v-if="isLoading" />

				<div class="bg-muted/30 border rounded-2xl p-4 overflow-auto bg-rcgray-900" v-if="!isLoading">
					<!-- ✅ Apply dynamic font size using CSS custom properties -->
					<div class="dynamic-font-container font-mono whitespace-pre-wrap" :style="codeStyle">
						<span v-if="processedCode === ''" class="text-muted-foreground italic">No changes detected</span>
						<span class="rc-text-sm-muted" v-html="processedCode" v-if="processedCode !== ''" />
					</div>
				</div>
			</div>

			<DialogFooter class="p-6 pt-0">
				<Button @click="handleClose()" variant="outline">Close</Button>
			</DialogFooter>
		</DialogContent>
	</Dialog>
</template>

<style scoped>
/* ✅ CSS to enforce dynamic font size on all nested HTML elements */
.dynamic-font-container,
.dynamic-font-container * {
	font-size: var(--dynamic-font-size) !important;
	line-height: var(--dynamic-line-height) !important;
}

/* Preserve font-family for code elements */
.dynamic-font-container {
	font-family: ui-monospace, SFMono-Regular, "SF Mono", Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}
</style>
