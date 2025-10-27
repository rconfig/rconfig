<script setup>
import Loading from "@/pages/Shared/Loaders/Loading.vue";
import axios from "axios";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from "@/components/ui/select";
import { ref, onUnmounted, onMounted, computed } from "vue";
import { useDialogStore } from "@/stores/dialogActions";
import { useToaster } from "@/composables/useToaster"; // Import the composable
import { useClipboard } from "@vueuse/core";

const activeIcons = ref({});
const dialogStore = useDialogStore();
const hoverIcons = ref({});
const isLoading = ref(true);
const selectedLanguage = ref(localStorage.getItem("selectedLanguage") || "language-plaintext");
const { closeDialog, isDialogOpen } = dialogStore;
const { toastError } = useToaster();
const { text, copy, copied, isSupported } = useClipboard();

const props = defineProps({
	editId: {
		type: Number,
		required: true,
	},
	record: {
		type: Object,
		required: true,
	},
	searchString: {
		type: String,
		required: true,
	},
});
// Destructuring props
const { matches } = props.record;
const languages = ["bash", "c", "cpp", "css", "html", "java", "javascript", "language-plaintext", "php", "python", "typescript"];

onMounted(() => {
	window.addEventListener("keydown", handleKeyDown);
	isLoading.value = false;
});

const highlightMatch = (context) => {
	if (!props.searchString || !context) return context;

	// Convert context to string if it's an array
	const contextString = Array.isArray(context) ? context.join("\n") : String(context);

	// Escape special regex characters in the search string
	const escapedSearchString = props.searchString.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");

	// Create a regular expression to highlight all occurrences of searchString
	const regex = new RegExp(`(${escapedSearchString})`, "gi");

	// Replace matches with highlighted spans and preserve line breaks
	const highlightedContext = contextString.replace(regex, '<span class="highlightMatch">$1</span>').replace(/\n/g, "<br>");

	return highlightedContext;
};

function handleKeyDown(event) {
	if (event.key === "Escape") {
		handleClose();
	}
}

const fileLocation = computed(() => {
	return props.record.file;
});

onUnmounted(() => {
	window.removeEventListener("keydown", handleKeyDown);
});

const handleMouseOver = (key) => {
	hoverIcons.value[key] = true;
};

const handleMouseLeave = (key) => {
	hoverIcons.value[key] = false;
};

function handleClose() {
	closeDialog("peek-config-search-matches-dialog-" + props.editId);
}
</script>

<template>
	<Dialog :open="isDialogOpen('peek-config-search-matches-dialog-' + editId)">
		<DialogTrigger as-child>
			<slot />
		</DialogTrigger>
		<DialogContent class="sm:max-w-[70dvw] grid-rows-[auto_minmax(0,1fr)_auto] p-0 max-h-[90dvh]" @escapeKeyDown="closeDialog('peek-config-search-matches-dialog-' + editId)" @pointerDownOutside="closeDialog('peek-config-search-matches-dialog-' + editId)" @closeClicked="closeDialog('peek-config-search-matches-dialog-' + editId)">
			<DialogHeader class="p-6 pb-0">
				<DialogTitle>Configuration Quick Peek (Config ID: {{ editId }})</DialogTitle>
				<DialogDescription>
					<div class="flex justify-between">
						<div class="flex items-center">
							<div class="mr-2">Language Options:</div>
							<Select v-model="selectedLanguage">
								<SelectTrigger class="w-[180px]">
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
						</div>
						<div class="flex items-center">
							Path: {{ fileLocation }}
							<span class="ml-2 cursor-pointer" @mouseover="handleMouseOver('fileLocation')" @mouseleave="handleMouseLeave('fileLocation')" @click="copy(fileLocation)">
								<RcIcon name="copy-transition" :isActive="copied" :size="16" />
							</span>
						</div>
					</div>
				</DialogDescription>
			</DialogHeader>

			<div class="grid gap-4 px-6 py-4 overflow-y-auto">
				<div class="flex flex-col justify-between">
					<Loading v-if="isLoading" />

					<div v-else class="space-y-4">
						<div v-for="(match, index) in matches" :key="index" class="context-block border rounded-lg p-4">
							<div class="mb-2">
								<span class="text-sm font-medium rc-text-sm-muted underline"> Matched Line: {{ match.line_number }} </span>
							</div>

							<pre v-highlightjs :class="selectedLanguage" class="whitespace-pre-wrap text-sm p-3 rounded border overflow-x-auto" v-html="highlightMatch(match.context)"></pre>
						</div>
					</div>
				</div>
			</div>

			<DialogFooter class="p-6 pt-0">
				<Button @click="handleClose()" variant="outline">
					Close
				</Button>
			</DialogFooter>
		</DialogContent>
	</Dialog>
</template>

<style>
.highlightMatch {
	background-color: #df8e1d !important;
	font-weight: bold !important;
	color: #000 !important;
}
</style>
