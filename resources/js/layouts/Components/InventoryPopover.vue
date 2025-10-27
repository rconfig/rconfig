<script setup>
import Loading from "@/pages/Shared/Loaders/Loading.vue";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from "@/components/ui/sheet";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { Button } from "@/components/ui/button";
import { Separator } from "@/components/ui/separator";
import { ref, onMounted, watch, computed } from "vue";
import { useInventory } from "@/pages/Inventory/useInventory";
import { ListRestart, GripVertical } from "lucide-vue-next";

const props = defineProps({
	boundaryEl: { type: [Object, HTMLElement], default: null },
	panelWidth: { type: Number, default: 260 },
});

const isLoading = ref(true);
const isDragging = ref(false);
const popoverOpen = ref(false);

const {
	viewItems,
	sortDirection,
	// Methods
	changeView,
	toggleFavorite,
	sortAlphabetically,
	resetToDefaultOrder,
	handleDragStart,
	handleDragEnd,
	handleDragOver,
	handleDrop,
} = useInventory();

onMounted(() => {});

watch(
	() => viewItems,
	(newValue) => {
		if (newValue) isLoading.value = false;
	},
	{ immediate: true }
);

function navigate(id) {
	changeView(id);
	close();
}

function close() {
	popoverOpen.value = false;
}

function onDragStart(event, itemId) {
	isDragging.value = true;
	handleDragStart(event, itemId);
}

function onDragEnd(event) {
	isDragging.value = false;
	handleDragEnd(event);
}

function onSortAlphabetically() {
	sortAlphabetically();
}

function onResetOrder() {
	resetToDefaultOrder();
}

// Switch to a drawer when the side panel is very narrow
const useSheet = computed(() => props.panelWidth < 220);

// Floating-UI positioning config for bounded popover
const positioning = computed(() => ({
	strategy: "fixed",
	placement: "right-start",
	middleware: [
		{ name: "offset", options: { mainAxis: 8 } },
		{ name: "flip" },
		{ name: "shift", options: { padding: 8, boundary: props.boundaryEl || undefined } },
		{
			name: "size",
			options: {
				apply({ elements, availableWidth }) {
					Object.assign(elements.floating.style, {
						maxWidth: Math.min(availableWidth, 360) + "px",
					});
				},
			},
		},
	],
	whileElementsMounted: "autoUpdate",
}));
</script>

