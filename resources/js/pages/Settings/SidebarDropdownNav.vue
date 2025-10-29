<script setup>
import { ref, computed } from "vue";
import { Menubar, MenubarItem, MenubarMenu, MenubarContent, MenubarTrigger, MenubarSub, MenubarSubContent, MenubarSubTrigger } from "@/components/ui/menubar";
import { useRouter } from "vue-router";

const router = useRouter();

// Define the sections with their items and collapsible states
const sections = computed(() => [
	{
		heading: "System Management",
		openState: ref(false),
		items: [
			{
				title: "System",
				href: "/settings/system",
			},
		],
	},
	{
		heading: "Security & Access",
		openState: ref(false),
		items: [
			{
				title: "Security",
				href: "/settings/security",
			},
			{
				title: "Device Credentials",
				href: "/settings/credentials",
			},
		],
	},
	{
		heading: "Monitoring & Debugging",
		openState: ref(false),
		items: [
			{
				title: "Logging & Debugging",
				href: "/settings/debugging",
			},
			{
				title: "Application Logs",
				href: "/settings/logs",
			},
			{
				title: "Scheduled Tasks",
				href: "/settings/scheduledtasks",
			},
		],
	},
	{
		heading: "Info",
		openState: ref(false),
		items: [
			{
				title: "About",
				href: "/settings/about",
			},
		],
	},
]);

const emit = defineEmits(["nav"]);

function navTo(href) {
	emit("nav", href);
}
</script>

<template>
	<Menubar class="flex items-start w-full bg-rcgray-900 text-rcgray-100 rounded-md shadow-md h-fit">
		<!-- Top-level navigation items -->
		<div class="flex space-x-4">
			<MenubarMenu v-for="(section, index) in sections" :key="index">
				<MenubarTrigger class="flex items-start">{{ section.heading }}</MenubarTrigger>
				<MenubarContent class="flex space-x-4">
					<!-- Submenu items -->
					<div v-if="section.openState" class="flex space-x-4">
						<MenubarItem v-for="item in section.items" :key="item.title" @click="navTo(item.href)" variant="ghost" class="inline-flex items-center justify-start w-auto px-4 py-2 text-sm font-medium text-left rounded-md focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring hover:text-accent-foreground hover:bg-rcgray-800">
							{{ item.title }}
						</MenubarItem>
					</div>
				</MenubarContent>
			</MenubarMenu>
		</div>
	</Menubar>
</template>

<style scoped></style>