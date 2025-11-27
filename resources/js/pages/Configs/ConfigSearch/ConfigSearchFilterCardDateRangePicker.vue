<script setup lang="ts">
import type { DateRange } from "radix-vue";
import { CalendarDate, DateFormatter, getLocalTimeZone } from "@internationalized/date";
import { Calendar } from "lucide-vue-next";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { RangeCalendar } from "@/components/ui/range-calendar";
import { cn } from "@/lib/utils";
import { ref, watch, onMounted, computed } from "vue";
import { useDebounceFn } from "@vueuse/core";

interface Props {
	// Set to true to start with default date range (last 7 days)
	useDefaultRange?: boolean;
	// Custom placeholder text when no date is selected
	placeholder?: string;
	// Custom width class
	width?: string;
	// Initial date range (overrides useDefaultRange)
	initialRange?: DateRange;
}

const props = withDefaults(defineProps<Props>(), {
	useDefaultRange: true,
	placeholder: "Pick a date range",
	width: "w-[40px] lg:w-[280px]",
	initialRange: undefined,
});

const emit = defineEmits(["dateChange"]);

const df = new DateFormatter("en-US", {
	dateStyle: "medium",
});

const currentYear = new Date().getFullYear();
const currentMonth = new Date().getMonth() + 1;
const currentDay = new Date().getDate();

// Create default range (last 7 days)
const createDefaultRange = (): DateRange => ({
	start: new CalendarDate(currentYear, currentMonth, currentDay).subtract({ days: 7 }),
	end: new CalendarDate(currentYear, currentMonth, currentDay),
});

// Initialize value based on props
const getInitialValue = (): DateRange | undefined => {
	if (props.initialRange) {
		return props.initialRange;
	}
	if (props.useDefaultRange) {
		return createDefaultRange();
	}
	return undefined;
};

const value = ref(getInitialValue()) as Ref<DateRange | undefined>;

const debouncedEmit = useDebounceFn((newValue) => {
	emit("dateChange", newValue);
}, 500);

watch(value, (newValue) => {
	debouncedEmit(newValue);
});

onMounted(() => {
	if (value.value) {
		emit("dateChange", value.value);
	}
});

// Computed property for button text
const buttonText = computed(() => {
	if (!value.value?.start) {
		return props.placeholder;
	}

	if (value.value.end) {
		return `${df.format(value.value.start.toDate(getLocalTimeZone()))} - ${df.format(value.value.end.toDate(getLocalTimeZone()))}`;
	}

	return df.format(value.value.start.toDate(getLocalTimeZone()));
});

// Method to reset to blank state
const clearSelection = () => {
	value.value = undefined;
};

// Method to set to default range
const setDefaultRange = () => {
	value.value = createDefaultRange();
	emit("dateChange", value.value);
};

// Expose methods for parent components
defineExpose({
	clearSelection,
	setDefaultRange,
});
</script>

<template>
	<Popover>
		<PopoverTrigger as-child>
			<Button variant="outline" :class="cn(props.width, 'justify-start text-left font-normal rounded-xl px-2 py-1 h-fit', !value?.start && 'text-muted-foreground')">
				<Calendar class="w-4 h-4" size="16" />
				<div class="hidden lg:inline-flex ml-2">
					{{ buttonText }}
				</div>
			</Button>
		</PopoverTrigger>
		<PopoverContent class="w-auto p-0">
			<RangeCalendar
				v-model="value"
				initial-focus
				:number-of-months="2"
				@update:start-value="
					(startDate) => {
						if (value) {
							value.start = startDate;
						} else {
							value = { start: startDate, end: undefined };
						}
					}
				"
			/>
		</PopoverContent>
	</Popover>
</template>
