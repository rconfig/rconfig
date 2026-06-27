<script setup>
import { computed } from "vue";
import { Button } from "@/components/ui/button";
import RcBadge from "@/pages/Shared/Badges/RcBadge.vue";
import { PencilLine, RotateCcw, SearchCheck } from "lucide-vue-next";

const props = defineProps({
	model: {
		type: Object,
		required: true,
	},
});

const emit = defineEmits(["clear", "edit"]);

function summarizeSelections(items, key, label) {
	if (!Array.isArray(items) || items.length === 0) {
		return null;
	}

	const labels = items
		.map((item) => item?.[key])
		.filter(Boolean);

	if (labels.length === 0) {
		return null;
	}

	if (labels.length <= 2) {
		return `${label}: ${labels.join(", ")}`;
	}

	return `${label}: ${labels.slice(0, 2).join(", ")} +${labels.length - 2}`;
}

const criteriaChips = computed(() => props.model.criteria.filter((criterion) => criterion.term.trim() !== "").slice(0, 4));

const summaryChips = computed(() => {
	const chips = [
		summarizeSelections(props.model.devices, "device_name", "Devices"),
		summarizeSelections(props.model.categories, "categoryName", "Groups"),
		summarizeSelections(props.model.commands, "command", "Commands"),
		summarizeSelections(props.model.tags, "tagname", "Tags"),
	];

	if (props.model.start_date || props.model.end_date) {
		chips.push(`Dates: ${props.model.start_date || "Any"} to ${props.model.end_date || "Any"}`);
	}

	if (props.model.limit) {
		chips.push(`Limit: ${props.model.limit}`);
	}

	chips.push(props.model.results_per_config === "all_matches" ? "Results: All matches" : "Results: First match");
	chips.push(`${props.model.lines_before} before / ${props.model.lines_after} after`);

	if (props.model.ignore_case) {
		chips.push("Ignore case");
	}

	if (props.model.latest_version_only) {
		chips.push("Latest version only");
	}

	return chips.filter(Boolean);
});
</script>

<template>
	<div class="rounded-[28px] border border-border/60 bg-card/95 px-5 py-4 shadow-sm backdrop-blur">
		<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
			<div class="min-w-0 flex-1 space-y-3">
				<div class="flex flex-wrap items-center gap-2">
					<RcBadge variant="success" size="large" :interactive="false" class="gap-2 border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800 ring-emerald-200">
						<SearchCheck class="h-3.5 w-3.5" />
						Active Search
					</RcBadge>
					<span class="text-sm text-muted-foreground">
						{{ props.model.criteria_mode === "all" ? "All terms must match" : "Any term can match" }}
					</span>
				</div>

				<div class="flex flex-wrap gap-2">
					<RcBadge v-for="criterion in criteriaChips" :key="criterion.id" variant="info" :interactive="false" class="py-1 text-xs font-medium">
						{{ criterion.term }}
					</RcBadge>
					<RcBadge v-if="props.model.criteria.length > criteriaChips.length" variant="info" :interactive="false" class="py-1 text-xs font-medium">
						+{{ props.model.criteria.length - criteriaChips.length }} more terms
					</RcBadge>
					<RcBadge v-for="chip in summaryChips" :key="chip" variant="outline" :interactive="false" class="px-3 py-1 text-xs font-medium text-foreground/80">
						{{ chip }}
					</RcBadge>
				</div>
			</div>

			<div class="flex items-center gap-2 lg:pl-4">
				<Button variant="outline" size="sm" class="px-4 text-xs font-medium rc-btn-shadow hover:animate-pulse" @click="emit('edit')">
					<PencilLine class="mr-2 h-3.5 w-3.5" />
					Edit Filters
				</Button>
				<Button variant="default" size="sm" class="px-4 text-xs font-medium btn-primary-action hover:animate-pulse" @click="emit('clear')">
					<RotateCcw class="mr-2 h-3.5 w-3.5" />
					Clear
				</Button>
			</div>
		</div>
	</div>
</template>
