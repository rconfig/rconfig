<script setup>
import NavigationSideI18n from "@/i18n/layouts/NavigationSide.i18n.js";
import RcToolTip from "@/pages/Shared/Tooltips/RcToolTip.vue";
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList, CommandSeparator } from "@/components/ui/command";
import { Dialog, DialogContent } from "@/components/ui/dialog";
import { Navigation, ExternalLink, Search } from "lucide-vue-next";
import { ref, onMounted, onUnmounted, computed } from "vue";
import { useComponentTranslations } from "@/composables/useComponentTranslations";
import { useRouter } from "vue-router";

const router = useRouter();
const { t } = useComponentTranslations(NavigationSideI18n);

const props = defineProps({
	panelWidth: Number,
});
// Command palette state
const isOpen = ref(false);
const searchQuery = ref("");

// Navigation items based on your existing routes
const navigationItems = computed(() => [
	// Main Navigation
	{
		id: "dashboard",
		label: t("dashboard"),
		icon: "dashboard",
		route: "/",
		group: "Main Navigation",
	},
	{
		id: "inventory",
		label: t("inventory"),
		icon: "inventory",
		route: "/inventory",
		group: "Main Navigation",
	},
	{
		id: "configs",
		label: t("configTools"),
		icon: "config-tools",
		route: "/configs",
		group: "Main Navigation",
	},
	{
		id: "tasks",
		label: t("tasks"),
		icon: "tasks",
		route: "/tasks",
		group: "Main Navigation",
	},
	// inventory
	{
		id: "devices",
		label: t("devices"),
		icon: "device",
		route: "/devices",
		group: "Inventory",
	},
	{
		id: "commandgroups",
		label: t("commandgroups"),
		icon: "command-group",
		route: "/commandgroups",
		group: "Inventory",
	},
	{
		id: "commands",
		label: t("commands"),
		icon: "commands",
		route: "/commands",
		group: "Inventory",
	},
	{
		id: "command-compare-options",
		label: t("commandcompareoptions"),
		icon: "config-compare",
		route: "/command-compare-options",
		group: "Inventory",
	},
	{
		id: "templates",
		label: t("templates"),
		icon: "template",
		route: "/templates",
		group: "Inventory",
	},
	{
		id: "vendors",
		label: t("vendors"),
		icon: "vendor",
		route: "/vendors",
		group: "Inventory",
	},
	{
		id: "tags",
		label: t("tags"),
		icon: "tag",
		route: "/tags",
		group: "Inventory",
	},
	// configs
	{
		id: "configs",
		label: t("configs"),
		icon: "config-tools",
		route: "/configs",
		group: "Configs",
	},
	{
		id: "configsearch",
		label: t("configSearch"),
		icon: "config-search",
		route: "/config-search",
		group: "Configs",
	},
	{
		id: "configcompare",
		label: t("configCompare"),
		icon: "config-compare",
		route: "/config-compare",
		group: "Configs",
	},
	{
		id: "users",
		label: t("users"),
		icon: "user",
		route: "/settings/users",
		group: "Settings",
	},
	{
		id: "backups",
		label: t("backups"),
		icon: "backup",
		route: "/settings/backups",
		group: "Settings",
	},
	{
		id: "system-settings",
		label: t("system"),
		icon: "settings",
		route: "/settings",
		group: "Settings",
	},

	// External Tools
	{
		id: "log-viewer",
		label: t("systemLogViewer"),
		icon: "sys-log-viewer",
		route: "/log-viewer",
		group: "External Tools",
		external: true,
	},
	{
		id: "queue-manager",
		label: t("systemQueueManager"),
		icon: "sys-queue-manager",
		route: "/horizon/dashboard",
		group: "External Tools",
		external: true,
	},
	{
		id: "Docs",
		label: t("documentation"),
		icon: "rconfig-white",
		route: "https://docs.rconfig.com",
		group: "Help",
		action: "openHelp",
		external: true,
	},
]);

