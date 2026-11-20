<script setup>
import { CircleX, TriangleAlert, CircleCheck, CircleMinus } from "lucide-vue-next";

const props = defineProps({
	counts: { type: Object, required: true },
	active: { type: Array, default: () => [] },
});

const emit = defineEmits(["select"]);

const tiles = [
	{ key: "fail", label: "Failed", icon: CircleX, tone: "text-red-600 dark:text-red-400" },
	{ key: "warn", label: "Warnings", icon: TriangleAlert, tone: "text-amber-600 dark:text-amber-400" },
	{ key: "pass", label: "Passed", icon: CircleCheck, tone: "text-emerald-600 dark:text-emerald-400" },
	{ key: "skip", label: "Skipped", icon: CircleMinus, tone: "text-slate-500 dark:text-slate-400" },
];

function isActive(key) {
	return props.active.includes(key);
}
</script>

<template>
	<div
		class="grid grid-cols-2 sm:grid-cols-4 gap-3"
		role="group"
		aria-label="Filter by result"
	>
		<button
			v-for="tile in tiles"
			:key="tile.key"
			type="button"
			class="rounded-lg border p-3 text-left transition-colors hover:bg-muted/60 focus-visible:outline-2 focus-visible:outline-blue-500"
			:class="isActive(tile.key) ? 'border-blue-500 ring-1 ring-blue-500 bg-muted/40' : 'border-border'"
			:aria-pressed="isActive(tile.key)"
			:data-testid="`posture-${tile.key}`"
			@click="emit('select', tile.key)"
		>
			<span class="flex items-center gap-2 text-xs text-muted-foreground">
				<component
					:is="tile.icon"
					class="h-4 w-4"
					:class="tile.tone"
				/>
				{{ tile.label }}
			</span>
			<span class="block mt-1 text-2xl font-semibold tabular-nums">{{ counts[tile.key] ?? 0 }}</span>
		</button>
	</div>
</template>
