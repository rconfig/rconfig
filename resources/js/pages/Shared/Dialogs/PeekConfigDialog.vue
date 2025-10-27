<script setup lang="ts">
import Loading from "@/pages/Shared/Loaders/Loading.vue";
import axios from "axios";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { ref, onUnmounted, onMounted } from "vue";
import { useDialogStore } from "@/stores/dialogActions";
import { useToaster } from "@/composables/useToaster";
import { useClipboard } from "@vueuse/core";

const dialogStore = useDialogStore();
const fileLocation = ref("");
const isLoading = ref(true);
const processedCode = ref("");
const ishardlink = ref(0);
const selectedLanguage = ref(localStorage.getItem("selectedPeekLanguage") || "language-plaintext");
const fontSize = ref(parseInt(localStorage.getItem("peekFontSize") || "14", 10)); // Default 14px

const { closeDialog, isDialogOpen } = dialogStore;
const { toastError } = useToaster();
const { copy, copied } = useClipboard();

const props = defineProps({
	editId: Number,
});

const languages = ["bash", "c", "cpp", "css", "html", "java", "javascript", "language-plaintext", "php", "python", "typescript"];

onMounted(() => {
	showConfig();
	window.addEventListener("keydown", handleKeyDown);
});

function showConfig() {
	processedCode.value = "";
	isLoading.value = true;
	axios
		.get("/api/configs/view-config/" + props.editId)
		.then((response) => {
			processedCode.value = response.data.data.content;
			fileLocation.value = response.data.data.config_location;
			ishardlink.value = response.data.data.is_hardlink;
			selectedLanguage.value = localStorage.getItem("selectedPeekLanguage") || "language-plaintext";
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
	closeDialog("peek-config-dialog-" + props.editId);
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
	<Dialog :open="isDialogOpen('peek-config-dialog-' + editId)">
		<DialogTrigger as-child>
			<slot />
		</DialogTrigger>
		<DialogContent class="sm:max-w-[70dvw] max-h-[90dvh] grid grid-rows-[auto_minmax(0,1fr)_auto] p-0" @escapeKeyDown="closeDialog('peek-config-dialog-' + editId)" @pointerDownOutside="closeDialog('peek-config-dialog-' + editId)" @closeClicked="closeDialog('peek-config-dialog-' + editId)">
			<DialogHeader class="p-6 pb-0">
				<DialogTitle>Configuration Quick Peek (Config ID: {{ editId }})</DialogTitle>
				<DialogDescription>
					<div class="flex justify-between items-center">
						<div class="flex items-center gap-2">
							<div>Language:</div>
							<Select v-model="selectedLanguage">
								<SelectTrigger class="w-[180px] focus:ring-0 focus:outline-none">
									<SelectValue placeholder="Select a language" />
								</SelectTrigger>
								<SelectContent>
									<SelectGroup>
										<SelectItem :value="language" v-for="language in languages" :key="language">
											{{ language }}
										</SelectItem>
									</SelectGroup>
								</SelectContent>
							</Select>

							<!-- ✅ Font size controls -->
							<div class="flex items-center gap-2">
								<Button variant="outline" size="sm" @click="decreaseFont">A-</Button>
								<Button variant="outline" size="sm" @click="resetFont">A</Button>
								<Button variant="outline" size="sm" @click="increaseFont">A+</Button>
							</div>
						</div>
						
						<div class="flex items-center gap-4">
							<div v-if="ishardlink === 1" size="sm" class="text-sm text-blue-400 font-medium flex justify-between items-center">
								🔗 HardLink
							</div>
							<Button variant="outline" size="sm" @click="copy(fileLocation)" class="flex items-center gap-2">
								<RcIcon name="copy-transition" :isActive="copied" />
								{{ copied ? "Copied" : "Copy path" }}
							</Button>
						</div>
					</div>
				</DialogDescription>
			</DialogHeader>

			<!-- Scrollable region -->
			<div class="overflow-y-auto px-6 py-4">
				<Loading v-if="isLoading" />
				<pre v-highlightjs class="rounded bg-muted overflow-auto whitespace-pre"><code class="pf-v5-c-code-block__code " :class="selectedLanguage" style="background: none !important;" :style="{ background: 'none', fontSize: fontSize + 'px' }">{{ processedCode }}
            </code> 
         </pre>
			</div>

			<DialogFooter class="p-6 pt-0">
				<Button @click="handleClose()" variant="outline">Close</Button>
			</DialogFooter>
		</DialogContent>
	</Dialog>
</template>
