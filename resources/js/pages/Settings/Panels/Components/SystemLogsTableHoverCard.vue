<script setup>
import { useCopy } from "@/composables/useCopy";
import { Calendar } from "lucide-vue-next";
import { HoverCard, HoverCardContent, HoverCardTrigger } from "@/components/ui/hover-card";
import { inject } from "vue";

const { copyItem, activeCopyIcon } = useCopy();
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
		<HoverCardContent class="w-full" align="start">
			<div class="flex justify-between space-x-4">
				<div class="absolute top-4 left-2">
					<slot name="leftIcon"></slot>
				</div>
				<div class="space-y-1">
					<h4 class="text-sm font-semibold">{{ log.log_name.charAt(0).toUpperCase() + log.log_name.slice(1) }}</h4>
					<p class="text-sm">{{ log.description }}</p>
					<p class="pt-4 text-sm">Raw Data</p>
					<div class="text-sm">
						<pre v-highlightjs><code class="javascript">{{ log }}</code></pre>
					</div>
					<div class="flex pt-2 items-between">
						<Calendar class="w-4 h-4 mr-2 opacity-70" />
						<span class="text-xs text-muted-foreground">Event Date {{ formatters.formatTime(log.created_at) }}</span>
						<Button class="h-6 p-1 ml-auto" variant="ghost" :title="'Copy Raw Data'" @click="copyItem(log.id, log)">
							<RcIcon name="copy-transition" :isActive="activeCopyIcon[log.id]" :size="16" />
						</Button>
					</div>
				</div>
			</div>
		</HoverCardContent>
	</HoverCard>
</template>
