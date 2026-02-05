<script setup>
import { Calendar } from "lucide-vue-next";
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from "@/components/ui/card";
import { Skeleton } from "@/components/ui/skeleton"; // Added missing Skeleton import
import { ref, inject } from "vue";

const emit = defineEmits(["refresh"]);
const formatters = inject("formatters");

defineProps({
	isLoading: Boolean,
	data: Object,
});
</script>

<template>
	<div class="p-2 mt-2">
		<Card class="overflow-hidden">
			<CardHeader class="flex flex-row items-start p-4 bg-muted/50">
				<div class="grid gap-0.5">
					<CardTitle class="flex items-center gap-2 text-lg group">Configuration Summary</CardTitle>
				</div>
			</CardHeader>
			<CardContent class="p-4 pt-0 text-sm">
				<div class="space-y-2" v-if="isLoading">
					<Skeleton class="w-1/2 h-4" />
					<Skeleton class="w-full h-4" />
					<Skeleton class="w-full h-4" />
					<Skeleton class="w-full h-4" />
					<Skeleton class="w-full h-4" />
					<Skeleton class="w-full h-4" />
					<Skeleton class="w-full h-4" />
					<Skeleton class="w-full h-4" />
					<Skeleton class="w-full h-4" />
				</div>
				<transition name="fade">
					<div class="grid gap-2 text-sm" v-if="!isLoading && data">
						<dl class="grid gap-2">
							<div class="flex items-center justify-between">
								<dt class="flex items-center gap-1 text-muted-foreground">
									<RcIcon name="status-green" />
									Good Configs
								</dt>
								<!-- if data.config_summary then this is a device else its an API -->
								<dd class="flex items-center gap-2" v-if="data.config_summary">
									{{ data.config_summary?.download_status_1_count ?? "--" }}
								</dd>
								<dd class="flex items-center gap-2" v-else>
									{{ data?.config_good_count ?? "--" }}
								</dd>
							</div>
							<div class="flex items-center justify-between">
								<dt class="flex items-center gap-1 text-muted-foreground">
									<RcIcon name="status-red" />
									Failed Configs
								</dt>
								<dd class="flex items-center gap-2" v-if="data.config_summary">
									{{ data.config_summary?.download_status_0_count ?? "--" }}
								</dd>
								<dd class="flex items-center gap-2" v-else>
									{{ data?.config_bad_count ?? "--" }}
								</dd>
							</div>
							<div class="flex items-center justify-between">
								<dt class="flex items-center gap-1 text-muted-foreground">
									<RcIcon name="status-yellow" />
									Unknown Configs
								</dt>
								<dd class="flex items-center gap-2" v-if="data.config_summary">
									{{ data.config_summary?.download_status_2_count ?? "--" }}
								</dd>
								<dd class="flex items-center gap-2" v-else>
									{{ data?.config_unknown_count ?? "--" }}
								</dd>
							</div>
							<div class="flex items-center justify-between">
								<dt class="flex items-center gap-1 text-muted-foreground">
									<Calendar size="16" class="text-indigo-400" />
									Last Download
								</dt>
								<!-- Different last_config_at for device and api collection -->
								<!-- <dd class="flex items-center gap-2">{{ data.last_config_at ? formatters.formatTime(data.last_config_at) : formatters.formatTime(data.last_config?.updated_at) }}</dd> -->
								<dd class="flex items-center gap-2">
									<span>
										{{ formatters.formatDateOnly(data.last_config_at ?? data.updated_at) }}
									</span>
									<span class="hidden xl:inline">
										{{ formatters.formatTimeOnly(data.last_config_at ?? data.updated_at) }}
									</span>
								</dd>
							</div>
						</dl>
					</div>
				</transition>
			</CardContent>
		</Card>
	</div>
</template>
