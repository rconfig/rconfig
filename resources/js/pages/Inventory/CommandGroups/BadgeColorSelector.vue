<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';

const emit = defineEmits(['update:modelValue']);
const selectedColor = ref({});

const colors = [
  { label: 'Lime', bgClass: 'bg-lime-400 text-lime-900 border-lime-300' },
  { label: 'Yellow', bgClass: 'bg-yellow-400 text-yellow-900 border-yellow-300' },
  { label: 'Teal', bgClass: 'bg-teal-400 text-teal-900 border-teal-300' },
  { label: 'Sky', bgClass: 'bg-sky-400 text-sky-900 border-sky-300' },
  { label: 'Pink', bgClass: 'bg-pink-400 text-pink-900 border-pink-300' },
  { label: 'Emerald', bgClass: 'bg-emerald-400 text-emerald-900 border-emerald-300' },
  { label: 'Orange', bgClass: 'bg-orange-400 text-orange-900 border-orange-300' },
  { label: 'Amber', bgClass: 'bg-amber-400 text-amber-900 border-amber-300' },
  { label: 'Fuchsia', bgClass: 'bg-fuchsia-400 text-fuchsia-900 border-fuchsia-300' },
  { label: 'Rose', bgClass: 'bg-rose-400 text-rose-900 border-rose-300' },
  { label: 'Violet', bgClass: 'bg-violet-500 text-violet-100 border-violet-600' },
  { label: 'Indigo', bgClass: 'bg-indigo-500 text-indigo-100 border-indigo-600' },
  { label: 'Blue', bgClass: 'bg-blue-500 text-blue-100 border-blue-600' },
  { label: 'Green', bgClass: 'bg-green-500 text-green-100 border-green-600' },
  { label: 'Purple', bgClass: 'bg-purple-500 text-purple-100 border-purple-600' },
  { label: 'Stone', bgClass: 'bg-stone-600 text-stone-200 border-stone-700' },
  { label: 'Red', bgClass: 'bg-red-600 text-red-100 border-red-700' },
  { label: 'Fuchsia Dark', bgClass: 'bg-fuchsia-700 text-fuchsia-200 border-fuchsia-800' },
  { label: 'Violet Dark', bgClass: 'bg-violet-700 text-violet-200 border-violet-800' },
  { label: 'Teal Dark', bgClass: 'bg-teal-700 text-teal-200 border-teal-800' }
];

function selectColor(color) {
  selectedColor.value = color;
  emit('update:modelValue', color.bgClass);
}
</script>

<template>
  <!-- DIV FOR RENDERING THE BADGE COLOR CLASSES -->
  <Popover>
    <div class="hidden text-yellow-200 text-teal-100 bg-yellow-700 bg-teal-700 border-yellow-500 border-teal-500 bg-stone-700 text-stone-200 border-stone-500 bg-lime-700 text-lime-200 border-lime-500 bg-sky-700 text-sky-100 border-sky-500 bg-violet-700 text-violet-200 border-violet-500 bg-fuchsia-700 text-fuchsia-200 border-fuchsia-500"></div>
    <PopoverTrigger class="col-span-3">
      <Button
        variant="ghost"
        class="flex flex-wrap items-start justify-start w-full p-1 pl-2 whitespace-normal border h-fit">
        <div
          class="h-6 px-2 border-2 border-transparent rounded-full cursor-pointer w-fit"
          :class="Object.keys(selectedColor).length === 0 ? 'bg-gray-600 text-gray-200 border-gray-500' : selectedColor.bgClass">
          {{ Object.keys(selectedColor).length === 0 ? 'Select color' : selectedColor.label }}
        </div>
      </Button>
    </PopoverTrigger>
    <PopoverContent
      side="bottom"
      align="start"
      class="col-span-3 p-0">
      <Separator />

      <ScrollArea class="h-64">
        <div class="py-1">
          <div class="grid grid-cols-3 gap-2 mt-4 place-items-center">
            <div
              v-for="(color, index) in colors"
              :key="index"
              :class="['cursor-pointer w-6 h-6 rounded-full border-2', color.bgClass, selectedColor.value === index ? 'ring-2 ring-offset-2 ring-black' : 'border-transparent']"
              @click="selectColor(color)"></div>
          </div>
        </div>
      </ScrollArea>
    </PopoverContent>
  </Popover>
</template>
