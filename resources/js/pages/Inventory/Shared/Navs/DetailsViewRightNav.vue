<script setup>
import NavOpenButton from "@/pages/Shared/Buttons/NavOpenButton.vue"; // Import the NavOpenButton component
import { Megaphone } from "lucide-vue-next";
import { ref, onMounted, nextTick, watch, computed } from "vue";
import { usePanelStore } from "@/stores/panelStore"; // Import the Pinia store
import NavPills from "@/pages/Shared/Buttons/NavPills.vue";

const activeNav = ref(null);
const selectedButtonRef = ref(null);
const panelStore = usePanelStore(); // Access the panel store
const props = defineProps({
	selectedNav: String,
});

const emit = defineEmits(["selectMainNavView", "closeNav"]);

onMounted(() => {
	// Check localStorage for saved navigation item
	const savedNav = localStorage.getItem("DeviceDetailsMainNav");
	activeNav.value = savedNav || props.selectedNav;
});

function selectNav(navItem, buttonElement) {
	activeNav.value = navItem;
	selectedButtonRef.value = buttonElement;
	emit("selectMainNavView", navItem); // Emit the selected option

	// Save the selected navigation item to localStorage
	localStorage.setItem("DeviceDetailsMainNav", navItem);
}

function openNav() {
	panelStore.panelRef2?.isCollapsed ? panelStore.panelRef2?.expand() : panelStore.panelRef2?.collapse();
}

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


	return items;
});

function handleNavSelection(navItem) {
	activeNav.value = navItem;
	emit("selectMainNavView", navItem);
	localStorage.setItem("DeviceDetailsMainNav", navItem);
}
</script>

<template>
	<div class="relative flex items-center w-full border-b p-2 pb-1">
		<NavOpenButton
			:nav-panel-btn-state="panelStore.panelRef2?.isCollapsed"
			@open-nav="openNav()"
		/>

		<div class="flex justify-between w-full mb-0">
			<div>
				<NavPills
					v-model="activeNav"
					:items="navItems"
					persist-key="DeviceDetailsMainNav"
					@select="handleNavSelection"
				/>
			</div>
			<div>
				<h3
					v-if="activeNav === 'notifications'"
					class="gap-2 ml-auto mr-4 text-lg font-semibold tracking-tight group"
				>
					Device Last Events
				</h3>

				<h3
					v-if="activeNav === 'configs'"
					class="gap-2 ml-auto mr-4 text-lg font-semibold tracking-tight group"
				>
					Latest Configs
				</h3>
			</div>
		</div>
	</div>
</template>
