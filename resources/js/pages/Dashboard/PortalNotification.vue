<script setup>
import { computed, inject } from "vue";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { Bell, ExternalLink, X } from "lucide-vue-next";
import { useLicenseInfoStore } from "@/stores/useLicenseInfoStore";

const formatters = inject("formatters");

// Use the license store for portal notices
const licenseStore = useLicenseInfoStore();

// Get the current notice
const currentNotice = computed(() => licenseStore.latestNotice?.[0] || null);

// Check if we should show the notification
const shouldShow = computed(() => {
	return licenseStore.hasActiveNotice && currentNotice.value && !licenseStore.isLoading.portalNotices;
});

// Methods
const dismissNotification = () => {
	if (currentNotice.value) {
		licenseStore.dismissNotice(currentNotice.value.id);
	}
};

const getBadgeClasses = () => {
	const type = currentNotice.value?.type || "info";

	switch (type) {
		case "warning":
			return "bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/50";
		case "success":
			return "bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 border-green-200 dark:border-green-800/50";
		case "error":
			return "bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 border-red-200 dark:border-red-800/50";
		default:
			// info
			return "bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800/50";
	}
};

const getBellIconClass = () => {
	const type = currentNotice.value?.type || "info";

	return {
		"text-amber-500": type === "warning",
		"text-green-500": type === "success",
		"text-red-500": type === "error",
		"text-blue-500": type === "info",
	};
};
</script>

<template>
	<div v-if="shouldShow" class="relative">
		<Popover>
			<PopoverTrigger asChild>
				<button class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-md border transition-all duration-200 cursor-pointer animate-pulse hover:animate-none" :class="getBadgeClasses()">
					<Bell size="12" class="animate-bounce" />
					<span>Notice</span>
				</button>
			</PopoverTrigger>

			<PopoverContent class="w-80 p-4 shadow-lg border bg-card/95 backdrop-blur-xl pb-0" align="start">
				<div class="space-y-3">
					<!-- Header -->
					<div class="flex items-center justify-between border-b pb-2">
						<div class="flex items-center gap-2">
							<Bell size="16" :class="getBellIconClass()" />
							<span class="text-sm font-semibold text-foreground">
								{{ currentNotice.title }}
							</span>
						</div>
						<Button v-if="currentNotice.dismissible" variant="ghost" size="sm" class="h-6 w-6 p-0 hover:bg-muted/50 transition-colors" @click="dismissNotification">
							<X size="12" />
						</Button>
					</div>

					<!-- Content -->
					<div class="text-sm text-muted-foreground leading-relaxed">
						{{ currentNotice.message }}
					</div>

					<!-- Action Button -->
					<div v-if="currentNotice.action" class="flex justify-end pb-2 rc-text-sm-muted">Date: {{ formatters.formatTime(currentNotice.created_at, true) }}</div>
				</div>

				<!-- Footer with portal link -->
				<div class="border-t bg-muted/20 px-4 py-3">
					<div class="flex items-center justify-center text-xs">
						<a :href="`${$rconfigPortalUrl || 'https://portal.rconfig.com'}/notifications`" target="_blank" class="text-muted-foreground hover:text-foreground transition-colors flex items-center gap-1">
							<ExternalLink size="12" />
							Visit rConfig.com Portal
						</a>
					</div>
				</div>
			</PopoverContent>
		</Popover>
	</div>
</template>

<style scoped>
/* Subtle animation for the badge */
@keyframes gentle-pulse {
	0%,
	100% {
		opacity: 1;
	}
	50% {
		opacity: 0.8;
	}
}

.animate-pulse {
	animation: gentle-pulse 2s ease-in-out infinite;
}

/* Gentle bounce for the bell icon */
@keyframes gentle-bounce {
	0%,
	100% {
		transform: translateY(0);
	}
	50% {
		transform: translateY(-1px);
	}
}

.animate-bounce {
	animation: gentle-bounce 2s ease-in-out infinite;
}
</style>
