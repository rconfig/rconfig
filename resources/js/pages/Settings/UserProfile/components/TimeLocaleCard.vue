<script setup>
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { AlertCircle, Clock, FlaskConical, Save, Minimize2 } from "lucide-vue-next";
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Switch } from "@/components/ui/switch";
import { useTimeLocalePreferences } from "./useTimeLocalePreferences";
import { ref, watch, onMounted } from "vue";

const props = defineProps({
	user: {
		type: Object,
		required: true,
	},
	profileUserId: {
		type: String,
		required: true,
	},
});

const { preferences, locales, savePreferences, selectedLocale, setSaving, isSaving } = useTimeLocalePreferences(props.profileUserId, props.user);

// Single compact view preference for all tables
const compactViewTables = ref(false);

// Load compact view preference from localStorage
const loadCompactViewPreference = () => {
	// Check if either table has compact view enabled
	const tableCompactViewStored = localStorage.getItem("TableCompactView");

	// If either is true, set to true
	if (tableCompactViewStored === "true") {
		compactViewTables.value = true;
	}
};

// Watch for changes and save to localStorage for all tables
watch(compactViewTables, (newValue) => {
	localStorage.setItem("TableCompactView", newValue.toString());
	setSaving(true);
	setTimeout(() => setSaving(false), 500);
});

onMounted(() => {
	loadCompactViewPreference();
});
</script>

<template>
	<Card class="bg-transparent border-none">
		<CardHeader class="pb-6">
			<div class="flex items-center justify-between">
				<div class="flex items-center space-x-3">
					<div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
						<Clock class="h-5 w-5 text-blue-600 dark:text-blue-400" />
					</div>
					<div>
						<CardTitle class="text-xl font-semibold text-gray-900 dark:text-white"> Time & Locale {{ selectedLocale }} </CardTitle>
						<CardDescription class="text-gray-500 dark:text-gray-400">
							Customize your regional and time preferences
						</CardDescription>
					</div>
				</div>
				<!-- Current Status Indicator -->
				<div class="flex flex-col items-start space-y-2">
					<div class="flex items-center space-x-2 text-sm" :class="isSaving ? 'text-blue-500 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400'">
						<div class="w-2 h-2 rounded-full" :class="isSaving ? 'bg-blue-500 animate-pulse' : 'bg-gray-400'"></div>
						<span>{{ isSaving ? "Saving changes…" : "Changes auto-saved" }}</span>
					</div>
				</div>
			</div>
		</CardHeader>

		<CardContent class="space-y-8">
			<form class="space-y-8">
				<!-- Language & Timezone Section -->
				<div class="space-y-4">
					<div class="pl-3">
						<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
							<!-- Timezone Settings -->
							<div class="space-y-3">
								<div class="flex items-center space-x-2 border-b border-gray-200 dark:border-gray-700">
									<div class="w-1 h-4 bg-blue-400 rounded-full"></div>
									<h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Timezone</h4>
								</div>
								<div class="pl-3 space-y-2">
									<Select v-model="selectedLocale">
										<SelectTrigger id="timezone">
											<SelectValue placeholder="Select timezone" />
										</SelectTrigger>
										<SelectContent>
											<SelectItem v-for="(locale, key) in locales" :key="key" :value="key">
												{{ locale }}
											</SelectItem>
										</SelectContent>
									</Select>
									<RcBadge v-if="selectedLocale" variant="success" :interactive="false">
										<div class="w-2 h-2 bg-green-500 rounded-full animate-pulse mr-2"></div>
										<span class="text-sm font-medium text-green-700 dark:text-green-300">{{ selectedLocale }}</span>
									</RcBadge>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Format Settings Section -->
				<div class="space-y-4">
					<div class="flex items-center space-x-2 pb-2 border-b border-gray-200 dark:border-gray-700">
						<div class="w-1 h-5 bg-purple-500 rounded-full"></div>
						<h3 class="text-base font-medium text-gray-900 dark:text-white">Display Formats</h3>
					</div>
					<div class="pl-3">
						<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
							<!-- Date Format -->
							<div class="space-y-2">
								<Label for="dateFormat" class="text-sm font-medium text-gray-700 dark:text-gray-300">
									Date Format
								</Label>
								<Select v-model="preferences.dateFormat">
									<SelectTrigger id="dateFormat">
										<SelectValue placeholder="Select date format" />
									</SelectTrigger>
									<SelectContent>
										<SelectItem value="MM/DD/YYYY">
											<div class="flex items-center justify-between w-full">
												<span>MM/DD/YYYY</span>
												<span class="text-xs text-gray-500 ml-4">12/25/2024</span>
											</div>
										</SelectItem>
										<SelectItem value="DD/MM/YYYY">
											<div class="flex items-center justify-between w-full">
												<span>DD/MM/YYYY</span>
												<span class="text-xs text-gray-500 ml-4">25/12/2024</span>
											</div>
										</SelectItem>
										<SelectItem value="YYYY-MM-DD">
											<div class="flex items-center justify-between w-full">
												<span>YYYY-MM-DD</span>
												<span class="text-xs text-gray-500 ml-4">2024-12-25</span>
											</div>
										</SelectItem>
									</SelectContent>
								</Select>
							</div>

							<!-- Time Format -->
							<div class="space-y-2">
								<Label for="timeFormat" class="text-sm font-medium text-gray-700 dark:text-gray-300">
									Time Format
								</Label>
								<Select v-model="preferences.timeFormat">
									<SelectTrigger id="timeFormat">
										<SelectValue placeholder="Select time format" />
									</SelectTrigger>
									<SelectContent>
										<SelectItem value="12">
											<div class="flex items-center justify-between w-full">
												<span>12 Hour (AM/PM)</span>
												<span class="text-xs text-gray-500 ml-4">2:30 PM</span>
											</div>
										</SelectItem>
										<SelectItem value="24">
											<div class="flex items-center justify-between w-full">
												<span>24 Hour</span>
												<span class="text-xs text-gray-500 ml-4">14:30</span>
											</div>
										</SelectItem>
									</SelectContent>
								</Select>
							</div>
						</div>
					</div>
				</div>

				<!-- Info Alert -->
				<div class="pt-4">
					<Alert class="bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800">
						<AlertCircle class="h-4 w-4 text-blue-600 dark:text-blue-400" />
						<AlertTitle class="text-blue-800 dark:text-blue-200">Display Preferences</AlertTitle>
						<AlertDescription class="text-blue-700 dark:text-blue-300">
							Your display settings affect how dates, times, and table layouts are shown throughout the application. Changes are automatically saved and will apply immediately to all tables.
						</AlertDescription>
					</Alert>
				</div>
			</form>
		</CardContent>

		<CardFooter class="flex justify-between items-center border-t border-gray-200 dark:border-gray-700 pt-6"> </CardFooter>
	</Card>
</template>
