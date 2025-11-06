<script setup>
import { ref, onMounted } from "vue";
import DeviceConfigsViewPanelAll from "@/pages/Inventory/Devices/DeviceView/DeviceConfigsViewPanelAll.vue";
import DeviceConfigsViewPanelLatest from "@/pages/Inventory/Devices/DeviceView/DeviceConfigsViewPanelLatest.vue";

const props = defineProps({
	deviceId: Number,
});

// State to track which view is active
const activeView = ref("latest");
const isReady = ref(false);

// Function to restore view selection from localStorage
const restoreActiveView = () => {
	const saved = localStorage.getItem("DeviceConfigsActiveView");
	const validViews = ["latest", "all"];

	if (saved && validViews.includes(saved)) {
		activeView.value = saved;
	} else {
		activeView.value = "latest"; // default fallback
	}
	isReady.value = true;
};

// Function to toggle views
const toggleView = (view) => {
	const validViews = ["latest", "all"];
	const selectedView = validViews.includes(view) ? view : "latest";

	// Update the reactive value
	activeView.value = selectedView;

	// Store in localStorage
	localStorage.setItem("DeviceConfigsActiveView", selectedView);
};

// Restore view selection on component mount
onMounted(() => {
	restoreActiveView();
});
</script>

<template>
	<div class="p-2">
		<div v-if="!isReady" class="flex justify-center items-center h-32">
			<!-- Optional: Add a loading spinner here -->
			<div class="text-gray-500">Loading...</div>
		</div>
		<transition v-else name="fade" mode="out-in">
			<component :is="activeView === 'latest' ? DeviceConfigsViewPanelLatest : DeviceConfigsViewPanelAll" :deviceId="deviceId" @toggle-view="toggleView" />
		</transition>
	</div>
</template>
