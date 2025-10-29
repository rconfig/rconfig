<script setup>
import Loading from "@/pages/Shared/Loaders/Loading.vue";
import SidebarDropdownNav from "@/pages/Settings/SidebarDropdownNav.vue";
import SidebarNav from "@/pages/Settings/SidebarNav.vue";
import { Separator } from "@/components/ui/separator";
import { useSettings } from "@/pages/Settings/useSettings";

const { settingsActivePane, setForm, formComponents, settingsActivePaneComponent } = useSettings();
</script>

<template>
	<div class="pb-16 px-6 space-y-6 md:block">
		<div class="space-y-0.5">
			<h2 class="flex items-center space-x-2 text-2xl font-bold tracking-tight">
				<RcIcon name="settings" class="w-8 h-8" />
				<span>System Settings</span>
			</h2>
			<p class="text-muted-foreground">Manage your application settings and preferences</p>
		</div>
		<Separator class="my-6" />
		<div class="flex flex-col lg:flex-row lg:space-x-12 space-y-8 lg:space-y-0">
			<!-- Sidebar Nav (hidden on small screens, shown on lg and above) -->
			<div class="lg:block hidden w-full lg:w-1/4 xl:w-1/5">
				<SidebarNav v-if="settingsActivePane" @nav="setForm($event)" :settingsActivePane="settingsActivePane" />
			</div>

			<!-- Sidebar Dropdown Nav (shown on small screens) -->
			<div class="lg:hidden w-full">
				<SidebarDropdownNav @nav="setForm($event)" />
			</div>

			<!-- Main Content Area -->
			<div class="w-full lg:w-3/4 xl:w-4/5">
				<div class="flex flex-col w-full">
					<!-- Loading State -->
					<Loading v-if="!settingsActivePane" class="mx-auto" />

					<!-- Main Component Area -->
					<transition name="fade" mode="out-in">
						<component v-if="settingsActivePane" :is="settingsActivePaneComponent" :key="settingsActivePane" />
					</transition>
				</div>
			</div>
		</div>
	</div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
	transition: opacity 0.2s ease;
}
.fade-enter,
.fade-leave-to {
	opacity: 0;
}
</style>
