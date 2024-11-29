<script setup lang="ts">
import type { DateRange } from 'radix-vue';
import { Button } from '@/components/ui/button';
import { CalendarDate, DateFormatter, getLocalTimeZone } from '@internationalized/date';
import { CalendarIcon } from '@radix-icons/vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { RangeCalendar } from '@/components/ui/range-calendar';
import { cn } from '@/lib/utils';
import { ref, watch, onMounted } from 'vue';
import { useDebounceFn } from '@vueuse/core';

const emit = defineEmits(['dateChange']);

const df = new DateFormatter('en-US', {
  dateStyle: 'medium'
});

const currentYear = new Date().getFullYear();
const currentMonth = new Date().getMonth() + 1;
const currentDay = new Date().getDate();

const value = ref({
  start: new CalendarDate(currentYear, currentMonth, currentDay).subtract({ days: 7 }),
  end: new CalendarDate(currentYear, currentMonth, currentDay)
}) as Ref<DateRange>;

const debouncedEmit = useDebounceFn(newValue => {
  emit('dateChange', newValue);
}, 500);

watch(value, newValue => {
  // just wait for the user to stop
  debouncedEmit(newValue);
});

onMounted(() => {
  emit('dateChange', value.value);
});
</script>

<template>
  <Popover>
    <PopoverTrigger as-child>
      <Button
        variant="outline"
        :class="cn('w-[280px] justify-start text-left font-normal', !value && 'text-muted-foreground')">
        <CalendarIcon class="w-4 h-4 mr-2" />
        <template v-if="value.start">
          <template v-if="value.end">{{ df.format(value.start.toDate(getLocalTimeZone())) }} - {{ df.format(value.end.toDate(getLocalTimeZone())) }}</template>

          <template v-else>
            {{ df.format(value.start.toDate(getLocalTimeZone())) }}
          </template>
        </template>
        <template v-else>Pick a date</template>
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-auto p-0">
      <RangeCalendar
        v-model="value"
        initial-focus
        :number-of-months="2"
        @update:start-value="startDate => (value.start = startDate)" />
    </PopoverContent>
  </Popover>
</template>
