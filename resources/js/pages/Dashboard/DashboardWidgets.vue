<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from "vue";
import { X } from "lucide-vue-next";
import { eventBus } from "@/composables/eventBus";
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

const toggleEditMode = () => {
	editMode.value = !editMode.value;
};

const closeEditMode = () => {
	editMode.value = false;
};

const toggleWidget = (widgetId) => {
	const widget = availableWidgets.value.find(w => w.id === widgetId);
	if (widget) {
		widget.enabled = !widget.enabled;
	}
};

// Load configuration on mount and listen for the top nav customize button
onMounted(() => {
	loadWidgetConfig();
	eventBus.on("dashboard-widgets-customize-requested", toggleEditMode);
});

onBeforeUnmount(() => {
	eventBus.off("dashboard-widgets-customize-requested", toggleEditMode);
});
</script>

<template>
	<div class="hidden xl:block xl:col-span-12">
		<!-- Widget Selector Panel - opened from the Dashboard Widgets button in the top nav -->
		<div
			v-if="editMode"
			class="border-0 shadow-md rounded-2xl bg-card text-card-foreground p-4 transition-all duration-200 mb-4"
		>
			<div class="flex items-center justify-between mb-3">
				<h3 class="text-sm font-semibold flex items-center gap-2">
					<RcIcon
						name="dashboard"
						class="w-4 h-4"
					/>
					Available Widgets
					<span class="text-xs font-normal text-muted-foreground">Click to enable or disable</span>
				</h3>
				<button
					class="p-2 rounded-lg hover:bg-muted/50 transition-colors"
					title="Done"
					@click="closeEditMode"
				>
					<X class="w-4 h-4" />
				</button>
			</div>
			<div class="flex flex-wrap gap-2">
				<button
					v-for="widget in availableWidgets"
					:key="widget.id"
					:class="['px-3 py-1.5 rounded-md text-xs font-medium transition-all duration-200 cursor-pointer', widget.enabled ? 'bg-blue-600 text-white hover:bg-blue-700 hover:animate-pulse' : 'bg-muted text-muted-foreground hover:bg-muted/70']"
					@click="toggleWidget(widget.id)"
				>
					<span class="mr-1.5">{{ widget.enabled ? '✓' : '+' }}</span>
					{{ widget.name }}
				</button>
			</div>
		</div>

		<!-- Widget Grid - No background wrapper -->
		<div
			v-if="activeWidgets.length > 0"
			class="grid gap-4 grid-cols-1 lg:grid-cols-2 xl:grid-cols-3"
		>
			<component
				:is="widgetComponents[widget.component]"
				v-for="widget in activeWidgets"
				:key="widget.id"
				:configinfo="configinfo"
				:health-latest="healthLatest"
				:latest-devices="latestDevices"
				:is-loading-latest-devices="isLoadingLatestDevices"
				:edit-mode="editMode"
				class="transition-all duration-200"
			/>
		</div>
	</div>
</template>