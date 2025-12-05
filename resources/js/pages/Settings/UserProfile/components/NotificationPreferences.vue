<script setup>
import AlertInfo from "@/pages/Shared/Alerts/AlertInfo.vue";
import AlertWarning from "@/pages/Shared/Alerts/AlertWarning.vue";
import { Bell, Loader2 } from "lucide-vue-next";
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from "@/components/ui/card";
import { Checkbox } from "@/components/ui/checkbox";
import { ref, onMounted, computed } from "vue";
import { useNotificationStore } from "@/stores/useNotificationStore";

const props = defineProps({
	profileUserId: {
		type: [String, Number],
		required: true,
	},
});

const notificationStore = useNotificationStore();

// Reactive state
const loading = ref(false);
const showInfoAlert = ref(localStorage.getItem("notificationInfoAlertClosed") !== "true");

// Lifecycle
onMounted(async () => {
	try {
		await notificationStore.initialize();
	} catch (error) {
		toastError("Load Failed", "Failed to load notification preferences");
	}
});

const isSaving = computed(() => {
	return Object.values(notificationStore.updating).some((val) => val === true);
});

function handleAlertClosed() {
	localStorage.setItem("notificationInfoAlertClosed", "true");
	showInfoAlert.value = false;
}
</script>

<template>
	<Card class="bg-transparent border-none">
		<CardHeader>
			<CardTitle class="flex items-center justify-between">
				<div class="flex items-center">
					<div class="p-2 bg-amber-100 dark:bg-amber-900 rounded-lg mr-2">
						<Bell class="h-5 w-5 text-amber-600 dark:text-amber-400" />
					</div>
					Notification Preferences
					<div class="text-sm text-muted-foreground font-normal ml-2">({{ notificationStore.enabledNotifications }} / {{ notificationStore.totalNotificationTypes * 3 }} enabled)</div>
				</div>

				<div class="flex flex-col items-start space-y-2">
					<div class="flex items-center space-x-2 text-sm" :class="isSaving ? 'text-blue-500 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400'">
						<div class="w-2 h-2 rounded-full" :class="isSaving ? 'bg-blue-500 animate-pulse' : 'bg-gray-400'"></div>
						<span>{{ isSaving ? "Saving changes…" : "Changes auto-saved" }}</span>
					</div>
				</div>
			</CardTitle>
			<CardDescription>Manage your notification preferences and settings.</CardDescription>
        </CardHeader>

		<CardContent>
			<div v-if="loading" class="flex items-center justify-center py-8">
				<Loader2 class="h-6 w-6 animate-spin mr-2" />
				Loading preferences...
			</div>

			<div v-else class="space-y-6">
				<!-- Channel Legend -->
				<div class="bg-muted/50 rounded-lg p-4">
					<h4 class="font-medium mb-3 text-sm">Notification Channels</h4>
					<div class="grid grid-cols-1 md:grid-cols-3 gap-3">
						<div v-for="channel in notificationStore.channels" :key="channel.key" class="flex items-center space-x-2">
							<component :is="channel.icon" class="h-4 w-4" :class="`text-${channel.color}-600`" />
							<div>
								<span class="text-sm font-medium flex items-center">
									<RcIcon name="Database" v-if="channel.label === 'Database'" class="mr-2" />
									<RcIcon name="Email" v-if="channel.label === 'Email'" class="mr-2" />
									{{ channel.label }}
								</span>
								<p class="text-xs text-muted-foreground">{{ channel.description }}</p>
							</div>
						</div>
					</div>
				</div>

				<!-- Notification Preferences Table -->
				<div class="rounded-lg border border-border bg-card overflow-hidden">
					<!-- Table Header -->
					<div class="border-b border-border bg-muted/50 px-6 py-4">
						<div class="grid gap-4 items-center" style="grid-template-columns: 1fr auto auto auto;">
							<div class="flex items-center gap-2">
								<Bell class="h-4 w-4 text-muted-foreground" />
								<span class="text-sm font-medium">Notify me about</span>
							</div>
							<div class="flex items-center justify-center gap-2 min-w-[80px]">
								<RcIcon name="Database" class="mr-2" />
								<span class="text-sm font-medium">InApp</span>
							</div>
							<div class="flex items-center justify-center gap-2 min-w-[80px]">
								<RcIcon name="Email" class="mr-2" />
								<span class="text-sm font-medium">Email</span>
							</div>
						</div>
					</div>

					<!-- Table Body -->
					<div class="divide-y divide-border">
						<template v-for="category in notificationStore.notificationCategories" :key="category.key">
							<div
								v-for="(type, index) in category.types"
								:key="type.key"
								class="px-6 py-4 hover:bg-muted/30 transition-colors"
								:class="{
									'border-t border-muted': index === 0 && category !== notificationStore.notificationCategories[0],
								}"
							>
								<div class="grid gap-4 items-center" style="grid-template-columns: 1fr auto auto auto;">
									<!-- Notification Info -->
									<div class="min-w-0">
										<div class="space-y-1">
											<h4 class="text-sm font-medium leading-none">
												{{ type.label }}
											</h4>
											<p class="text-xs text-muted-foreground leading-tight">
												{{ type.description }}
											</p>
										</div>
									</div>

									<!-- Database Checkbox -->
									<div class="flex justify-center min-w-[80px]">
										<div class="flex items-center">
											<Loader2 v-if="notificationStore.isUpdating(type.key, 'db')" class="h-4 w-4 animate-spin text-muted-foreground" />
											<Checkbox v-else :checked="notificationStore.getPreference(type.key, 'db')" @update:checked="notificationStore.updatePreference(type.key, 'db', $event)" />
										</div>
									</div>

									<!-- Email Checkbox -->
									<div class="flex justify-center min-w-[80px]">
										<div class="flex items-center">
											<Loader2 v-if="notificationStore.isUpdating(type.key, 'mail')" class="h-4 w-4 animate-spin text-muted-foreground" />
											<Checkbox v-else :checked="notificationStore.getPreference(type.key, 'mail')" :disabled="!type.channels.includes('mail')" @update:checked="notificationStore.updatePreference(type.key, 'mail', $event)" />
										</div>
									</div>
								</div>
							</div>
						</template>
					</div>
				</div>
			</div>
		</CardContent>
	</Card>
</template>
