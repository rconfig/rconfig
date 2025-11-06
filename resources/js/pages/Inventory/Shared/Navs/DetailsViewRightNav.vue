<script setup>
import NavOpenButton from "@/pages/Shared/Buttons/NavOpenButton.vue"; // Import the NavOpenButton component
import { Megaphone } from "lucide-vue-next";
import { ref, onMounted, nextTick, watch, computed } from "vue";
import { usePanelStore } from "@/stores/panelStore"; // Import the Pinia store
import NavPills from "@/pages/Shared/Buttons/NavPills.vue";

const selectedNav = ref(null);
const selectedButtonRef = ref(null);
const panelStore = usePanelStore(); // Access the panel store
const props = defineProps({
	selectedNav: String,
	hasApiEndpoints: {
		type: Boolean,
		default: false,
	},
	hasXftpFiles: {
		type: Boolean,
		default: false,
	},
	apiEndpointsCount: {
		type: Number,
		default: 0,
	},
});

const emit = defineEmits(["selectMainNavView", "closeNav"]);

onMounted(() => {
	// Check localStorage for saved navigation item
	const savedNav = localStorage.getItem("DeviceDetailsMainNav");
	selectedNav.value = savedNav || props.selectedNav;
});

function selectNav(navItem, buttonElement) {
	selectedNav.value = navItem;
	selectedButtonRef.value = buttonElement;
	emit("selectMainNavView", navItem); // Emit the selected option

	// Save the selected navigation item to localStorage
	localStorage.setItem("DeviceDetailsMainNav", navItem);
}

function openNav() {
	panelStore.panelRef2?.isCollapsed ? panelStore.panelRef2?.expand() : panelStore.panelRef2?.collapse();
}

watch(
	() => panelStore.panelRef2?.isCollapsed,
	() => {
		updateBottomBorder();
	}
);

watch(
	() => props.selectedNav,
	(newVal) => {
		selectNav(newVal, document.querySelector(`[data-nav='${newVal}']`));
	}
);

const navItems = computed(() => {
	const items = [
		{
			label: "Configs",
			to: "configs",
			icon: "command-group",
		},
		{
			label: "Notifications",
			to: "notifications",
			icon: "notification",
		},
	];

	// Conditionally add xftp item
	if (props.hasXftpFiles) {
		items.push({
			label: "XFTP Files",
			to: "xftp",
			icon: "xftp",
		});
	}

	// Conditionally add api-endpoints item
	if (props.hasApiEndpoints) {
		items.push({
			label: "API Endpoints",
			to: "api-endpoints",
			icon: "api-collection",
			badge: props.apiEndpointsCount > 0 ? props.apiEndpointsCount : null,
		});
	}

	return items;
});

function handleNavSelection(navItem) {
	selectedNav.value = navItem;
	emit("selectMainNavView", navItem);
	localStorage.setItem("DeviceDetailsMainNav", navItem);
}
</script>

<template>
	<div class="relative flex items-center w-full border-b p-2 pb-1">
		<NavOpenButton @openNav="openNav()" :navPanelBtnState="panelStore.panelRef2?.isCollapsed" />

		<div class="flex justify-between w-full mb-0">
			<div>
				<NavPills :items="navItems" v-model="selectedNav" persist-key="DeviceDetailsMainNav" @select="handleNavSelection" />
			</div>
			<div>
				<h3 class="gap-2 ml-auto mr-4 text-lg font-semibold tracking-tight group" v-if="selectedNav === 'notifications'">
					Device Last Events
				</h3>

				<h3 class="gap-2 ml-auto mr-4 text-lg font-semibold tracking-tight group" v-if="selectedNav === 'configs'">
					Latest Configs
				</h3>
				<h3 class="gap-2 ml-auto mr-4 text-lg font-semibold tracking-tight group" v-if="selectedNav === 'xftp' && hasXftpFiles">XFTP Files</h3>
				<h3 class="gap-2 ml-auto mr-4 text-lg font-semibold tracking-tight group" v-if="selectedNav === 'api-endpoints' && hasApiEndpoints">
					API Endpoints
				</h3>
			</div>
		</div>
	</div>
</template>