<template>
	<!-- Drawer fallback for very narrow panels -->
	<Sheet v-if="useSheet" v-model:open="popoverOpen">
		<SheetTrigger as-child>
			<slot />
		</SheetTrigger>
		<SheetContent side="left" class="w-80 sm:w-96">
			<SheetHeader>
				<SheetTitle>
					<div class="flex items-center">
						<RcIcon name="inventory" class="w-5 h-5" />
						<span class="ml-2">Inventory</span>
					</div>
				</SheetTitle>
			</SheetHeader>

			<div class="mt-4 grid gap-4">
				<p class="text-xs text-muted-foreground">Select an inventory view to manage your devices and assets</p>

				<!-- Sort Controls -->
				<div class="flex items-center justify-between">
					<div class="flex items-center gap-2">
						<TooltipProvider>
							<Tooltip>
								<TooltipTrigger as-child>
									<Button variant="ghost" size="sm" @click="onSortAlphabetically" class="h-8 px-2 rc-btn-shadow">
										<RcIcon name="sort" :sortParam="sortDirection === 'asc' ? 'sort-alpha' : '-sort-alpha'" field="id" />
									</Button>
								</TooltipTrigger>
								<TooltipContent>
									<p>{{ sortDirection === "asc" ? "Sort alphabetically (A-Z)" : "Sort alphabetically (Z-A)" }}</p>
								</TooltipContent>
							</Tooltip>
						</TooltipProvider>

						<TooltipProvider>
							<Tooltip>
								<TooltipTrigger as-child>
									<Button variant="ghost" size="sm" @click="onResetOrder" class="h-8 px-2 rc-btn-shadow">
										<ListRestart class="w-4 h-4" />
									</Button>
								</TooltipTrigger>
								<TooltipContent>
									<p>Reset to default order</p>
								</TooltipContent>
							</Tooltip>
						</TooltipProvider>
					</div>
				</div>

				<Separator />

				<div class="flex flex-col">
					<template v-if="isLoading">
						<div class="flex items-center justify-center w-full py-4">
							<Loading />
						</div>
					</template>

					<div
						v-if="!isLoading"
						v-for="item in viewItems"
						:key="item.id"
						class="flex items-center justify-between group rounded-md transition-colors"
						:class="{
							'bg-muted/50': isDragging,
							'hover:bg-muted/30': !isDragging,
						}"
						draggable="true"
						@dragstart="onDragStart($event, item.id)"
						@dragend="onDragEnd"
						@dragover="handleDragOver"
						@drop="handleDrop($event, item.id)"
					>
						<!-- Drag Handle -->
						<div class="flex items-center cursor-grab active:cursor-grabbing opacity-0 group-hover:opacity-100 transition-opacity">
							<GripVertical class="w-3 h-3 text-muted-foreground" />
						</div>

						<!-- Navigation Button -->
						<Button variant="ghost" class="flex-1 justify-start mr-2 px-2 focus:outline-none focus:ring-0" @click="navigate(item.id)" tabindex="-1">
							<span class="flex items-center gap-2">
								<RcIcon :name="item.icon" class="w-4 h-4" />
								<span class="text-sm">{{ item.label }}</span>
							</span>
						</Button>

						<!-- Favorite Toggle -->
						<TooltipProvider>
							<Tooltip>
								<TooltipTrigger as-child>
									<button @click.stop.prevent="toggleFavorite(item.id)" class="p-1 rounded hover:bg-muted/50 transition-colors">
										<RcIcon
											:name="item.isFavorite ? 'star-selected' : 'star-unselected'"
											class="w-4 h-4 animated-star"
											:class="{
												'text-yellow-500': item.isFavorite,
												'text-muted-foreground hover:text-yellow-400': !item.isFavorite,
											}"
										/>
									</button>
								</TooltipTrigger>
								<TooltipContent side="right">
									<p>{{ item.isFavorite ? "Remove from favorites" : "Add to favorites" }}</p>
								</TooltipContent>
							</Tooltip>
						</TooltipProvider>
					</div>
				</div>
			</div>
		</SheetContent>
	</Sheet>

	<!-- Properly bounded popover for normal widths -->
	<Popover v-else v-model:open="popoverOpen" :positioning="positioning">
		<PopoverTrigger as-child>
			<slot />
		</PopoverTrigger>

		<PopoverContent class="w-80 p-0 overflow-hidden" side="right" align="start" :side-offset="panelWidth < 260 ? 0 : 26" :avoid-collisions="true" :collision-padding="8" :style="{ maxHeight: 'min(70vh, 640px)' }">
			<div class="grid gap-4 p-4">
				<div class="space-y-2">
					<h4 class="flex justify-start w-full font-medium leading-none">
						<div class="flex items-center justify-between w-full">
							<div class="flex items-center">
								<RcIcon name="inventory" class="w-5 h-5" />
								<span class="ml-2">Inventory</span>
							</div>
							<kbd class="rc-kdb-class">ESC</kbd>
						</div>
					</h4>
					<p class="text-xs text-muted-foreground">Select an inventory view to manage your devices and assets</p>
				</div>

				<!-- Sort Controls -->
				<div class="flex items-center justify-between">
					<div class="flex items-center gap-2">
						<TooltipProvider>
							<Tooltip>
								<TooltipTrigger as-child>
									<Button variant="ghost" size="sm" @click="onSortAlphabetically" class="h-8 px-2 rc-btn-shadow">
										<RcIcon name="sort" :sortParam="sortDirection === 'asc' ? 'sort-alpha' : '-sort-alpha'" field="id" />
									</Button>
								</TooltipTrigger>
								<TooltipContent>
									<p>{{ sortDirection === "asc" ? "Sort alphabetically (A-Z)" : "Sort alphabetically (Z-A)" }}</p>
								</TooltipContent>
							</Tooltip>
						</TooltipProvider>

						<TooltipProvider>
							<Tooltip>
								<TooltipTrigger as-child>
									<Button variant="ghost" size="sm" @click="onResetOrder" class="h-8 px-2 rc-btn-shadow">
										<ListRestart class="w-4 h-4" />
									</Button>
								</TooltipTrigger>
								<TooltipContent>
									<p>Reset to default order</p>
								</TooltipContent>
							</Tooltip>
						</TooltipProvider>
					</div>
				</div>

				<Separator />

				<div class="flex flex-col">
					<template v-if="isLoading">
						<div class="flex items-center justify-center w-full py-4">
							<Loading />
						</div>
					</template>

					<div
						v-if="!isLoading"
						v-for="item in viewItems"
						:key="item.id"
						class="flex items-center justify-between group rounded-md transition-colors"
						:class="{
							'bg-muted/50': isDragging,
							'hover:bg-muted/30': !isDragging,
						}"
						draggable="true"
						@dragstart="onDragStart($event, item.id)"
						@dragend="onDragEnd"
						@dragover="handleDragOver"
						@drop="handleDrop($event, item.id)"
					>
						<!-- Drag Handle -->
						<div class="flex items-center cursor-grab active:cursor-grabbing opacity-0 group-hover:opacity-100 transition-opacity">
							<GripVertical class="w-3 h-3 text-muted-foreground" />
						</div>

						<!-- Navigation Button -->
						<Button variant="ghost" class="flex-1 justify-start mr-2 px-2 focus:outline-none focus:ring-0" @click="navigate(item.id)" tabindex="-1">
							<span class="flex items-center gap-2">
								<RcIcon :name="item.icon" class="w-4 h-4" />
								<span class="text-sm">{{ item.label }}</span>
							</span>
						</Button>

						<!-- Favorite Toggle -->
						<TooltipProvider>
							<Tooltip>
								<TooltipTrigger as-child>
									<button @click.stop.prevent="toggleFavorite(item.id)" class="p-1 rounded hover:bg-muted/50 transition-colors">
										<RcIcon
											:name="item.isFavorite ? 'star-selected' : 'star-unselected'"
											class="w-4 h-4 animated-star"
											:class="{
												'text-yellow-500': item.isFavorite,
												'text-muted-foreground hover:text-yellow-400': !item.isFavorite,
											}"
										/>
									</button>
								</TooltipTrigger>
								<TooltipContent side="right">
									<p>{{ item.isFavorite ? "Remove from favorites" : "Add to favorites" }}</p>
								</TooltipContent>
							</Tooltip>
						</TooltipProvider>
					</div>
				</div>
			</div>
		</PopoverContent>
	</Popover>
</template>

<style scoped>
.animated-star {
	transition: all 0.2s ease-in-out;
}
.animated-star:hover {
	transform: scale(1.1);
}
/* Drag feedback styles */
[draggable="true"]:active {
	cursor: grabbing !important;
}
.group:hover [draggable="true"] {
	cursor: grab;
}
</style>