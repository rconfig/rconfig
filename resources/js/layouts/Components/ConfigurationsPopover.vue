<script setup>
import Loading from "@/pages/Shared/Loaders/Loading.vue";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from "@/components/ui/sheet";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { Button } from "@/components/ui/button";
import { ref, onMounted, watch, computed } from "vue";
import { useConfigs } from "@/pages/Configs/useConfigs";

const props = defineProps({
	boundaryEl: { type: [Object, HTMLElement], default: null },
	panelWidth: { type: Number, default: 260 },
});

const isLoading = ref(true);
const popoverOpen = ref(false);

const {
	viewItems,
	// Methods
	changeViewSidePopover,
	toggleFavorite,
} = useConfigs();

onMounted(() => {});

watch(
	() => viewItems,
	(newValue) => {
		if (newValue) {
			isLoading.value = false;
		}
	},
	{ immediate: true }
);

function navigate(id) {
	changeViewSidePopover(id);
	close();
}

function close() {
	popoverOpen.value = false;
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
						<RcIcon name="config-tools" class="w-5 h-5" />
						<span class="ml-2">Configuration Tools</span>
					</div>
				</SheetTitle>
			</SheetHeader>

			<div class="mt-4 grid gap-4">
				<p class="text-xs text-muted-foreground">
					Select a configuration tool to manage your network configurations
				</p>

				<div class="flex flex-col">
					<template v-if="isLoading">
						<div class="flex items-center justify-center w-full py-4">
							<Loading />
						</div>
					</template>

					<div v-if="!isLoading" v-for="item in viewItems" :key="item.id" class="flex items-center justify-between group rounded-md transition-colors hover:bg-muted/30">
						<Button variant="ghost" class="w-full flex justify-start mr-2 px-1 focus:outline-none focus:ring-0" @click="navigate(item.id)" tabindex="-1">
							<span class="flex gap-2 items-center">
								<RcIcon :name="item.icon" class="w-4 h-4" />
								<span class="text-sm">{{ item.label }}</span>
							</span>
						</Button>

						<TooltipProvider>
							<Tooltip>
								<TooltipTrigger as-child>
									<span @click.stop.prevent="toggleFavorite(item.id)" class="p-1 rounded hover:bg-muted/50 transition-colors">
										<RcIcon name="star-unselected" v-if="!item.isFavorite.value" class="animated-star text-muted-foreground hover:text-yellow-400 w-4 h-4" />
										<RcIcon name="star-selected" v-else class="animated-star text-yellow-500 w-4 h-4" />
									</span>
								</TooltipTrigger>
								<TooltipContent side="right">
									<p>{{ item.isFavorite.value ? "Remove from favorites" : "Add to favorites" }}</p>
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
								<RcIcon name="config-tools" class="w-5 h-5" />
								<span class="ml-2">Configuration Tools</span>
							</div>
							<kbd class="rc-kdb-class">ESC</kbd>
						</div>
					</h4>
					<p class="text-xs text-muted-foreground">
						Select a configuration tool to manage your network configurations
					</p>
				</div>

				<div class="flex flex-col">
					<template v-if="isLoading">
						<div class="flex items-center justify-center w-full py-4">
							<Loading />
						</div>
					</template>

					<div v-if="!isLoading" v-for="item in viewItems" :key="item.id" class="flex items-center justify-between group rounded-md transition-colors hover:bg-muted/30">
						<Button variant="ghost" class="w-full flex justify-start mr-2 px-1 focus:outline-none focus:ring-0" @click="navigate(item.id)" tabindex="-1">
							<span class="flex gap-2 items-center">
								<RcIcon :name="item.icon" class="w-4 h-4" />
								<span class="text-sm">{{ item.label }}</span>
							</span>
						</Button>

						<TooltipProvider>
							<Tooltip>
								<TooltipTrigger as-child>
									<span @click.stop.prevent="toggleFavorite(item.id)" class="p-1 rounded hover:bg-muted/50 transition-colors">
										<RcIcon name="star-unselected" v-if="!item.isFavorite.value" class="animated-star text-muted-foreground hover:text-yellow-400 w-4 h-4" />
										<RcIcon name="star-selected" v-else class="animated-star text-yellow-500 w-4 h-4" />
									</span>
								</TooltipTrigger>
								<TooltipContent side="right">
									<p>{{ item.isFavorite.value ? "Remove from favorites" : "Add to favorites" }}</p>
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
</style>