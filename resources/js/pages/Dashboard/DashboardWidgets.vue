<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { Button } from "@/components/ui/button";
import { GripVertical, Plus, Settings2, Maximize2, Minimize2 } from "lucide-vue-next";
import LatestDevicesWidget from "@/pages/Dashboard/Widgets/LatestDevicesWidget.vue";
import LatestConfigsWidget from "@/pages/Dashboard/Widgets/LatestConfigsWidget.vue";
import QuickStatsWidget from "@/pages/Dashboard/Widgets/QuickStatsWidget.vue";

const props = defineProps({
	configinfo: {
		type: Object,
		required: true,
	},
	healthLatest: {
		type: Object,
		required: true,
	},
    latestDevices: {
		type: Object,
		required: true,
	},
	isLoadingLatestDevices: {
		type: Boolean,
		required: true,
	},
});

const STORAGE_KEY = 'dashboard_widgets_config';

const isExpanded = ref(true);
const editMode = ref(false);

// Map component names to actual components
const widgetComponents = {
	LatestDevicesWidget,
	LatestConfigsWidget,
	QuickStatsWidget,
};

// Widget configuration - will be loaded from localStorage
const availableWidgets = ref([
	{ 
		id: "latest-devices", 
		name: "Latest Devices", 
		component: "LatestDevicesWidget", 
		enabled: false, 
		order: 1 
	},
	{ 
		id: "latest-configs", 
		name: "Latest Configs", 
		component: "LatestConfigsWidget", 
		enabled: false, 
		order: 2 
	},
	{ 
		id: "quick-stats", 
		name: "Quick Stats", 
		component: "QuickStatsWidget", 
		enabled: false, 
		order: 3 
	},
]);

const activeWidgets = computed(() => {
	return availableWidgets.value
		.filter(w => w.enabled)
		.sort((a, b) => a.order - b.order);
});

// Load widget configuration from localStorage
const loadWidgetConfig = () => {
	try {
		const saved = localStorage.getItem(STORAGE_KEY);
		if (saved) {
			const savedConfig = JSON.parse(saved);
			// Merge saved config with default config
			availableWidgets.value = availableWidgets.value.map(widget => {
				const savedWidget = savedConfig.find(w => w.id === widget.id);
				return savedWidget ? { ...widget, ...savedWidget } : widget;
			});
		}
	} catch (error) {
		console.error('Error loading widget config:', error);
	}
};

// Save widget configuration to localStorage
const saveWidgetConfig = () => {
	try {
		localStorage.setItem(STORAGE_KEY, JSON.stringify(availableWidgets.value));
	} catch (error) {
		console.error('Error saving widget config:', error);
	}
};

// Watch for changes in widget configuration and save
watch(
	availableWidgets,
	() => {
		saveWidgetConfig();
	},
	{ deep: true }
);

const toggleExpanded = () => {
	isExpanded.value = !isExpanded.value;
};

const toggleEditMode = () => {
	editMode.value = !editMode.value;
};

const toggleWidget = (widgetId) => {
	const widget = availableWidgets.value.find(w => w.id === widgetId);
	if (widget) {
		widget.enabled = !widget.enabled;
	}
};

// Load configuration on mount
onMounted(() => {
	loadWidgetConfig();
});
</script>

<template>
	<div :class="[
		'hidden xl:block transition-all duration-300',
		isExpanded ? 'xl:col-span-12' : 'xl:col-span-4'
	]">
		<!-- Header Card with Controls -->
		<div class="border-0 shadow-md rounded-2xl bg-card text-card-foreground p-4 transition-all duration-200 hover:shadow-lg mb-4">
			<div class="flex items-center justify-between mb-3">
				<h3 class="text-lg font-semibold flex items-center gap-2">
					<RcIcon name="dashboard" class="w-5 h-5" />
					Dashboard Widgets
				</h3>
				
				<div class="flex items-center gap-2">
					<button class="p-2 rounded-lg hover:bg-muted/50 transition-colors" @click="toggleEditMode" :title="editMode ? 'Close configuration' : 'Configure Widgets'">
						<Settings2 class="w-4 h-4" />
					</button>
					<button class="p-2 rounded-lg hover:bg-muted/50 transition-colors" @click="toggleExpanded" :title="isExpanded ? 'Collapse' : 'Expand'">
						<Maximize2 v-if="!isExpanded" class="w-4 h-4" />
						<Minimize2 v-else class="w-4 h-4" />
					</button>
				</div>
			</div>

			<!-- Edit Mode Panel -->
			<div v-if="editMode" class="p-3 rounded-lg bg-muted/30 border border-muted">
				<div class="flex items-center justify-between mb-2">
					<span class="text-sm font-medium">Available Widgets</span>
					<span class="text-xs text-muted-foreground">Click to enable/disable</span>
				</div>
				<div class="flex flex-wrap gap-2">
					<button v-for="widget in availableWidgets" :key="widget.id" @click="toggleWidget(widget.id)" :class="['px-3 py-1.5 rounded-md text-xs font-medium transition-all duration-200 cursor-pointer', widget.enabled ? 'bg-blue-600 text-white hover:bg-blue-700 hover:animate-pulse' : 'bg-muted text-muted-foreground hover:bg-muted/70']">
						<span class="mr-1.5">{{ widget.enabled ? '✓' : '+' }}</span>
						{{ widget.name }}
					</button>
				</div>
			</div>
		</div>

		<!-- Widget Grid - No background wrapper -->
		<div v-if="activeWidgets.length > 0" :class="['grid gap-4', isExpanded ? 'grid-cols-1 lg:grid-cols-2 xl:grid-cols-3' : 'grid-cols-1']">
			<component v-for="widget in activeWidgets" :key="widget.id" :is="widgetComponents[widget.component]" :configinfo="configinfo" :healthLatest="healthLatest" :latestDevices="latestDevices" :isLoadingLatestDevices="isLoadingLatestDevices" :editMode="editMode" class="transition-all duration-200"/>
		</div>

		<!-- Empty State -->
		<div v-if="activeWidgets.length === 0" class="border-0 shadow-md rounded-2xl bg-card text-card-foreground p-4 transition-all duration-200 hover:shadow-lg">
			<div class="text-center py-8">
				<div class="inline-flex p-3 rounded-full bg-muted/50 mb-3">
					<Plus class="w-6 h-6 text-muted-foreground" />
				</div>
				<h4 class="text-sm font-semibold mb-1">No Widgets Active</h4>
				<p class="text-xs text-muted-foreground mb-3">
					Click the settings icon to add widgets
				</p>
				<Button 
					class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse text-white"
					size="sm" 
					@click="editMode = true"
					variant="primary"
				>
					<Settings2 class="w-4 h-4 mr-2" />
					Configure Widgets
				</Button>
			</div>
		</div>
	</div>
</template>