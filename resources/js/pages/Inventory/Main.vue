<script setup>
import Command from "@/pages/Inventory/Commands/Main.vue";
import CommandGroups from "@/pages/Inventory/CommandGroups/Main.vue";
import Devices from "@/pages/Inventory/Devices/Main.vue";
import Tags from "@/pages/Inventory/Tags/Main.vue";
import Template from "@/pages/Inventory/Templates/Main.vue";
import Vendors from "@/pages/Inventory/Vendors/Main.vue";
import { ChevronDown } from "lucide-vue-next";
import { DropdownMenu, DropdownMenuContent, DropdownMenuGroup, DropdownMenuItem, DropdownMenuShortcut, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { Button } from "@/components/ui/button";
import { ref, computed } from "vue";
import { useInventory } from "@/pages/Inventory/useInventory";

// State for dropdown open/close
const isDropdownOpen = ref(false);

const {
	// State
	currentView,
	viewItems,

	// Methods
	changeView,
	toggleFavorite,
} = useInventory();

// Check if the current view exists in the list of valid views
const currentViewExists = computed(() => {
	const validViews = ["devices", "commandgroups", "commands", "templates", "vendors", "tags"];
	return validViews.includes(currentView.value);
});
</script>

<template>
	<main class="flex flex-col flex-1 gap-2 dark:bg-rcgray-900">
		<div class="border-t border-b topRow">
			<DropdownMenu @update:open="isDropdownOpen = $event">
				<div class="flex items-center justify-between">
					<DropdownMenuTrigger as-child class="p-4 ml-2">
						<Button variant="outline" class="w-48">
							<div class="flex items-center justify-start w-full gap-2">
								<RcIcon name="pin" class="w-4 h-4 text-blue-400 shrink-0" />
								<span class="truncate">{{ viewItems.find((item) => item.id === currentView)?.label || "Select View" }}</span>
								<ChevronDown class="w-4 h-4 ml-auto transition-transform duration-300" :class="{ 'rotate-180': isDropdownOpen }" />
							</div>
						</Button>
					</DropdownMenuTrigger>
				</div>
				<div class="flex items-center gap-2 mr-4">
					<RcIcon :name="viewItems.find((item) => item.id === currentView)?.icon || ''" />
					{{ viewItems.find((item) => item.id === currentView)?.label || "" }}
				</div>
				<DropdownMenuContent class="w-56" align="start">
					<DropdownMenuGroup>
						<DropdownMenuItem v-for="item in viewItems" :key="item.id" @click="changeView(item.id)">
							<span class="flex items-center gap-2">
								<RcIcon :name="item.icon" />
								{{ item.label }}
							</span>
							<TooltipProvider>
								<Tooltip>
									<TooltipTrigger as-child>
										<DropdownMenuShortcut @click.stop.prevent="toggleFavorite(item.id)">
											<RcIcon name="star-unselected" v-if="!item.isFavorite" class="animated-star" />
											<RcIcon name="star-selected" v-if="item.isFavorite" class="animated-star" />
										</DropdownMenuShortcut>
									</TooltipTrigger>
									<TooltipContent>
										<p>{{ item.isFavorite ? "Remove from favorites" : "Add to favorites" }}</p>
									</TooltipContent>
								</Tooltip>
							</TooltipProvider>
						</DropdownMenuItem>
					</DropdownMenuGroup>
				</DropdownMenuContent>
			</DropdownMenu>
		</div>

		<div>
			<!-- Create a wrapper div that will only show one of these components -->
			<template v-if="currentViewExists">
				<Devices v-if="currentView === 'devices'"></Devices>
				<CommandGroups v-if="currentView === 'commandgroups'"></CommandGroups>
				<Command v-if="currentView === 'commands'"></Command>
				<Template v-if="currentView === 'templates'"></Template>
				<Vendors v-if="currentView === 'vendors'"></Vendors>
				<Tags v-if="currentView === 'tags'"></Tags>
			</template>

			<!-- Error message when view doesn't exist -->
			<div v-else class="flex flex-col h-full gap-1 text-center">
				<div class="flex items-center justify-between p-4">
					<div class="flex items-center">
						<p class="text-lg text-red-500 font-semibold">{{ currentView }} view not found</p>
					</div>
				</div>
			</div>
		</div>
	</main>
</template>

<style scoped>
/* Optional: Add a subtle bounce to the rotation animation */
@keyframes bounce {
	0%,
	100% {
		transform: rotate(var(--rotation)) scale(1);
	}
	50% {
		transform: rotate(var(--rotation)) scale(1.1);
	}
}

.bounce-rotate {
	animation: bounce 0.3s ease-in-out;
}
</style>