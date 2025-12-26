<script setup>
import ConfigurationsPopover from "@/layouts/Components/ConfigurationsPopover.vue";
import ExternalToolDialog from "@/layouts/Components/ExternalToolDialog.vue";
import InventoryPopover from "@/layouts/Components/InventoryPopover.vue";
import NavCloseButton from "@/pages/Shared/Buttons/NavCloseButton.vue";
import NotificationsPopover from "@/layouts/Components/NotificationsPopover.vue";
import QuickActions from "@/layouts/Components/QuickActions.vue";
import SheetHelp from "@/layouts/Components/SheetHelp.vue";
import { ChevronRight, ChevronDown, ExternalLink, Trash2, LifeBuoy } from "lucide-vue-next";
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from "@/components/ui/collapsible";
import { Icon } from "@iconify/vue"; // Used for External Links ONLY
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { inject, ref, onMounted, onBeforeUnmount } from "vue";
import { useConfigs } from "@/pages/Configs/useConfigs";
import { useInventory } from "@/pages/Inventory/useInventory";
import { useNavigationSide } from "./useNavigationSide";

// Get the userId from inject
const userid = inject("userid");

// Use the composable to get all navigation functionality and state
const { sideNavSettingsIsOpen,sideNavExtLinksIsOpen, sideNavFavLinksIsOpen, externalLinks, externalLinksDialogKey, favoritesStore, notificationsLength, panelElement, closeNav, closeExtDialog, removeExternalLink, notificationsCount, navToSettingsUpgrade, openSheet } = useNavigationSide(userid);
const { viewItems: inventoryViewItems } = useInventory();
const { viewItems: configViewItems } = useConfigs();

const image = ref("/images/brand/rconfig-with-strap-white.png");
const panelWidth = ref(0);
let observer;

onMounted(() => {
	observer = new ResizeObserver((entries) => {
		for (const entry of entries) {
			panelWidth.value = entry.contentRect.width;
		}
	});

	if (panelElement.value?.$el) {
		observer.observe(panelElement.value.$el);
	} else if (panelElement.value) {
		observer.observe(panelElement.value);
	}
});

onBeforeUnmount(() => {
	if (observer) observer.disconnect();
});

// Add a method to toggle favorites
const toggleFavorite = (viewId) => {
	// If an item object was passed directly
	if (typeof viewId === "object" && viewId !== null) {
		// Handle object case (when called from favorites list)
		viewId.isFavorite = { value: !viewId.isFavorite?.value };
		favoritesStore.toggleFavorite(viewId);
		return;
	}

	// Handle ID case (when called from elsewhere)
	const allViewItems = [...inventoryViewItems, ...configViewItems];
	const viewItem = allViewItems.find((item) => item.id === viewId);

	if (viewItem) {
		viewItem.isFavorite = !viewItem.isFavorite;
		favoritesStore.toggleFavorite(viewItem);
	}
};
</script>

