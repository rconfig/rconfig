<script setup>
import RcBadge from "@/pages/Shared/Badges/RcBadge.vue";
import RcListPopoverI18N from "@/i18n/pages/Shared/Popover/RcListPopover.i18n.js";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { computed } from "vue";
import { useComponentTranslations } from "@/composables/useComponentTranslations";

const props = defineProps({
	recordName: { type: String, required: true },
	items: { type: Array, required: true },
	displayField: { type: String, default: "name" },
	displayCount: { type: Number, default: 100 },
	linkTo: { type: String, default: "" },
	customDescription: { type: String, default: "" },
});

const defaultTypeGuess = computed(() => {
	const base = props.displayField.replace(/_name$|Name$/i, "") || "items";
	return base.endsWith("s") ? base : `${base}s`;
});

const linkPath = computed(() => props.linkTo || `/${defaultTypeGuess.value.toLowerCase()}`);

const description = computed(() => {
	if (props.customDescription) return props.customDescription;
	return `Showing ${props.displayCount} ${defaultTypeGuess.value} associated with ${props.recordName}.`;
});

const { t } = useComponentTranslations(RcListPopoverI18N);
</script>

<template>
	<Popover>
		<PopoverTrigger as-child>
			<button type="button" class="mt-1 inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground bg-background border-border">
				...
			</button>
		</PopoverTrigger>

		<PopoverContent class="max-w-max bg-popover text-popover-foreground border border-border shadow-md" style="width: 800px;">
			<div class="space-y-2">
				<p class="mb-2 text-sm text-muted-foreground">{{ description }}</p>
			</div>

			<RcBadge v-for="item in items.slice(0, displayCount)" :key="item[displayField]" variant="outline" class="py-1 mt-1 hover:bg-muted">
				<router-link :to="item.view_url">{{ item[displayField] }}</router-link>
			</RcBadge>

			<span class="text-xs text-muted-foreground" v-if="items.length > displayCount - 1">
				<br />
				{{ t("displayingOnly") }} {{ displayCount }} {{ t("records") }}. {{ t("visitThe") }}
				<router-link :to="linkPath" class="text-blue-500 hover:underline">
					{{ defaultTypeGuess }}
				</router-link>
				{{ t("pageToViewAll") }}.
			</span>
		</PopoverContent>
	</Popover>
</template>
