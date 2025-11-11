<script setup>
import { ref, inject } from "vue";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { useCopy } from "@/composables/useCopy";
import RcToolTip from "@/pages/Shared/Tooltips/RcToolTip.vue";
import { Lightbulb } from "lucide-vue-next";

const formatters = inject("formatters");
const { copyItem, activeCopyIcon } = useCopy();

defineProps({
	isLoading: Boolean,
	configData: Object,
});

function copyPath(path, key = "getPath") {
	copyItem(key, path);
}
</script>

<template>
	<div>
		<Card class="overflow-hidden">
			<CardHeader class="flex flex-row items-start p-4 bg-muted/50">
				<div class="grid gap-0.5 w-full">
					<CardTitle class="gap-2 text-lg group">
						<div class="flex justify-between">
							<div>Config Details</div>
							<div class="flex items-center gap-2 text-xs text-muted-foreground" v-if="!isLoading && configData">
								Download Status:
								<RcIcon name="status-green" class="mr-2" v-if="configData.download_status === 1" />
								<RcIcon name="status-red" class="mr-2" v-else-if="configData.download_status === 0" />
								<RcIcon name="status-yellow" class="mr-2" v-else-if="configData.download_status === 2" />
							</div>
						</div>
					</CardTitle>
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
					<div class="grid gap-3" v-if="!isLoading && configData">
						<dl class="grid gap-3">
							<div class="flex items-center justify-between">
								<dt class="text-muted-foreground">Device ID</dt>
								<dd class="flex items-center gap-2">{{ configData.id }}</dd>
							</div>
							<div class="flex items-center justify-between">
								<dt class="text-muted-foreground">Device Name</dt>
								<dd class="flex items-center gap-2">
									<span v-if="configData.device_name && configData.device_name.length > 15">{{ configData.device_name.substring(0, 15) + "..." }}</span>
									<RcToolTip :delayDuration="100" :content="configData.device_name" :side="'bottom'" v-if="configData.device_name && configData.device_name.length > 15">
										<template #trigger>
											<Lightbulb size="16" class="text-rcgray-500" />
										</template>
									</RcToolTip>
									<span v-else>{{ configData.device_name }}</span>
								</dd>
							</div>
							<div class="flex items-center justify-between">
								<dt class="text-muted-foreground">Command Group</dt>
								<dd class="flex items-center gap-2">{{ configData.device_category }}</dd>
							</div>
							<div class="flex items-center justify-between">
								<dt class="text-muted-foreground">Command</dt>
								<dd class="flex items-center gap-2">
									<span v-if="configData.command && configData.command.length > 15">{{ configData.command.substring(0, 15) + "..." }}</span>
									<RcToolTip :delayDuration="100" :content="configData.command" :side="'bottom'" v-if="configData.command && configData.command.length > 15">
										<template #trigger>
											<Lightbulb size="16" class="text-rcgray-500" />
										</template>
									</RcToolTip>
									<span v-else>{{ configData.command }}</span>
								</dd>
							</div>
							<div class="flex items-center justify-between">
								<dt class="text-muted-foreground">Filename</dt>
								<dd class="flex items-center gap-2">
									<span v-if="configData.config_filename && configData.config_filename.length > 15">{{ configData.config_filename.substring(0, 15) + "..." }}</span>
									<RcToolTip :delayDuration="100" :content="configData.config_filename" :side="'bottom'" v-if="configData.config_filename && configData.config_filename.length > 15">
										<template #trigger>
											<Lightbulb size="16" class="text-rcgray-500" />
										</template>
									</RcToolTip>
									<span v-else>{{ configData.config_filename }}</span>
								</dd>
							</div>
							<div class="flex items-center justify-between">
								<dt class="text-muted-foreground">File Size</dt>
								<dd class="flex items-center gap-2">{{ formatters.formatFileSize(configData.config_filesize) }}</dd>
							</div>
							<div class="flex items-center justify-between">
								<dt class="text-muted-foreground">Duration</dt>
								<dd class="flex items-center gap-2">{{ formatters.formatDuration(configData.start_time, configData.end_time) }}</dd>
							</div>
							<div class="flex items-center justify-between">
								<dt class="text-muted-foreground">Created</dt>
								<dd class="flex items-center gap-2">{{ formatters.formatTime(configData.created_at) }}</dd>
							</div>
						</dl>
					</div>
				</transition>
			</CardContent>
		</Card>
	</div>
</template>