// Keyboard shortcut handler
const handleKeyDown = (event) => {
	// Cmd/Ctrl + K to open command palette
	if ((event.metaKey || event.ctrlKey) && event.key === "k") {
		event.preventDefault();
		isOpen.value = !isOpen.value;
	}

	// Escape to close
	if (event.key === "Escape") {
		isOpen.value = false;
	}
};

// Navigate to selected item
const handleSelect = (item) => {
	if (item.external) {
		// Open external links in new tab
		window.open(item.route, "_blank");
	} else if (item.action === "openHelp") {
		// Handle special actions (you'll need to emit this or call the parent method)
		// For now, we'll assume you have access to the openSheet method from parent
		// You might need to emit this event or use a composable
		console.log("Opening help sheet"); // Replace with actual help opening logic
	} else {
		// Regular navigation
		router.push(item.route);
	}

	isOpen.value = false;
	searchQuery.value = "";
};

// Lifecycle hooks
onMounted(() => {
	document.addEventListener("keydown", handleKeyDown);
});

onUnmounted(() => {
	document.removeEventListener("keydown", handleKeyDown);
});

// Group items by category
const groupedItems = computed(() => {
	const groups = {};
	navigationItems.value.forEach((item) => {
		if (!groups[item.group]) {
			groups[item.group] = [];
		}
		groups[item.group].push(item);
	});
	return groups;
});

// Expose method for manual opening (you can call this from parent component)
defineExpose({
	open: () => {
		isOpen.value = true;
	},
});
</script>

<template>
	<!-- Command Palette Dialog -->
	<Dialog v-model:open="isOpen">
		<DialogContent class="p-0 max-w-[450px] top-[20%] translate-y-0">
			<Command class="rounded-lg border-0 shadow-md">
				<CommandInput v-model="searchQuery" placeholder="Navigate to…" class="border-0 focus:ring-0" />
				<CommandList class="max-h-[300px]">
					<CommandEmpty>No results found.</CommandEmpty>

					<!-- Navigation Group -->
					<CommandGroup v-for="(items, groupName) in groupedItems" :key="groupName" :heading="groupName">
						<CommandItem v-for="item in items" :key="item.id" :value="item.id" @select="handleSelect(item)" class="cursor-pointer">
							<RcIcon :name="item.icon" class="mr-2 h-4 w-4" />
							<span>{{ item.label }}</span>
							<ExternalLink v-if="item.external" class="ml-auto h-3 w-3 text-muted-foreground" />
						</CommandItem>
					</CommandGroup>

					<CommandSeparator />

					<!-- Quick Actions Group -->
					<CommandGroup heading="Quick Actions">
						<CommandItem
							value="search"
							@select="
								() => {
									/* Add search functionality */
								}
							"
							class="cursor-pointer"
						>
							<Search class="mr-2 h-4 w-4" />
							<span>Record Search</span>
							<kbd class="ml-auto text-xs text-muted-foreground rc-kdb-class">CTRL+/</kbd>
						</CommandItem>
					</CommandGroup>
				</CommandList>
			</Command>
		</DialogContent>
	</Dialog>

	<!-- Optional: Add a trigger button to your existing navigation -->
	<div class="w-1/2 mx-2 mb-2">
		<RcToolTip :delayDuration="100" :content="`Quick navigation search... (⌘ Ctrl+K)`" :side="'bottom'">
			<template #trigger>
				<Button @click="isOpen = true" variant="ghost" size="sm" class="w-auto px-2 py-1 rc-btn-shadow bg-rcgray-900 hover:bg-rcgray-850 transition focus:outline-none focus:ring-0">
					<div class="flex items-center w-full">
						<Navigation class="text-muted-foreground h-4 w-4 opacity-60" />

						<div class="ml-2">
							<kbd class="ml-auto text-xs opacity-60 rc-kdb-class">
								⌘ Ctrl+K
							</kbd>
						</div>
					</div>
				</Button>
			</template>
		</RcToolTip>
	</div>
</template>

<style scoped>
/* Custom styles if needed */
</style>
