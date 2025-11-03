<script setup>
/**
 * DeviceMultiSelect Component
 *
 * High-performance device selection component optimized for large datasets (100+ records).
 *
 * PERFORMANCE FEATURES:
 * - Infinite scroll pagination (loads 50 records at a time)
 * - Debounced server-side search (300ms delay, 2+ character minimum)
 * - Client-side filtering for already loaded data
 * - Only fetches required fields (id, device_name) from API
 * - Prevents duplicate records when appending paginated data
 *
 * BACKEND REQUIREMENTS:
 * - API endpoint: /api/get-devices-filter-list
 * - Expected response format: Laravel paginated response with data.data array
 * - Required fields in response: { id, device_name }
 * - Supports query parameters: page, per_page, sort, filter[device_name]
 * - Database index recommended on device_name column for performance
 *
 * USAGE:
 * - Single select: <DeviceMultiSelect v-model="selectedDevice" single-select />
 * - Multi select: <DeviceMultiSelect v-model="selectedDevices" />
 * - Custom placeholder: <DeviceMultiSelect v-model="devices" placeholder="Choose devices" />
 *
 * SEARCH BEHAVIOR:
 * - 0-1 chars: Shows all loaded devices (no server call)
 * - 2+ chars: Triggers debounced server-side search
 * - Empty search: Resets to initial paginated load
 *
 * INFINITE SCROLL:
 * - Triggers when user scrolls within 100px of bottom
 * - Disabled during search (search shows all matching results)
 * - Maintains alphabetical order via backend sorting
 */
import axios from "axios";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { ScrollArea } from "@/components/ui/scroll-area";
import { X } from "lucide-vue-next";
import { onMounted, ref, computed, watch, nextTick } from "vue";
import { useMultiSelect } from "./useMultiSelect.js";
import { useDebounceFn } from "@vueuse/core";

const emit = defineEmits(["update:modelValue"]);
const devices = ref([]);
const isLoading = ref(false);
const isLoadingMore = ref(false);
const hasMoreData = ref(true);
const currentPage = ref(1);
const totalRecords = ref(0);
const searchTerm = ref("");
const scrollContainer = ref(null);

const props = defineProps({
	modelValue: {
		type: [Array, Object],
		required: true,
	},
	singleSelect: {
		type: Boolean,
		default: false,
	},
	placeholder: {
		type: String,
		default: "Select devices",
	},
});

const { selectedItems: selectedDevs, open, filteredItems: filteredDevices, selectItem, deleteItem: deleteItemById } = useMultiSelect({
	items: devices,
	modelValue: props.modelValue,
	singleSelect: props.singleSelect,
	displayField: "device_name",
	searchFields: ["device_name"],
	emit,
});

// For client-side search when we have search term
const clientFilteredDevices = computed(() => {
	if (!searchTerm.value) return devices.value;
	const term = searchTerm.value.toLowerCase();
	return devices.value.filter((device) => device.device_name.toLowerCase().includes(term));
});

// Use server-side search results when searching, otherwise use all loaded devices
const displayedDevices = computed(() => {
	return searchTerm.value ? clientFilteredDevices.value : devices.value;
});

// Custom deleteItem function that uses device_name instead of ID
function deleteItem(itemName) {
	const item = selectedDevs.value.find((dev) => dev.device_name === itemName);
	if (item) {
		deleteItemById(item.id);
	}
}

// Debounced search function for server-side search
const debouncedSearch = useDebounceFn(async (term) => {
	if (term.length > 0) {
		// For search, fetch from server with filter
		await fetchDevices(1, term, true);
	} else {
		// Reset to initial load
		resetToInitialLoad();
	}
}, 300);

// Watch search term and trigger debounced search
watch(searchTerm, (newTerm) => {
	if (newTerm.length >= 2) {
		debouncedSearch(newTerm);
	} else if (newTerm.length === 0) {
		debouncedSearch(newTerm);
	}
});

async function fetchDevices(page = 1, search = "", replaceData = false) {
	if (page === 1) {
		isLoading.value = true;
	} else {
		isLoadingMore.value = true;
	}

	try {
		const params = {
			page,
			per_page: 50,
			sort: "device_name",
		};

		if (search) {
			params["filter[device_name]"] = search;
		}

		const response = await axios.get("/api/get-devices-filter-list", { params });
		const newDevices = response.data.data;

		if (replaceData || page === 1) {
			devices.value = newDevices;
		} else {
			// Avoid duplicates when appending
			const existingIds = new Set(devices.value.map((d) => d.id));
			const uniqueNewDevices = newDevices.filter((d) => !existingIds.has(d.id));
			devices.value = [...devices.value, ...uniqueNewDevices];
		}

		currentPage.value = response.data.current_page;
		hasMoreData.value = response.data.current_page < response.data.last_page;
		totalRecords.value = response.data.total;
	} catch (error) {
		console.error("Error fetching devices:", error);
	} finally {
		isLoading.value = false;
		isLoadingMore.value = false;
	}
}

