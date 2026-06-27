<script setup>
import Loading from "@/pages/Shared/Table/Loading.vue";
import AlertWarning from "@/pages/Shared/Alerts/AlertWarning.vue";
import Pagination from "@/pages/Shared/Table/Pagination.vue";
import PeekConfigDialog from "@/pages/Shared/Dialogs/PeekConfigDialog.vue";
import PeekConfigSearchMatchesDialog from "@/pages/Shared/Dialogs/PeekConfigSearchMatchesDialog.vue";
import RcBadge from "@/pages/Shared/Badges/RcBadge.vue";
import { Button } from "@/components/ui/button";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { useResultsTable } from "./useResultsTable";
import { Eye, FileSearch } from "lucide-vue-next";

const props = defineProps({
	filters: Object,
});

const { currentPage, errors, formatters, isDialogOpen, isFetching, lastPage, openDialog, perPage, resultMeta, results, searchModel, totalRecords, viewDetailsPane } = useResultsTable(props);

function escapeHtml(value) {
	return String(value ?? "")
		.replaceAll("&", "&amp;")
		.replaceAll("<", "&lt;")
		.replaceAll(">", "&gt;")
		.replaceAll('"', "&quot;")
		.replaceAll("'", "&#39;");
}

function escapeRegExp(value) {
	return String(value).replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

function highlightPreviewText(lineText, matchedTerms = []) {
	const sourceText = String(lineText ?? "");
	const terms = [...new Set((Array.isArray(matchedTerms) ? matchedTerms : []).map((term) => String(term ?? "").trim()).filter(Boolean))]
		.sort((left, right) => right.length - left.length);

	if (sourceText.length === 0 || terms.length === 0) {
		return escapeHtml(sourceText);
	}

	const pattern = new RegExp(terms.map(escapeRegExp).join("|"), "gi");
	let highlighted = "";
	let lastIndex = 0;

	for (const match of sourceText.matchAll(pattern)) {
		const matchText = match[0];
		const matchIndex = match.index ?? 0;

		highlighted += escapeHtml(sourceText.slice(lastIndex, matchIndex));
		highlighted += `<mark class="rounded bg-amber-200/80 px-1 py-0 text-foreground shadow-sm dark:bg-amber-500/30">${escapeHtml(matchText)}</mark>`;
		lastIndex = matchIndex + matchText.length;
	}

	if (lastIndex === 0) {
		return escapeHtml(sourceText);
	}

	highlighted += escapeHtml(sourceText.slice(lastIndex));
	return highlighted;
}
</script>

<template>
	<div class="px-6">
		<AlertWarning
			v-if="resultMeta.limit_reached"
			title="Result limit reached"
			:small="true"
			:message="`Showing the first ${resultMeta.limit} matching configs. Refine your terms or filters, or increase Search Limit to inspect more results.`"
			class="mb-4"
		/>
		<div v-if="searchModel.search_terms.length" class="mb-4 flex flex-wrap items-center gap-2 border-b border-border/60 px-4 py-3 text-sm">
			<span class="font-medium text-foreground">Searching:</span>
			<RcBadge variant="info" v-for="term in searchModel.search_terms" :key="term" class="rounded-full border border-border bg-background px-3 py-1 text-xs font-medium">
				{{ term }}
			</RcBadge>
			<span class="ml-auto text-xs text-muted-foreground">
				{{ searchModel.criteria_mode === "all" ? "All terms must match" : "Any term may match" }}
			</span>
		</div>

		<Table>
			<TableHeader>
				<TableRow>
					<TableHead class="w-[5%]">Device</TableHead>
					<TableHead class="w-[10%]">Command</TableHead>
					<TableHead class="w-[10%]">Config date</TableHead>
					<TableHead class="w-[10%]">View Matches</TableHead>
					<TableHead class="w-[40%]">Preview</TableHead>
					<TableHead class="w-[10%]">Actions</TableHead>
				</TableRow>
			</TableHeader>
			<TableBody>
				<Loading v-if="isFetching" />

				<template v-else>
					<TableRow v-for="row in results" :key="row.id">
						<TableCell class="text-start">{{ row.device_name }}</TableCell>
						<TableCell class="text-start">{{ row.command || row.config_command }}</TableCell>
						<TableCell class="text-start">{{ formatters.formatTime(row.created_at || row.config_date) }}</TableCell>
						<TableCell class="text-start">
							<TooltipProvider>
								<Tooltip>
									<TooltipTrigger as-child>
										<Button
											variant="ghost"
											@click="openDialog('peek-config-search-matches-dialog-' + row.id)"
											class="group rounded-xl border border-border/60 bg-muted/35 px-2.5 py-1 text-foreground shadow-xs transition-colors hover:bg-sky-50 hover:text-sky-900 dark:bg-rcgray-900/50 dark:hover:bg-sky-950/35 dark:hover:text-sky-100"
										>
											<span class="flex items-center rounded-lg">
												<Eye class="mr-2 text-muted-foreground transition-colors group-hover:text-blue-500" size="16" />
												{{ row.match_count ? row.match_count : "No" }} {{ row.match_count === 1 ? "match" : "matches" }}
											</span>
										</Button>
									</TooltipTrigger>
									<TooltipContent class="text-white bg-rcgray-800">
										<p>View Matches</p>
									</TooltipContent>
								</Tooltip>
							</TooltipProvider>
						</TableCell>
						<TableCell class="text-start align-top">
							<div v-if="row.preview_match" class="space-y-1">
								<div class="text-xs text-muted-foreground">
									Line {{ row.preview_match.line_number }}
									<span v-if="row.preview_match.matched_terms?.length">· {{ row.preview_match.matched_terms.join(", ") }}</span>
								</div>
								<p
									class="line-clamp-3 text-sm text-foreground/90"
									v-html="highlightPreviewText(row.preview_match.line_text, row.preview_match.matched_terms?.length ? row.preview_match.matched_terms : searchModel.search_terms)"
								></p>
							</div>
							<span v-else class="text-sm text-muted-foreground">No preview available</span>
						</TableCell>
						<TableCell class="flex items-center">
							<TooltipProvider>
								<Tooltip>
									<TooltipTrigger as-child>
										<Button variant="ghost" @click="openDialog('peek-config-dialog-' + row.id)">
											<RcIcon name="peek-eye" />
										</Button>
									</TooltipTrigger>
									<TooltipContent class="text-white bg-rcgray-800">
										<p>Peek Config</p>
									</TooltipContent>
								</Tooltip>
							</TooltipProvider>

							<TooltipProvider>
								<Tooltip>
									<TooltipTrigger as-child>
										<Button variant="ghost" @click="viewDetailsPane(row.id)">
											<FileSearch class="size-5 text-muted-foreground hover:text-blue-500" />
										</Button>
									</TooltipTrigger>
									<TooltipContent class="text-white bg-rcgray-800">
										<p>Open Config</p>
									</TooltipContent>
								</Tooltip>
							</TooltipProvider>

							<PeekConfigSearchMatchesDialog
								v-if="isDialogOpen('peek-config-search-matches-dialog-' + row.id)"
								:record="row"
								:edit-id="row.id"
								:search-string="(searchModel.search_terms && searchModel.search_terms[0]) || ''"
								:search-terms="searchModel.search_terms"
							/>

							<PeekConfigDialog :edit-id="row.id" v-if="isDialogOpen('peek-config-dialog-' + row.id)"></PeekConfigDialog>
						</TableCell>
					</TableRow>
				</template>
			</TableBody>
		</Table>

		<div v-if="!isFetching && results.length === 0" class="flex items-center justify-center my-4">
			<div v-if="Object.keys(errors).length === 0">No results found</div>
			<div v-if="Object.keys(errors).length">
				<span v-for="error in errors" :key="error" class="col-span-3 col-start-2 text-sm text-red-400">
					<br />
					{{ error[0] }}
				</span>
			</div>
		</div>

		<Pagination
			v-if="totalRecords > 0"
			:current-page="currentPage"
			:last-page="lastPage"
			:per-page="perPage"
			:total-records="totalRecords"
			:is-loading="isFetching"
			@update:current-page="currentPage = $event"
			@update:per-page="perPage = $event"
		/>
	</div>
</template>
