<script setup>
import { inject } from "vue";
import UserActivityLogsTableHoverCardI18N from "@/i18n/pages/Settings/UserActivityLogs/UserActivityLogsTableHoverCard.i18n.js";
import { Calendar } from "lucide-vue-next";
import { HoverCard, HoverCardContent, HoverCardTrigger } from "@/components/ui/hover-card";
import { useCopy } from "@/composables/useCopy";
import { useComponentTranslations } from "@/composables/useComponentTranslations";

const { copyItem, activeCopyIcon } = useCopy();
const { t } = useComponentTranslations(UserActivityLogsTableHoverCardI18N);
const formatters = inject("formatters");

const props = defineProps({
	log: {
		type: Object,
		required: true,
	},
});
</script>

<template>
	<HoverCard>
		<HoverCardTrigger as-child>
			<slot />
		</HoverCardTrigger>
		<HoverCardContent class="w-full max-h-[85vh]" align="start">
			<div class="flex justify-between space-x-4 max-h-[80vh] overflow-y-auto">
				<div class="absolute top-4 left-2">
					<slot name="leftIcon"></slot>
				</div>
				<div class="space-y-1">
					<h4 class="text-sm font-semibold">{{ log.subject }}</h4>
					<p class="text-sm">{{ log.description }}</p>
					<p class="pt-4 text-sm">{{ t("rawData") }}:</p>
					<div class="text-sm">
						<pre v-highlightjs><code class="javascript">{{ log }}</code></pre>
					</div>
					<div class="flex pt-2 items-between">
						<Calendar class="w-4 h-4 mr-2 opacity-70" />
						<span class="text-xs text-muted-foreground">{{ t("eventDate") }}: {{ formatters.formatTime(log.created_at) }}</span>
						<Button class="h-6 p-1 ml-auto" variant="ghost" :title="t('copyRawData')" @click="copyItem(log.id, log)">
							<RcIcon name="copy-transition" :isActive="activeCopyIcon[log.id]" :size="16" />
						</Button>
					</div>
				</div>
			</div>
		</HoverCardContent>
	</HoverCard>
</template>
