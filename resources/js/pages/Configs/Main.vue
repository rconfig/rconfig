<script setup>
import ConfigCompare from "@/pages/Configs/ConfigCompare.vue";
import ConfigSearch from "@/pages/Configs/ConfigSearch.vue";
import ConfigsTable from "@/pages/Configs/ConfigsTable.vue";
import ConfigReportsTable from "@/pages/Configs/ConfigReportsTable.vue";
import NavPills from "@/pages/Shared/Buttons/NavPills.vue";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { useConfigs } from "@/pages/Configs/useConfigs";

const {
	// State
	configsId,
	statusIdParam,
	currentView,
	viewItems,
	navPillsItems,

	// Methods
	changeView,
	handleNavSelection,
	toggleFavorite,
} = useConfigs();
</script>

<template>
	<main class="flex flex-col flex-1 gap-2 dark:bg-rcgray-900">
		<div class="flex flex-row items-center justify-between h-12 gap-3 px-5 border-t border-b">
			<div class="flex justify-start items-center">
				<NavPills :items="navPillsItems" v-model="currentView" persist-key="inventorySelectedView" @select="handleNavSelection" />
			</div>

			<!-- Star/favorite icon -->
			<TooltipProvider>
				<Tooltip v-for="item in viewItems" :key="item.id">
					<TooltipTrigger as-child v-if="currentView === item.id">
						<div @click.stop.prevent="toggleFavorite(item.id)" class="cursor-pointer">
							<RcIcon :name="item.isFavorite.value ? 'star-selected' : 'star-unselected'" />
						</div>
					</TooltipTrigger>
					<TooltipContent>
						<p>{{ item.isFavorite.value ? "Remove from favorites" : "Add " + currentView + " to favorites" }}</p>
					</TooltipContent>
				</Tooltip>
			</TooltipProvider>
		</div>

		<div>
			<keep-alive>
				<component 
					:is="currentView === 'configs' ? ConfigsTable : currentView === 'configsearch' ? ConfigSearch : currentView === 'configcompare' ? ConfigCompare : currentView === 'configreport' ? ConfigReportsTable : null" 
					:configsId="configsId" 
					:statusId="statusIdParam" 
					:key="currentView">
					<template v-if="['configsearch', 'configcompare'].includes(currentView)" #default>
						<h1 class="text-2xl text-muted-foreground">
							{{ currentView === 'configsearch' ? 'Config Search' : currentView === 'configcompare' ? 'Config Compare' : 'Config Reports' }}
						</h1>
					</template>
				</component>
			</keep-alive>
		</div>
	</main>
</template>

<style scoped>
/* Fade transition for content */
.fade-enter-active,
.fade-leave-active {
	transition: opacity 0.15s ease;
}

.fade-enter-from,
.fade-leave-to {
	opacity: 0;
}
</style>