function resetToInitialLoad() {
	devices.value = [];
	currentPage.value = 1;
	hasMoreData.value = true;
	fetchDevices(1);
}

// Infinite scroll handler
async function handleScroll(event) {
	if (searchTerm.value) return; // Don't infinite scroll during search
	if (isLoadingMore.value || !hasMoreData.value) return;

	const { scrollTop, scrollHeight, clientHeight } = event.target;
	const threshold = 100; // Load more when 100px from bottom

	if (scrollTop + clientHeight >= scrollHeight - threshold) {
		await fetchDevices(currentPage.value + 1);
	}
}

// Setup scroll listener
function setupScrollListener() {
	nextTick(() => {
		const scrollArea = scrollContainer.value?.querySelector("[data-radix-scroll-area-viewport]") || scrollContainer.value?.querySelector(".scroll-area-viewport") || scrollContainer.value?.querySelector("[data-scroll-area-viewport]");

		if (scrollArea) {
			scrollArea.addEventListener("scroll", handleScroll);
		} else {
			if (scrollContainer.value) {
				scrollContainer.value.addEventListener("scroll", handleScroll);
			}
		}
	});
}

onMounted(() => {
	fetchDevices(1);
	setupScrollListener();
});
</script>

<template>
	<Popover>
		<PopoverTrigger class="flex items-center">
			<Button variant="ghost" class="flex items-center justify-start p-1 pl-2 border h-fit gap-1 min-w-2xl" :class="selectedDevs.length === 0 ? 'text-muted-foreground' : ''">
				<RcIcon name="device" class="flex-shrink-0 mx-2" />
				<span v-if="selectedDevs && selectedDevs.length === 0" class="flex-1 text-left mr-2">
					Select devices
				</span>
				<div v-else class="flex flex-wrap gap-1 flex-1 overflow-hidden">
					<!-- Show first 3 devices -->
					<span v-for="(dev, index) in selectedDevs.slice(0, 3)" :key="dev.id" class="relative group flex-shrink-0">
						<span class="flex items-center text-xs font-medium px-2.5 py-0.5 rounded-xl border whitespace-nowrap max-w-[150px]">
							<span class="truncate">{{ dev.device_name }}</span>
							<X size="10" class="ml-1 cursor-pointer text-rcgray-300 hover:text-white flex-shrink-0" @click.stop="deleteItem(dev.device_name)" />
						</span>
					</span>
					<!-- Show count if there are more than 3 selected -->
					<span v-if="selectedDevs.length > 3" class="flex items-center text-xs font-medium px-2.5 py-0.5 rounded-xl border bg-muted text-muted-foreground flex-shrink-0"> +{{ selectedDevs.length - 3 }} more </span>
				</div>
			</Button>
		</PopoverTrigger>
		<PopoverContent side="bottom" align="start" class="col-span-3 p-0">
			<div class="relative items-center w-full">
				<Input id="search" type="text" v-model="searchTerm" autocomplete="off" placeholder="Search..." class="pl-10 border-none focus:outline-none focus-visible:ring-0 text-muted-foreground font-inter" />
				<span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
					<RcIcon name="search" />
				</span>
			</div>
			<Separator />

			<ScrollArea ref="scrollContainer" class="h-64">
				<div class="py-1">
					<RcIcon name="three-dots-loading" class="w-8 h-8 mx-auto my-4 text-muted-foreground" v-if="isLoading" />
					<template v-else>
						<div v-for="dev in displayedDevices" :key="dev.id" class="w-full p-1 pl-2 my-1 text-xs rounded-lg hover:bg-rcgray-600 cursor-pointer" @click="selectItem(dev)">
							<span class="cursor-default text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border">
								{{ dev.device_name }}
							</span>
						</div>

						<!-- Manual Load More Button -->
						<div v-if="hasMoreData && !searchTerm" class="text-center rc-text-xs-muted">
							<Button @click="fetchDevices(currentPage + 1)" :disabled="isLoadingMore" variant="outline" class="load-more-btn my-4">
								<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
									<path d="M12 8v8m0 0l4-4m-4 4l-4-4" />
									<path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
								</svg>
								{{ isLoadingMore ? "Loading..." : `Load More (${devices.length}/${totalRecords})` }}
							</Button>
						</div>

						<!-- Loading more indicator -->
						<div v-if="isLoadingMore" class="flex justify-center py-2">
							<RcIcon name="three-dots-loading" class="w-6 h-6 text-muted-foreground" />
						</div>

						<!-- End of data indicator -->
						<div v-else-if="!hasMoreData && !searchTerm && devices.length > 0" class="text-center py-2 rc-text-xs-muted">No more devices to load ({{ devices.length }} total)</div>

						<!-- No search results -->
						<div v-else-if="searchTerm && displayedDevices.length === 0" class="text-center py-4 rc-text-xs-muted">
							No devices found
						</div>
					</template>
				</div>
			</ScrollArea>
		</PopoverContent>
	</Popover>
</template>