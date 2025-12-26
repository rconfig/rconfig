<script setup>
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { GripVertical, Clock } from "lucide-vue-next";
import { useRouter } from "vue-router";
import { inject } from "vue";

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

const router = useRouter();
const formatters = inject("formatters");

const navigateToConfigs = () => {
	if (!props.editMode) {
		router.push("/configs");
	}
};

const getStatusIcon = (status) => {
	return status === 1 ? '✓' : '✗';
};
</script>

<template>
	<Card class="border-0 shadow-md rounded-2xl bg-card text-card-foreground p-4 transition-all duration-200 hover:shadow-lg relative">
		<div v-if="editMode" class="absolute top-2 right-2 cursor-move opacity-50">
			<GripVertical class="w-4 h-4 text-muted-foreground" />
		</div>

		<div class="flex items-center justify-between mb-3">
			<h3 class="text-base font-semibold flex items-center gap-2">
				<RcIcon name="config-tools" class="w-4" />
				Latest Configs
			</h3>
		</div>

		<div class="space-y-3 text-sm">
			<div v-if="configinfo.data?.lastConfig">
				<div class="flex items-center justify-between py-1.5">
					<span class="text-muted-foreground">Last Download</span>
					<div class="flex items-center gap-2">
						<span class="font-medium">{{ getStatusIcon(configinfo.data.lastConfig.download_status) }}</span>
						<div :class="[
							'w-2 h-2 rounded-full',
							configinfo.data.lastConfig.download_status === 1 ? 'bg-green-500' : 'bg-red-500'
						]"></div>
					</div>
				</div>

				<div class="flex items-center justify-between py-1.5">
					<span class="text-muted-foreground flex items-center gap-1">
						<Clock class="w-3 h-3" />
						Time
					</span>
					<span class="font-medium text-xs">{{ formatters.formatTime(configinfo.data.lastConfig.created_at) }}</span>
				</div>

				<div class="border-t border-muted/50 pt-2 mt-2">
					<div class="grid grid-cols-2 gap-2">
						<div class="flex justify-between items-center py-1">
							<span class="text-muted-foreground">Total</span>
							<span class="font-medium">{{ configinfo.data.configTotalCount }}</span>
						</div>
						<div class="flex justify-between items-center py-1">
							<span class="text-muted-foreground">Failed</span>
							<span class="font-medium text-red-500">{{ configinfo.data.failedConfigCount || 0 }}</span>
						</div>
					</div>
				</div>
			</div>

			<div v-else class="text-center py-4 text-muted-foreground">
				No config data available
			</div>

			<Button 
				type="button"
				class="w-full px-2 py-1 mt-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
				size="sm" 
				@click="navigateToConfigs"
				:disabled="editMode"
				variant="primary"
			>
				View All Configs
			</Button>
		</div>
	</Card>
</template>