<template>
	<resizable-panel id="nav-panel-1" :default-size="17" :max-size="30" :min-size="10" collapsible :collapsed-size="0" ref="panelElement" class="dark:bg-rcgray-800">
		<div class="grid h-full overflow-y-auto">
			<div class="bg-gray-100 bg-muted/40 md:block">
				<div class="flex flex-col h-full max-h-screen gap-2">
					<div class="flex justify-between w-full max-w-full px-2 py-1">
						<div class="flex items-center my-2">
							<router-link to="/" class="pf-v5-c-page__header-brand-link">
								<img alt="rConfig" class="pf-v5-c-brand max-h-12 ml-4" :src="image" />
							</router-link>
						</div>
						<div class="flex items-center justify-center h-full">
							<NavCloseButton class="mr-2" @close="closeNav()" />
						</div>
					</div>

					<div class="flex-1">
						<QuickActions :panelWidth="panelWidth" />

						<div class="mx-2 hover:transition-all">
							<nav class="grid items-start px-2 text-sm font-medium">
								<NotificationsPopover @notificationsLength="notificationsCount($event)">
									<div class="group cursor-pointer transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md hover:bg-rcgray-600">
										<RcIcon name="notification" class="h-4" />
										<div class="flex justify-between w-full p-1 text-gray-200">
											<span>Notifications</span>
											<span class="bg-blue-100 text-blue-800 text-xs font-medium me-2 px-1 py-0.5 rounded-lg dark:bg-blue-900 dark:text-blue-300" v-if="notificationsLength > 0">
												{{ notificationsLength }}
											</span>
										</div>
										<span class="text-rcgray-400 group-hover:text-blue-400 transition-colors h-full mr-2">
											<ChevronRight size="16" />
										</span>
									</div>
								</NotificationsPopover>

								<router-link
									to="/"
									class="transition ease-in-out delay-50 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600"
									:class="{
										'active-nav': $route.name === 'Home' || $route.name === 'Dashboard' || $route.path === '/' || ($route.path === '' && $route.name === null),
									}"
								>
									<span class="gradient-indicator"></span>
									<RcIcon name="dashboard" class="w-4" />
									<div class="p-1 ml-2 text-left text-gray-200">
										<div>Dashboard</div>
									</div>
								</router-link>

								<InventoryPopover :boundary-el="panelElement?.$el || panelElement" :panel-width="panelWidth">
									<div to="/inventory" class="group flex justify-between transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600" :class="{ 'active-nav': inventoryViewItems.some((item) => $route.path.startsWith(item.route)) }">
										<div class="flex items-center">
											<RcIcon name="inventory" />
											<div class="p-1 ml-2 text-left text-gray-200">
												<div>Inventory</div>
											</div>
										</div>
										<span class="text-rcgray-400 group-hover:text-blue-400 transition-colors h-full mr-2">
											<ChevronRight size="16" />
										</span>
									</div>
								</InventoryPopover>

								<ConfigurationsPopover :boundary-el="panelElement?.$el || panelElement" :panel-width="panelWidth">
									<div to="/configs" class="group flex justify-between transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600" :class="{ 'active-nav': configViewItems.some((item) => $route.path.startsWith(item.route)) }">
										<div class="flex items-center">
											<RcIcon name="config-tools" class="w-4" />
											<div class="p-1 ml-2 text-left text-gray-200">
												<div>Config Tools</div>
											</div>
										</div>
										<span class="text-rcgray-400 group-hover:text-blue-400 transition-colors h-full mr-2">
											<ChevronRight size="16" />
										</span>
									</div>
								</ConfigurationsPopover>

								<router-link to="/tasks" class="transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600" :class="{ 'active-nav': $route.name === 'scheduled-tasks' }">
									<RcIcon name="tasks" />
									<div class="p-1 ml-2 text-left text-gray-200">
										<div>Tasks</div>
									</div>
								</router-link>
								
								<Collapsible v-model:open="sideNavSettingsIsOpen" class="w-full mt-2">
									<div class="flex items-center justify-between">
										<CollapsibleTrigger as-child>
											<div class="flex items-center w-full">
												<Button variant="ghost" size="sm" class="w-full pl-0 pr-4">
													<div class="flex items-center w-full cursor-pointer" type="button" aria-expanded="true" data-state="open">
														<ChevronRight v-if="!sideNavSettingsIsOpen" size="18" class="text-rcgray-500" />
														<ChevronDown v-if="sideNavSettingsIsOpen" size="18" class="text-rcgray-500" />
														<div class="ml-2 text-left" data-truncate="false" data-numeric="false" data-uppercase="false" style="color: rgb(134, 136, 141);">
															<span>Settings</span>
														</div>
													</div>
												</Button>
											</div>
										</CollapsibleTrigger>
									</div>
									<CollapsibleContent>
										<div>
											<router-link to="/settings/integrations" class="transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600" :class="{ 'active-nav': $route.name === 'integrations' }">
												<RcIcon name="integrations" />
												<div class="p-1 ml-2 text-left text-gray-200">
													<div> Integrations </div>
												</div>
											</router-link>
											<router-link to="/settings/users" class="transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600" :class="{ 'active-nav': $route.name === 'users' }">
												<RcIcon name="user" />
												<div class="p-1 ml-2 text-left text-gray-200">
													<div> Users </div>
												</div>
											</router-link>
											<router-link to="/settings" class="transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600" :class="{ 'active-nav': $route.name === 'settings' }">
												<RcIcon name="settings" />
												<div class="p-1 ml-2 text-left text-gray-200">
													<div> System Settings </div>
												</div>
											</router-link>
										</div>
									</CollapsibleContent>
								</Collapsible>

								<Collapsible v-model:open="sideNavExtLinksIsOpen" class="w-full mt-2">
									<div class="flex items-center justify-between">
										<CollapsibleTrigger as-child>
											<div class="flex items-center w-full">
												<Button variant="ghost" size="sm" class="w-full pl-0 pr-4">
													<div class="flex items-center w-full cursor-pointer" type="button" aria-expanded="true" data-state="open">
														<ChevronRight v-if="!sideNavExtLinksIsOpen" size="18" class="text-rcgray-500" />
														<ChevronDown v-if="sideNavExtLinksIsOpen" size="18" class="text-rcgray-500" />
														<div class="ml-2 text-left" data-truncate="false" data-numeric="false" data-uppercase="false" style="color: rgb(134, 136, 141)">
															<span>External Tools</span>
														</div>
													</div>
												</Button>
											</div>
										</CollapsibleTrigger>
										<ExternalToolDialog @close="closeExtDialog" :key="externalLinksDialogKey" />
									</div>
									<CollapsibleContent>
										<div>
											<router-link to="/log-viewer" target="_blank" class="transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600">
												<RcIcon name="sys-log-viewer" />
												<div class="flex items-center justify-between w-full p-1 ml-2 text-left text-gray-200">
													<div>System Log Viewer</div>
													<ExternalLink size="18" class="text-rcgray-500" />
												</div>
											</router-link>
											<router-link to="/horizon/dashboard" target="_blank" class="transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600">
												<RcIcon name="sys-queue-manager" />
												<div class="flex items-center justify-between w-full p-1 ml-2 text-left text-gray-200">
													<div>System Queue Manager</div>
													<ExternalLink size="18" class="text-rcgray-500" />
												</div>
											</router-link>
											<div class="transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600">
												<RcIcon name="external-link" />
												<a href="https://www.rconfig.com" target="_blank" class="flex items-center justify-between w-full p-1 ml-2 text-left text-gray-200">
													<div>rConfig.com</div>
													<ExternalLink size="18" class="text-rcgray-500" />
												</a>
											</div>
											<div v-if="externalLinks.length > 0" v-for="link in externalLinks" class="transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600">
												<Icon :icon="link.icon" class="text-rcgray-400 w-6 h-6" />
												<a :href="link.url" target="_blank" class="flex items-center justify-between w-full p-1 ml-2 text-left text-gray-200">
													<div>{{ link.name }}</div>
												</a>
												<Trash2 size="18" class="text-rcgray-500 mr-1 hover:text-rcgray-400" @click="removeExternalLink(link.name)" />
											</div>
										</div>
									</CollapsibleContent>
								</Collapsible>

								<Collapsible v-model:open="sideNavFavLinksIsOpen" class="w-full mb-4">
									<div class="flex items-center justify-between">
										<CollapsibleTrigger as-child>
											<div class="flex items-center w-full">
												<Button variant="ghost" size="sm" class="w-full pl-0 pr-4">
													<div class="flex items-center w-full cursor-pointer" type="button" aria-expanded="true" data-state="open">
														<ChevronRight v-if="!sideNavFavLinksIsOpen" size="18" class="text-rcgray-500" />
														<ChevronDown v-if="sideNavFavLinksIsOpen" size="18" class="text-rcgray-500" />
														<div class="ml-2 text-left" data-truncate="false" data-numeric="false" data-uppercase="false" style="color: rgb(134, 136, 141)">Favorites</div>
													</div>
												</Button>
											</div>
										</CollapsibleTrigger>
									</div>
									<CollapsibleContent>
										<div v-if="favoritesStore.favorites.size == 0" class="ml-6 text-xs text-muted-foreground">No favorites set</div>
										<div v-else>
											<div v-for="item in favoritesStore.favorites" :key="item.id" class="flex items-center">
												<router-link :to="item.route" class="transition ease-in-out delay-150 flex justify-between items-center flex-grow mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600" :class="{ 'active-nav': $route.name === item.route }">
													<div class="flex items-center">
														<RcIcon :name="item.icon" />
														<div class="p-1 ml-2 text-left text-gray-200">
															<div>{{ item.label }}</div>
														</div>
													</div>
													<TooltipProvider>
														<Tooltip>
															<TooltipTrigger as-child>
																<div @click.stop.prevent="toggleFavorite(item)" class="p-1 cursor-pointer">
																	<RcIcon name="star-selected" class="w-4 h-4 text-yellow-400 animated-star" />
																</div>
															</TooltipTrigger>
															<TooltipContent>
																<p>Remove from favorites</p>
															</TooltipContent>
														</Tooltip>
													</TooltipProvider>
												</router-link>
											</div>
										</div>
									</CollapsibleContent>
								</Collapsible>
							</nav>
						</div>
					</div>

					<div class="p-4 mt-auto border-t">
						<a @click.prevent="openSheet('SheetHelp')" class="transition ease-in-out delay-150 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600" :class="{ 'active-nav': $route.name === 'scheduled-tasks' }">
							<LifeBuoy class="text-rcgray-400" size="18" />
							<div class="p-1 ml-2 text-left text-gray-200">
								<div>Getting started & Help</div>
							</div>
						</a>
						<SheetHelp />
					</div>
				</div>
			</div>
		</div>
	</resizable-panel>
</template>

<style scoped>
.active-nav {
	@apply font-semibold text-sm bg-rcgray-600;
}
</style>