<script setup>
import { ref, inject } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';

const hoverIcons = ref({});
const activeIcons = ref({});
const emit = defineEmits(['refresh']);
const formatters = inject('formatters');

defineProps({
  isLoading: Boolean,
  deviceData: Object
});

function refresh() {
  emit('refresh');
}
</script>

<template>
  <div>
    <Card class="overflow-hidden">
      <CardHeader class="flex flex-row items-start p-4 bg-muted/50">
        <div class="grid gap-0.5">
          <!-- <CardTitle class="flex items-center gap-2 text-lg group">Device Details</CardTitle> -->
        </div>
      </CardHeader>
      <CardContent class="p-4 pt-0 text-sm">
        <div
          class="space-y-2"
          v-if="isLoading">
          <Skeleton class="w-1/2 h-4" />
          <Skeleton class="w-full h-4" />
          <Skeleton class="w-full h-4" />
          <Skeleton class="w-full h-4" />
          <Skeleton class="w-full h-4" />
          <Skeleton class="w-full h-4" />
          <Skeleton class="w-full h-4" />
          <Skeleton class="w-full h-4" />
          <Skeleton class="w-full h-4" />
        </div>
        <transition name="fade">
          <div
            class="grid gap-3"
            v-if="!isLoading">
            <div class="grid items-center">
              <Label for="device_name">Device Name</Label>
              <Input
                id="device_name"
                autocomplete="off"
                placeholder="Device Name"
                class="w-full mt-4 mb-1 focus:outline-none focus-visible:ring-0" />
              <p class="ml-1 text-xs text-muted-foreground">The name of the device.</p>
            </div>
          </div>
        </transition>
      </CardContent>
      <CardFooter class="flex flex-row items-center px-4 py-3 border-t bg-muted/50">
        <div
          class="flex items-center gap-2 text-xs text-muted-foreground"
          v-if="!isLoading">
          <Icon
            icon="formkit:datetime"
            class="text-indigo-400" />
          Last Poll:
        </div>
      </CardFooter>
    </Card>
  </div>
</template>
