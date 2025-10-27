<script setup lang="ts">
import { ref, watch, computed } from "vue";
import { RangeCalendar } from "@/components/ui/range-calendar";
import { Button } from "@/components/ui/button";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { CalendarDays, X } from "lucide-vue-next";

const emit = defineEmits(["update:startDate", "update:endDate", "clear"]);

const props = defineProps({
	startDate: {
		type: [String, Date, null],
		default: null,
	},
	endDate: {
		type: [String, Date, null],
		default: null,
	},
});

const open = ref(false);
// Initialize range with null instead of undefined to avoid RangeCalendar issues
const range = ref({
	start: props.startDate ? new Date(props.startDate) : null,
	end: props.endDate ? new Date(props.endDate) : null,
});

// Watch for prop changes
watch(
	() => [props.startDate, props.endDate],
	([newStart, newEnd]) => {
		range.value = {
			start: newStart ? new Date(newStart) : null,
			end: newEnd ? new Date(newEnd) : null,
		};
	}
);

// Helper function to format date as YYYY-MM-DD with proper validation
const formatDate = (date) => {
	if (!date) return null;

	// Ensure we have a valid Date object
	const dateObj = date instanceof Date ? date : new Date(date);

	// Check if the date is valid
	if (isNaN(dateObj.getTime())) {
		console.warn("Invalid date provided to formatDate:", date);
		return null;
	}

	return dateObj.toISOString().split("T")[0];
};

// Helper function to format date for display with proper validation
const formatDisplayDate = (date, options = {}) => {
	if (!date) return "";

	// Ensure we have a valid Date object
	const dateObj = date instanceof Date ? date : new Date(date);

	// Check if the date is valid
	if (isNaN(dateObj.getTime())) {
		console.warn("Invalid date provided to formatDisplayDate:", date);
		return "";
	}

	return dateObj.toLocaleDateString("en-US", {
		month: "short",
		day: "numeric",
		year: options.includeYear ? "numeric" : undefined,
		...options,
	});
};

// Watch for range changes and emit to parent
watch(
	range,
	(newRange) => {
		if (newRange.start) {
			const formattedStart = formatDate(newRange.start);
			if (formattedStart) {
				emit("update:startDate", formattedStart);
			}
		} else {
			emit("update:startDate", null);
		}

		if (newRange.end) {
			const formattedEnd = formatDate(newRange.end);
			if (formattedEnd) {
				emit("update:endDate", formattedEnd);
			}
		} else {
			emit("update:endDate", null);
		}
	},
	{ deep: true }
);

const displayText = computed(() => {
	if (range.value.start && range.value.end) {
		const startText = formatDisplayDate(range.value.start);
		const endText = formatDisplayDate(range.value.end, { includeYear: true });
		return startText && endText ? `${startText} - ${endText}` : "Date Range";
	} else if (range.value.start) {
		const startText = formatDisplayDate(range.value.start, { includeYear: true });
		return startText ? `From ${startText}` : "Date Range";
	} else if (range.value.end) {
		const endText = formatDisplayDate(range.value.end, { includeYear: true });
		return endText ? `Until ${endText}` : "Date Range";
	}
	return "Date Range";
});

const hasSelection = computed(() => {
	return range.value.start || range.value.end;
});

function clearRange() {
	range.value = { start: null, end: null };
	emit("clear");
	open.value = false;
}

function selectToday() {
	const today = new Date();
	range.value = { start: today, end: today };
	open.value = false;
}

function selectLast7Days() {
	const end = new Date();
	const start = new Date();
	start.setDate(start.getDate() - 7);
	range.value = { start, end };
	open.value = false;
}

function selectLast30Days() {
	const end = new Date();
	const start = new Date();
	start.setDate(start.getDate() - 30);
	range.value = { start, end };
	open.value = false;
}
</script>

<template>
	<Popover v-model:open="open">
		<PopoverTrigger asChild>
			<Button variant="ghost" :class="[' ml-2 flex items-center justify-center w-full px-2 py-1 border rounded-xl whitespace-nowrap h-fit', hasSelection ? 'bg-blue-600/10 text-blue-200 border-blue-300' : 'bg-rcgray-700 text-rcgray-400']">
				<CalendarDays class="w-4 h-4 lg:mr-2" />
				<div class="hidden lg:inline-flex">
					{{ displayText }}
				</div>
				<X v-if="hasSelection" class="w-3 h-3 ml-2 hover:bg-blue-200 rounded" @click.stop="clearRange" />
			</Button>
		</PopoverTrigger>
		<PopoverContent class="w-auto p-0" align="start">
			<div class="p-4">
				<!-- Quick Select Buttons -->
				<div class="flex flex-wrap gap-2 mb-4">
					<Button variant="outline" size="sm" @click="selectToday">
						Today
					</Button>
					<Button variant="outline" size="sm" @click="selectLast7Days">
						Last 7 days
					</Button>
					<Button variant="outline" size="sm" @click="selectLast30Days">
						Last 30 days
					</Button>
					<Button variant="outline" size="sm" @click="clearRange">
						Clear
					</Button>
				</div>
				<!-- Calendar Component -->
				<!-- <Calendar v-model:range="range" mode="range" :number-of-months="2" class="rounded-md border" /> -->
				<RangeCalendar v-model="range" class="rounded-md border" />
			</div>
		</PopoverContent>
	</Popover>
</template>
