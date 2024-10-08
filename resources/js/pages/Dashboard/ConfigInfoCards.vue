<script setup>
import { ref } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';

const test = ref(false);

defineProps({
  configinfo: {
    type: Object,
    required: true
  },
  isLoadingConfiginfo: {
    type: Boolean,
    required: true
  }
});
</script>

<template>
  <div class="grid gap-2 p-8 sm:gap-4 md:grid-cols-2 xl:gap-8 lg:grid-cols-4">
    <Card class="border shadow rounded-xl bg-card text-card-foreground">
      <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
        <CardTitle class="text-sm font-medium">Device count</CardTitle>
        <Icon
          icon="devicon:networkx"
          class="w-4 h-4 text-muted-foreground" />
      </CardHeader>
      <CardContent>
        <div v-if="configinfo.data">
          <div class="text-2xl font-bold">
            {{ configinfo.data.deviceCount }}
          </div>
          <p class="text-xs text-muted-foreground">Total count of devices</p>
        </div>

        <div
          class="flex items-center space-x-4"
          v-if="isLoadingConfiginfo">
          <Skeleton class="w-12 h-12 rounded-full" />
          <div class="space-y-2">
            <Skeleton class="h-4 w-[250px]" />
            <Skeleton class="h-4 w-[200px]" />
          </div>
        </div>
      </CardContent>
    </Card>
    <Card>
      <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
        <CardTitle class="text-sm font-medium">Devices down</CardTitle>
        <Icon
          icon="fluent-color:cloud-dismiss-24"
          class="w-4 h-4 text-muted-foreground" />
      </CardHeader>
      <CardContent>
        <div v-if="configinfo.data">
          <div class="text-2xl font-bold">{{ configinfo.data.deviceDownCount }}</div>
          <p class="text-xs text-muted-foreground">Total number of devices unreachable</p>
        </div>

        <div
          class="flex items-center space-x-4"
          v-if="isLoadingConfiginfo">
          <Skeleton class="w-12 h-12 rounded-full" />
          <div class="space-y-2">
            <Skeleton class="h-4 w-[250px]" />
            <Skeleton class="h-4 w-[200px]" />
          </div>
        </div>
      </CardContent>
    </Card>
    <Card>
      <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
        <CardTitle class="text-sm font-medium">Total configs</CardTitle>
        <Icon
          icon="fluent-color:checkbox-24"
          class="w-4 h-4 text-muted-foreground" />
      </CardHeader>
      <CardContent>
        <div v-if="configinfo.data">
          <div class="text-2xl font-bold">{{ configinfo.data.configTotalCount }}</div>
          <p class="text-xs text-muted-foreground">Total number of configs downloaded</p>
        </div>
        <div
          class="flex items-center space-x-4"
          v-if="isLoadingConfiginfo">
          <Skeleton class="w-12 h-12 rounded-full" />
          <div class="space-y-2">
            <Skeleton class="h-4 w-[250px]" />
            <Skeleton class="h-4 w-[200px]" />
          </div>
        </div>
      </CardContent>
    </Card>
    <Card>
      <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
        <CardTitle class="text-sm font-medium">Failed configs</CardTitle>
        <Icon
          icon="fluent-color:calendar-cancel-24"
          class="w-4 h-4 text-muted-foreground" />
      </CardHeader>
      <CardContent>
        <div v-if="configinfo.data">
          <div class="text-2xl font-bold">{{ configinfo.data.configDownCount }}</div>
          <p class="text-xs text-muted-foreground">Number of configuration download failures</p>
        </div>

        <div
          class="flex items-center space-x-4"
          v-if="isLoadingConfiginfo">
          <Skeleton class="w-12 h-12 rounded-full" />
          <div class="space-y-2">
            <Skeleton class="h-4 w-[250px]" />
            <Skeleton class="h-4 w-[200px]" />
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>
