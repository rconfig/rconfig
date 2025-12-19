<script setup>
import { ref, watch, computed } from "vue";
import { useRouter } from "vue-router";
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from "@/components/ui/collapsible";
import { FileCode2, Bug, FileText, Save, BetweenHorizontalStart, CircleFadingArrowUp, Info, CloudUpload, ChevronRight, ChevronDown, Settings, Shield, Key, Users } from "lucide-vue-next";

const props = defineProps({
	settingsActivePane: String,
});
const router = useRouter();

// Use localStorage to persist the state of the collapsibles
const systemManagementIsOpen = ref(JSON.parse(localStorage.getItem("sidebarSystemManagementIsOpen")) ?? true);
const securityAccessIsOpen = ref(JSON.parse(localStorage.getItem("sidebarSecurityAccessIsOpen")) ?? true);
const dataManagementIsOpen = ref(JSON.parse(localStorage.getItem("sidebarDataManagementIsOpen")) ?? true);
const monitoringDebuggingIsOpen = ref(JSON.parse(localStorage.getItem("sidebarMonitoringDebuggingIsOpen")) ?? true);
const infoIsOpen = ref(JSON.parse(localStorage.getItem("sidebarInfoIsOpen")) ?? true);

// Watch for changes and update localStorage
watch(systemManagementIsOpen, (newVal) => {
	localStorage.setItem("sidebarSystemManagementIsOpen", JSON.stringify(newVal));
});

watch(securityAccessIsOpen, (newVal) => {
	localStorage.setItem("sidebarSecurityAccessIsOpen", JSON.stringify(newVal));
});

watch(dataManagementIsOpen, (newVal) => {
	localStorage.setItem("sidebarDataManagementIsOpen", JSON.stringify(newVal));
});

watch(monitoringDebuggingIsOpen, (newVal) => {
	localStorage.setItem("sidebarMonitoringDebuggingIsOpen", JSON.stringify(newVal));
});

watch(infoIsOpen, (newVal) => {
	localStorage.setItem("sidebarInfoIsOpen", JSON.stringify(newVal));
});

// Define menu sections with their items
const sections = computed(() => [
	{
		heading: "System Management",
		openState: systemManagementIsOpen,
		items: [
			{
				title: "System",
				href: "/settings/system",
				icon: Settings,
			},
			{
				title: "Upgrade",
				href: "/settings/upgrade",
				icon: CloudUpload,
			},
		],
	},
	{
		heading: "Security & Access",
		openState: securityAccessIsOpen,
		items: [
			{
				title: "Security",
				href: "/settings/security",
				icon: Shield,
			},
			{
				title: "Device Credentials",
				href: "/settings/credentials",
				icon: Key,
			},
		],
	},
	{
		heading: "Data Management",
		openState: dataManagementIsOpen,
		items: [
			{
				title: "Data Migration",
				href: "/settings/data-migration",
				icon: BetweenHorizontalStart,
			},
		],
	},
	{
		heading: "Monitoring & Debugging",
		openState: monitoringDebuggingIsOpen,
		items: [
			{
				title: "Logging & Debugging",
				href: "/settings/debugging",
				icon: Bug,
			},
			{
				title: "Application Logs",
				href: "/settings/logs",
				icon: FileText,
			},
			{
				title: "Scheduled Tasks",
				href: "/settings/scheduled-tasks",
				icon: FileCode2,
			},
		],
	},
	{
		heading: "Info",
		openState: infoIsOpen,
		items: [
			{
				title: "About",
				href: "/settings/about",
				icon: Info,
			},
		],
	},
]);

const emit = defineEmits(["nav"]);

function navTo(href) {
	emit("nav", href);
	// Also update the router to maintain consistency between URL and component
	router.push(href);
}
</script>

<template>
	<nav class="flex flex-col space-y-2">
		<div v-for="section in sections" :key="section.heading" class="space-y-1">
			<!-- Section with Collapsible -->
			<Collapsible v-model:open="section.openState.value" class="w-full">
				<div class="flex items-center justify-between">
					<CollapsibleTrigger as-child>
						<div class="flex items-center w-full">
							<Button variant="ghost" size="sm" class="w-full pl-0 pr-4">
								<div class="flex items-center w-full cursor-pointer">
									<ChevronRight size="18" class="text-rcgray-500" v-if="!section.openState.value" />
									<ChevronDown size="18" class="text-rcgray-500" v-if="section.openState.value" />
									<div class="ml-2 text-left" style="color: rgb(134, 136, 141);">
										<span>{{ section.heading }}</span>
									</div>
								</div>
							</Button>
						</div>
					</CollapsibleTrigger>
				</div>

				<CollapsibleContent>
					<div>
						<Button v-for="item in section.items" :key="item.title" @click="navTo(item.href)" variant="ghost" :class="[settingsActivePane === item.href ? 'bg-rcgray-600' : 'text-rcgray-300', 'inline-flex items-center justify-start w-full px-4 py-2 text-sm font-medium text-left rounded-md focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:text-accent-foreground hover:bg-rcgray-800 h-auto min-h-[2.25rem] whitespace-normal']">
							<component :is="item.icon" class="w-4 h-4 mr-2 shrink-0" />
							{{ item.title }}
						</Button>
					</div>
				</CollapsibleContent>
			</Collapsible>
		</div>
	</nav>
</template>