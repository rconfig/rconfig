<script setup>
import { computed, ref } from "vue";
import { ChevronRight, Copy, Check, ExternalLink } from "lucide-vue-next";
import { formatEvidence, severityClasses, statusClasses, statusLabel } from "./securityReview";

const props = defineProps({
	row: { type: Object, required: true },
});

const open = ref(false);
const copied = ref(false);

const needsAction = computed(() => ["fail", "warn"].includes(props.row.status));
const evidenceEntries = computed(() => Object.entries(props.row.evidence ?? {}).filter(([, value]) => value !== null && value !== ""));

async function copyFix() {
	try {
		await navigator.clipboard.writeText(props.row.fix ?? "");
		copied.value = true;
		setTimeout(() => (copied.value = false), 2000);
	} catch {
		copied.value = false;
	}
}
</script>

<template>
	<div
		class="border-b last:border-b-0 border-border"
		:data-testid="`finding-${row.id}`"
	>
		<button
			type="button"
			class="w-full flex items-start gap-3 px-4 py-3 text-left hover:bg-muted/50 focus-visible:outline-2 focus-visible:outline-blue-500"
			:aria-expanded="open"
			@click="open = !open"
		>
			<ChevronRight
				class="h-4 w-4 mt-0.5 shrink-0 text-muted-foreground transition-transform"
				:class="{ 'rotate-90': open }"
			/>
			<span
				class="inline-flex shrink-0 items-center rounded-full border px-2 py-0.5 text-xs font-medium w-20 justify-center"
				:class="statusClasses(row.status)"
				data-testid="finding-status"
			>{{ statusLabel(row.status) }}</span>
			<span class="flex-1 min-w-0">
				<span class="flex flex-wrap items-baseline gap-x-2">
					<span class="font-medium text-sm">{{ row.title }}</span>
					<code class="text-xs text-muted-foreground">{{ row.id }}</code>
				</span>
				<span class="block text-xs text-muted-foreground mt-0.5 break-words">{{ row.message }}</span>
			</span>
			<span
				class="hidden sm:inline capitalize font-medium text-xs w-16 text-right shrink-0"
				:class="severityClasses(row.severity)"
			>{{ row.severity }}</span>
		</button>

		<div
			v-if="open"
			class="px-4 pb-4 pl-11 space-y-3 text-sm"
			data-testid="finding-details"
		>
			<div v-if="row.rationale">
				<p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
					Why it matters
				</p>
				<p class="text-muted-foreground mt-1">
					{{ row.rationale }}
				</p>
			</div>

			<div
				v-if="row.fix && needsAction"
				class="space-y-1"
			>
				<p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
					How to fix
				</p>
				<div class="flex items-start gap-2">
					<code
						class="flex-1 block whitespace-pre-wrap break-words rounded-md bg-muted px-3 py-2 text-xs"
						data-testid="finding-fix"
					>{{ row.fix }}</code>
					<button
						type="button"
						class="p-2 rounded-md hover:bg-muted text-muted-foreground"
						:aria-label="copied ? 'Copied' : 'Copy the fix'"
						data-testid="finding-copy"
						@click="copyFix"
					>
						<Check
							v-if="copied"
							class="h-4 w-4 text-emerald-600"
						/>
						<Copy
							v-else
							class="h-4 w-4"
						/>
					</button>
				</div>
			</div>

			<dl
				v-if="evidenceEntries.length"
				class="grid grid-cols-1 sm:grid-cols-[max-content_1fr] gap-x-4 gap-y-1 text-xs"
				data-testid="finding-evidence"
			>
				<template
					v-for="[key, value] in evidenceEntries"
					:key="key"
				>
					<dt class="text-muted-foreground">
						{{ key.replaceAll('_', ' ') }}
					</dt>
					<dd class="break-words font-mono">
						{{ formatEvidence(value) }}
					</dd>
				</template>
			</dl>

			<div class="flex flex-wrap items-center gap-1.5">
				<span
					v-for="reference in row.references ?? []"
					:key="reference"
					class="inline-flex items-center rounded border border-border px-1.5 py-0.5 text-[11px] text-muted-foreground"
				>{{ reference }}</span>
				<a
					v-if="row.docs_url"
					:href="row.docs_url"
					target="_blank"
					rel="noopener noreferrer"
					class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 ml-auto"
					data-testid="finding-docs"
				>
					Docs <ExternalLink class="h-3 w-3" />
				</a>
			</div>
		</div>
	</div>
</template>
