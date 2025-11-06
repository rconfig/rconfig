<script setup>
import AlertInfo from "@/pages/Shared/Alerts/AlertInfo.vue";
import Loading from "@/pages/Shared/Loaders/Loading.vue";
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Copy } from "lucide-vue-next";
import { useDialogStore } from "@/stores/dialogActions";
import { useSendSnippetModal } from "@/pages/Inventory/Devices/Components/useSendSnippetModal";
import { useComponentTranslations } from "@/composables/useComponentTranslations";
import SendSnippetModalI18N from "@/i18n/pages/Inventory/Devices/Components/SendSnippetModal.i18n.js";

// Define props
const props = defineProps({
	deviceId: {
		type: Number,
		required: true,
	},
});

// Define emits
const emit = defineEmits(["closeModal", "submitSnippet"]);

// Use the dialog store for managing the dialog state
const dialogStore = useDialogStore();
const { closeDialog, isDialogOpen } = dialogStore;
const { t } = useComponentTranslations(SendSnippetModalI18N);

// Use the send snippet modal composable
const {
	// State
	copied,
	dynamicVarsArr,
	isLoading,
	selectedSnippet,
	selectedSnippetId,
	selectedSnippetForDisplay, // HTML formatted for display
	selectedSnippetPlainText, // Plain text for API/copy
	snippets,

	// Methods
	close,
	copy,
	selectSnippet,
	submitSnippet,
	updateSnippetString,
} = useSendSnippetModal(props, emit);
</script>

<template>
	<Dialog :open="isDialogOpen('SendSnippetModal')">
		<DialogTrigger as-child>
			<!-- Trigger is handled externally -->
		</DialogTrigger>

		<DialogContent class="p-0 max-w-[90vw] max-h-[90vh] w-auto h-auto overflow-auto" @escapeKeyDown="close" @pointerDownOutside="close" @closeClicked="close">
			<!-- Dialog Header -->
			<DialogHeader class="rc-dialog-header">
				<DialogTitle class="text-sm text-rcgray-200">
					<div class="flex items-center">
						<RcIcon name="config-snippet" />
						<span class="ml-2">{{ t("sendSnippetTitle") }} {{ deviceId }}</span>
					</div>
				</DialogTitle>
			</DialogHeader>

			<!-- Dialog Content -->
			<div class="grid space-y-4 py-4 px-6">
				<Loading v-if="isLoading" class="w-full" />

				<div v-else class="space-y-4">
					<!-- Snippet Selection -->
					<div class="grid items-center grid-cols-4 gap-4">
						<Label for="snippet-select" class="text-right"> {{ t("fields.selectSnippet") }} <span class="text-red-400">*</span> </Label>
						<div class="col-span-3">
							<Select v-model="selectedSnippetId">
								<SelectTrigger class="focus:outline-none focus:ring-0">
									<SelectValue :placeholder="t('placeholders.selectSnippet')" />
								</SelectTrigger>
								<SelectContent class="focus:outline-none focus:ring-0">
									<SelectItem value="0">{{ t("placeholders.selectSnippet") }}</SelectItem>
									<SelectItem v-for="snippet in snippets" :key="snippet.id" :value="snippet.id.toString()"> [ID: {{ snippet.id }}] {{ snippet.snippet_name }} </SelectItem>
								</SelectContent>
							</Select>
						</div>
					</div>

					<!-- Dynamic Variables -->
					<div v-if="Object.keys(dynamicVarsArr).length > 0">
						<div v-for="(value, key) in dynamicVarsArr" :key="key" class="grid items-center grid-cols-4 gap-4 mb-4">
							<Label :for="`dynamicfield-${key}`" class="text-right"> {{ key }} <span class="text-red-400">*</span> </Label>
							<div class="col-span-3">
								<Input :id="`dynamicfield-${key}`" :name="`dynamicfield-${key}`" v-model="dynamicVarsArr[key]" @input="updateSnippetString" required autocomplete="off" />
							</div>
						</div>
					</div>

					<!-- Snippet Preview -->
					<div v-if="selectedSnippet" class="space-y-2">
						<div class="flex items-center justify-between">
							<Label class="text-sm font-medium">{{ t("fields.snippetPreview") }}</Label>

							<Button variant="outline" size="sm" @click="copy(selectedSnippetPlainText)" class="flex items-center gap-2">
								<RcIcon name="copy-transition" :isActive="copied" />
								{{ copied ? "Copied" : "Copy snippet" }}
							</Button>
						</div>
						<div class="relative">
							<div class="flex flex-col justify-between">
								<Loading v-if="isLoading" />
								<pre v-if="!isLoading" class="p-4 rounded bg-muted overflow-auto"><code
				class="pf-v5-c-code-block__code"
				v-html="selectedSnippetForDisplay"
				style="background: none !important;"
			></code></pre>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Dialog Footer -->
			<DialogFooter class="rc-dialog-footer bg-rcgray-800">
				<Button type="button" variant="outline" class="px-2 py-1 ml-2 text-sm hover:bg-gray-700 hover:animate-pulse" @click="close" size="sm">
					{{ t("common.cancel") }}
					<div class="pl-2 ml-auto">
						<kbd class="rc-kdb-class">ESC</kbd>
					</div>
				</Button>

				<Button v-if="!isLoading" type="submit" class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse" size="sm" @click="submitSnippet" variant="primary" :disabled="!selectedSnippet">
					{{ t("actions.sendSnippet") }}
					<div class="pl-2 ml-auto">
						<kbd class="rc-kdb-class2">
							Ctrl&nbsp;
							<RcIcon name="enter" class="ml-1" />
						</kbd>
					</div>
				</Button>
			</DialogFooter>
		</DialogContent>
	</Dialog>
</template>

<style scoped>
.code-textarea {
	font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
	font-size: 0.875rem;
	line-height: 1.6;
	tab-size: 4;
	letter-spacing: 0;
	white-space: pre-wrap;
	padding: 0.75rem;
	background-color: rgba(0, 0, 0, 0.02);
	border: 1px solid rgba(0, 0, 0, 0.1);
	border-radius: 0.25rem;
}

/* Dark mode support */
:deep(.dark) .code-textarea {
	background-color: rgba(255, 255, 255, 0.05);
	border-color: rgba(255, 255, 255, 0.1);
	color: rgba(255, 255, 255, 0.9);
}

/* Optional: Add syntax highlighting-like styling */
.code-textarea::selection {
	background-color: rgba(59, 130, 246, 0.3);
}
</style>
