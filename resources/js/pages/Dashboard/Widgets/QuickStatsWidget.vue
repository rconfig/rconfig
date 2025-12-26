<script setup>
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { GripVertical, TrendingUp, TrendingDown, BarChart3 } from "lucide-vue-next";
import { computed } from "vue";

const props = defineProps({
	configinfo: {
		type: Object,
		required: true,
	},
	editMode: {
		type: Boolean,
		default: false,
	},
});

const stats = computed(() => {
	const data = props.configinfo?.data;
	if (!data) return [];

	const deviceUpPercentage = data.deviceCount > 0 
		? ((data.deviceCount - data.deviceDownCount) / data.deviceCount * 100).toFixed(1)
		: 0;

	const configSuccessRate = data.configTotalCount > 0
		? ((data.configTotalCount - (data.failedConfigCount || 0)) / data.configTotalCount * 100).toFixed(1)
		: 0;

	return [
		{
			label: "Device Uptime",
			value: `${deviceUpPercentage}%`,
			trend: deviceUpPercentage >= 90 ? 'up' : 'down',
			color: deviceUpPercentage >= 90 ? 'green' : 'red',
		},
		{
			label: "Config Success",
			value: `${configSuccessRate}%`,
			trend: configSuccessRate >= 90 ? 'up' : 'down',
			color: configSuccessRate >= 90 ? 'green' : 'amber',
		},
		{
			label: "Total Files",
			value: data.configFileTotalCount || 0,
			trend: 'neutral',
			color: 'blue',
		},
	];
});
</script>

<template>
	<Card class="border-0 shadow-md rounded-2xl bg-card text-card-foreground p-4 transition-all duration-200 hover:shadow-lg relative">
		<div v-if="editMode" class="absolute top-2 right-2 cursor-move opacity-50">
			<GripVertical class="w-4 h-4 text-muted-foreground" />
		</div>

		<div class="flex items-center justify-between mb-3">
			<h3 class="text-base font-semibold flex items-center gap-2">
				<BarChart3 class="w-4" />
				Quick Stats
			</h3>
		</div>

		<div class="space-y-2 text-sm">
			<div
				v-for="(stat, index) in stats"
				:key="index"
				class="flex items-center justify-between py-1.5"
			>
				<div class="flex items-center gap-2">
					<TrendingUp v-if="stat.trend === 'up'" :class="[
						'w-4 h-4',
						stat.color === 'green' ? 'text-green-500' : 'text-amber-500'
					]" />
					<TrendingDown v-else-if="stat.trend === 'down'" class="w-4 h-4 text-red-500" />
					<BarChart3 v-else class="w-4 h-4 text-muted-foreground" />
					<span class="text-muted-foreground">{{ stat.label }}</span>
				</div>
				<span class="font-medium">{{ stat.value }}</span>
			</div>
		</div>
	</Card>
</template>