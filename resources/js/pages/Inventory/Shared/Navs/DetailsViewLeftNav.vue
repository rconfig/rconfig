<script setup>
import NavCloseButton from "@/pages/Shared/Buttons/NavCloseButton.vue";
import NavPills from "@/pages/Shared/Buttons/NavPills.vue";
import { ref, computed, watch } from "vue";
import { useCommentsStore } from "@/stores/useCommentsStore";

const props = defineProps({
	selectedNav: String,
	deviceId: Number,
	context: {
		type: String,
		default: "device",
		validator: (value) => ["device", "config"].includes(value),
	},
});

const selectedNav = ref(props.selectedNav);
const commentsStore = useCommentsStore();
const commentCount = ref(commentsStore.commentCounters[props.deviceId] || 0);

const emit = defineEmits(["selectLeftNavView", "closeNav"]);

// Create navigation items based on context
const navItems = computed(() => {
	const items = [
		{
			label: "Details",
			to: "details",
			icon: "list-collapse", // You might need to map this to your RcIcon equivalent
		},
	];

	// Conditionally add config history for config context
	if (props.context === "config") {
		items.push({
			label: "Config History",
			to: "configHistory",
			icon: "config-history",
		});
	}

	// Uncomment when ready to add comments back
	// items.push({
	//   label: `${t("navigation.comments")} (${commentCount.value})`,
	//   to: "comments",
	//   icon: "comments"
	// });

	return items;
});

// Watch for changes to the comment counter for the current device
watch(
	() => commentsStore.commentCounters[props.deviceId],
	(newCount) => {
		commentCount.value = newCount;
	}
);

// Watch for prop changes
watch(
	() => props.selectedNav,
	(newNav) => {
		selectedNav.value = newNav;
	}
);

function handleNavSelection(navItem) {
	selectedNav.value = navItem;
	emit("selectLeftNavView", navItem);
}

function closeNav() {
	emit("closeNav");
}
</script>

<template>
	<div class="relative flex items-center justify-between border-b">
		<div class="flex items-start pt-2 pb-1 px-2 pt-1">
			<NavPills :items="navItems" v-model="selectedNav" persist-key="detailsViewLeftNav" @select="handleNavSelection" />
		</div>

		<NavCloseButton class="mr-2" @close="closeNav()" />
	</div>
</template>
