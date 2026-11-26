<script setup>
import { HoverCard, HoverCardContent, HoverCardTrigger } from "@/components/ui/hover-card";
import Table from "@/components/ui/table/Table.vue";
import { useCopy } from "@/composables/useCopy";
import { Calendar } from "lucide-vue-next";
import { computed, inject } from "vue";

const props = defineProps({
	run: {
		type: Object,
		required: true,
	},
});

const { copyItem, activeCopyIcon } = useCopy();
const formatters = inject("formatters");

const formattedRun = computed(() => {
	try {
		return JSON.stringify(props.run, null, 2);
	} catch (e) {
		return String(props.run);
	}
});
</script>

<template>
	<HoverCard>
		<HoverCardTrigger as-child>
			<slot />
		</HoverCardTrigger>
		<HoverCardContent class="w-full max-w-3xl" align="start">
			<div class="flex space-x-4">
				<div class="space-y-2 w-full">
					<div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
						<div><span class="font-semibold">Source:</span> {{ run.source || "-" }}</div>
						<div><span class="font-semibold">Message:</span> {{ run.message || "-" }}</div>
						<div>
							<span class="font-semibold">Scope:</span>
							{{
								(() => {
									const scope = run.scope;
									try {
										const decoded = typeof scope === "string" ? JSON.parse(scope) : scope;
										return decoded && decoded.table ? decoded.table : formatScope(scope);
									} catch {
										return formatScope(scope);
									}
								})()
							}}
						</div>
						<div>
							<span class="font-semibold">Dry Run:</span> <RcBadge :variant="run.dry_run ? 'secondary' : 'primary'" :interactive="false">{{ run.dry_run ? "Dry run" : "Live run" }}</RcBadge>
						</div>
						<span class="font-semibold">Summary:</span>
						<div v-if="run.summary">
							<template v-if="typeof run.summary === 'string'">
								<template
									v-if="
										(() => {
											try {
												JSON.parse(run.summary);
												return true;
											} catch {
												return false;
											}
										})()
									"
								>
									<template v-for="(value, key) in JSON.parse(run.summary).metrics" :key="key">
										<div v-if="Array.isArray(value)">
											<span class="font-semibold mr-2 rc-text-xs-muted">{{ key }}:</span>
											<span>{{ value.length ? value.join(", ") : "None" }}</span>
										</div>
										<div v-else>
											<span class="font-semibold mr-2 rc-text-xs-muted">{{ key }}:</span>
											<span>{{ value }}</span>
										</div>
									</template>
								</template>
								<template v-else>
									{{ run.summary }}
								</template>
							</template>
							<template v-else>
								<template v-for="(value, key) in run.summary.metrics" :key="key">
									<div v-if="Array.isArray(value)">
										<span class="font-semibold mr-2 rc-text-xs-muted">{{ key }}:</span>
										<span>{{ value.length ? value.join(", ") : "None" }}</span>
									</div>
									<div v-else>
										<span class="font-semibold mr-2 rc-text-xs-muted">{{ key }}:</span>
										<span>{{ value }}</span>
									</div>
								</template>
							</template>
						</div>
						<div v-else>-</div>
					</div>
					<p class="pt-2 text-sm font-semibold">Raw data</p>
					<div class="text-sm overflow-auto max-h-96 border rounded-md">
						<pre class="p-3 bg-rcgray-900 text-rcgray-100" v-highlightjs><code class="json">{{ formattedRun }}</code></pre>
					</div>
					<div class="flex items-center pt-2">
						<Calendar class="w-4 h-4 mr-2 opacity-70" />
						<span class="text-xs text-muted-foreground">Started {{ run.started_at ? formatters.formatTime(run.started_at) : "-" }}</span>
						<span class="text-xs text-muted-foreground ml-4">Finished {{ run.finished_at ? formatters.formatTime(run.finished_at) : "-" }}</span>
						<Button class="h-6 p-1 ml-auto" variant="ghost" title="Copy raw data" @click="copyItem(run.id, run)">
							<RcIcon name="copy-transition" :isActive="activeCopyIcon[run.id]" :size="16" />
						</Button>
					</div>
				</div>
			</div>
		</HoverCardContent>
	</HoverCard>
</template>
