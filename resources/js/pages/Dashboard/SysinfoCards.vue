<script setup>
import PhpLogo from "@/pages/Shared/Icon/Icons/PhpLogo.vue";
import { HoverCard, HoverCardContent, HoverCardTrigger } from "@/components/ui/hover-card";
import { ref } from "vue";
import { useCopy } from "@/composables/useCopy";

const activeIcons = ref({});
const emit = defineEmits(["refresh"]);
const { copyItem, activeCopyIcon } = useCopy();

defineProps({
	sysinfo: {
		type: Object,
		required: true,
	},
	isLoadingSysinfo: {
		type: Boolean,
		required: true,
	},
});

function refresh() {
	emit("refresh");
}
</script>

<template>
	<!-- System Info Cards - Updated with 4 items and Laravel in footer -->
	<div class="xl:col-span-6">
		<div class="border-0 shadow-md rounded-2xl bg-card text-card-foreground p-4 transition-all duration-200 hover:shadow-lg h-fit">
			<div class="flex items-center justify-between mb-3">
				<h3 class="text-lg font-semibold flex items-center gap-2">
					<svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
					</svg>
					System Info
				</h3>
				<button class="p-2 rounded-lg hover:bg-muted/50 transition-colors" @click="refresh" :disabled="isLoadingSysinfo">
					<RcIcon name="refresh" size="16" :class="isLoadingSysinfo ? 'animate-spin' : ''" />
				</button>
			</div>

			<!-- Loading Skeleton for System Info -->
			<div v-if="isLoadingSysinfo" class="space-y-3 text-sm min-h-[175px]">
				<div v-for="i in 5" :key="i" class="flex justify-between items-center py-1">
					<div class="flex items-center gap-2">
						<div class="w-4 h-4 bg-muted rounded animate-pulse"></div>
						<div class="h-4 bg-muted rounded w-16 animate-pulse"></div>
					</div>
					<div class="flex items-center gap-2">
						<div class="h-4 bg-muted rounded w-24 animate-pulse"></div>
						<div class="w-6 h-6 bg-muted rounded animate-pulse"></div>
					</div>
				</div>
			</div>

			<!-- Actual System Info Content -->
			<div v-else class="space-y-3 text-sm min-h-[175px]">
				<div class="flex justify-between items-center py-1">
					<div class="flex items-center gap-2">
						<svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
						</svg>
						<span class="text-muted-foreground">OS</span>
					</div>
					<div class="flex items-center gap-2">
						<span class="font-medium">{{ sysinfo.OSVersion || "Loading..." }}</span>
						<Button class="h-6 p-1 ml-auto text-muted-foreground" variant="ghost" title="copy raw data" @click="copyItem('OSVersion', sysinfo.OSVersion)">
							<RcIcon name="copy-transition" :isActive="activeCopyIcon['OSVersion']" :size="16" />
						</Button>
					</div>
				</div>
				<div class="flex justify-between items-center py-1">
					<div class="flex items-center gap-2">
						<svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
						</svg>
						<span class="text-muted-foreground">Server</span>
					</div>
					<div class="flex items-center gap-2">
						<span class="font-medium">{{ sysinfo.ServerName || "Loading..." }}</span>
						<Button class="h-6 p-1 ml-auto text-muted-foreground" variant="ghost" title="copy raw data" @click="copyItem('ServerName', sysinfo.ServerName)">
							<RcIcon name="copy-transition" :isActive="activeCopyIcon['ServerName']" :size="16" />
						</Button>
					</div>
				</div>
				<div class="flex justify-between items-center py-1">
					<div class="flex items-center gap-2">
						<svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
						</svg>
						<span class="text-muted-foreground">Local IP</span>
					</div>
					<div class="flex items-center gap-2">
						<span class="font-medium">{{ sysinfo.localIp || "Loading..." }}</span>
						<Button class="h-6 p-1 ml-auto text-muted-foreground" variant="ghost" title="copy raw data" @click="copyItem('localIp', sysinfo.localIp)">
							<RcIcon name="copy-transition" :isActive="activeCopyIcon['localIp']" :size="16" />
						</Button>
					</div>
				</div>
				<div class="flex justify-between items-center py-1">
					<div class="flex items-center gap-2">
						<PhpLogo size="24" />
						<span class="text-muted-foreground">PHP</span>
					</div>
					<div class="flex items-center gap-2">
						<span class="font-medium">{{ sysinfo.PHPVersion?.split(" / ")[0] || "Loading..." }}</span>
						<Button class="h-6 p-1 ml-auto text-muted-foreground" variant="ghost" title="copy raw data" @click="copyItem('PHPVersion', sysinfo.PHPVersion?.split(' / ')[0])">
							<RcIcon name="copy-transition" :isActive="activeCopyIcon['PHPVersion']" :size="16" />
						</Button>
					</div>
				</div>
				<div class="flex justify-between items-center py-1">
					<div class="flex items-center gap-2">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path fill="none" stroke="#eed49f" stroke-linecap="round" stroke-linejoin="round" d="M8 6.5c3.59 0 6.5-1.4 6.5-2.68S11.59 1.5 8 1.5S1.5 2.54 1.5 3.82S4.41 6.5 8 6.5M14.5 8c0 .83-1.24 1.79-3.25 2.2s-4.49.41-6.5 0S1.5 8.83 1.5 8m13 4.18c0 .83-1.24 1.6-3.25 2c-2.01.42-4.49.42-6.5 0c-2.01-.4-3.25-1.17-3.25-2m0-8.3v8.3m13-8.3v8.3" stroke-width="1" /></svg>
						<span class="text-muted-foreground">MySQL</span>
					</div>
					<div class="flex items-center gap-2">
						<span class="font-medium">{{ sysinfo.MySQLVersion || "Loading..." }}</span>
						<Button class="h-6 p-1 ml-auto text-muted-foreground" variant="ghost" title="copy raw data" @click="copyItem('MySQLVersion', sysinfo.MySQLVersion)">
							<RcIcon name="copy-transition" :isActive="activeCopyIcon['MySQLVersion']" :size="16" />
						</Button>
					</div>
				</div>
			</div>

			<div class="mt-4 pt-3 border-t border-muted/50 text-xs text-muted-foreground flex items-center justify-between">
				<div class="flex items-center gap-2">
					<RcIcon name="laravel" size="14" />
					<span v-if="!isLoadingSysinfo">Laravel: {{ sysinfo.PHPVersion?.split(" / ")[1] || "Loading..." }}</span>
					<div v-else class="h-3 bg-muted rounded w-20 animate-pulse"></div>
				</div>

				<HoverCard>
					<HoverCardTrigger as-child>
						<button class="flex items-center gap-1 px-2 py-1 rounded-md hover:bg-muted/50 transition-colors text-xs text-muted-foreground hover:text-foreground">
							<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
							</svg>
							Details
						</button>
					</HoverCardTrigger>
					<HoverCardContent class="w-80 bg-gradient-to-r from-card/80 via-card/60 to-card/80 backdrop-blur-md shadow-sm" side="top" align="end">
						<div class="space-y-3">
							<div class="flex items-center gap-2 border-b pb-2">
								<svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
								</svg>
								<h4 class="font-semibold text-sm">System Details</h4>
							</div>

							<div class="grid grid-cols-1 gap-2 text-xs">
								<div class="flex justify-between items-center py-1">
									<span class="text-muted-foreground font-medium">OS Version:</span>
									<span class="text-right">{{ sysinfo.OSVersion || "Loading..." }}</span>
								</div>
								<div class="flex justify-between items-center py-1">
									<span class="text-muted-foreground font-medium">Server Name:</span>
									<span class="text-right">{{ sysinfo.ServerName || "Loading..." }}</span>
								</div>
								<div class="flex justify-between items-center py-1">
									<span class="text-muted-foreground font-medium">Local IP:</span>
									<span class="text-right font-mono">{{ sysinfo.localIp || "Loading..." }}</span>
								</div>
								<div class="flex justify-between items-center py-1">
									<span class="text-muted-foreground font-medium">Public IP:</span>
									<span class="text-right font-mono">{{ sysinfo.PublicIP || "Loading..." }}</span>
								</div>
								<div class="flex justify-between items-center py-1">
									<span class="text-muted-foreground font-medium">PHP Version:</span>
									<span class="text-right">{{ sysinfo.PHPVersion?.split(" / ")[0] || "Loading..." }}</span>
								</div>
								<div class="flex justify-between items-center py-1">
									<span class="text-muted-foreground font-medium">Laravel Version:</span>
									<span class="text-right">{{ sysinfo.PHPVersion?.split(" / ")[1] || "Loading..." }}</span>
								</div>
								<div class="flex justify-between items-center py-1">
									<span class="text-muted-foreground font-medium">Redis Version:</span>
									<span class="text-right">{{ sysinfo.RedisVersion || "Loading..." }}</span>
								</div>
								<div class="flex justify-between items-center py-1">
									<span class="text-muted-foreground font-medium">Database:</span>
									<span class="text-right">{{ sysinfo.MySQLVersion || "Loading..." }}</span>
								</div>
								<div class="flex justify-between items-center py-1">
									<span class="text-muted-foreground font-medium">Timezone:</span>
									<span class="text-right">{{ sysinfo.timezone || "Loading..." }}</span>
								</div>
								<div class="flex justify-between items-center py-1">
									<span class="text-muted-foreground font-medium">URL:</span>
									<a :href="sysinfo.url" target="_blank" class="text-right text-blue-500 hover:text-blue-600 underline text-xs break-all">
										{{ sysinfo.url || "Loading..." }}
									</a>
								</div>
							</div>
						</div>
					</HoverCardContent>
				</HoverCard>
			</div>
		</div>
	</div>
	<!-- System Info Cards - Updated with 4 items and Laravel in footer -->
</template>

<style scoped>
/* Add subtle elevation instead of borders */
.shadow-md {
	box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.dark .shadow-md {
	box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.18);
}

@keyframes pulse {
	0%,
	100% {
		opacity: 1;
	}
	50% {
		opacity: 0.5;
	}
}

.animate-pulse {
	animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
