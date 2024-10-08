<script setup>
import { ref } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import { Icon } from '@iconify/vue';

const hoverIcons = ref({});
const activeIcons = ref({});
const emit = defineEmits(['refresh']);

defineProps({
  healthLatest: {
    type: Object,
    required: true
  },
  isLoadingHealth: {
    type: Boolean,
    required: true
  },
  SystemUptime: {
    type: String
  }
});

function refresh() {
  emit('refresh');
}
</script>

<template>
  <div>
    <Card class="overflow-hidden">
      <CardHeader class="flex flex-row items-start bg-muted/50">
        <div class="grid gap-0.5">
          <CardTitle class="flex items-center gap-2 text-lg group">Latest Health</CardTitle>
          <CardDescription>Server health information</CardDescription>
        </div>
        <div class="flex items-center gap-1 ml-auto">
          <Button
            @click="refresh()"
            size="sm"
            variant="outline"
            class="gap-1 hover:bg-rcgray-800">
            <Icon
              icon="flat-color-icons:refresh"
              class="hover:animate-pulse" />
          </Button>
        </div>
      </CardHeader>
      <CardContent class="text-sm">
        <div
          class="flex items-start space-x-4"
          v-if="isLoadingHealth">
          <Skeleton class="w-12 h-12 rounded-full" />
          <div class="space-y-2">
            <Skeleton class="h-4 w-[250px]" />
            <Skeleton class="h-4 w-[250px]" />
            <Skeleton class="h-4 w-[250px]" />
            <Skeleton class="h-4 w-[250px]" />
            <Skeleton class="h-4 w-[250px]" />
            <Skeleton class="h-4 w-[250px]" />
            <Skeleton class="h-4 w-[250px]" />
            <Skeleton class="h-4 w-[250px]" />
            <Skeleton class="h-4 w-[250px]" />
          </div>
        </div>
        <div v-if="!healthLatest">asd</div>

        <div
          class="grid gap-3"
          v-if="healthLatest.data">
          <dl class="grid gap-3">
            <div class="flex items-center justify-between">
              <dt class="text-muted-foreground">{{ healthLatest.data[0].label }}</dt>
              <dd class="flex items-center gap-2">
                <Icon :icon="healthLatest.data[0].status === 'Ok' ? 'fluent-color:checkmark-circle-32' : 'fluent-color:dismiss-circle-32'" />
              </dd>
            </div>
            <div class="flex items-center justify-between">
              <dt class="text-muted-foreground">{{ healthLatest.data[1].label }}</dt>
              <dd class="flex items-center gap-2">
                <Icon :icon="healthLatest.data[1].status === 'Ok' ? 'fluent-color:checkmark-circle-32' : 'fluent-color:dismiss-circle-32'" />
              </dd>
            </div>
            <div class="flex items-center justify-between">
              <dt class="text-muted-foreground">{{ healthLatest.data[3].label }}</dt>
              <dd class="flex items-center gap-2">
                <Icon :icon="healthLatest.data[3].status === 'Running' ? 'fluent-color:checkmark-circle-32' : 'fluent-color:dismiss-circle-32'" />
              </dd>
            </div>
            <div class="flex items-center justify-between">
              <dt class="text-muted-foreground">{{ healthLatest.data[4].label }}</dt>
              <dd class="flex items-center gap-2">
                <Icon :icon="healthLatest.data[4].status === 'Reachable' ? 'fluent-color:checkmark-circle-32' : 'fluent-color:dismiss-circle-32'" />
              </dd>
            </div>
            <div class="flex items-center justify-between">
              <dt class="text-muted-foreground">{{ healthLatest.data[6].label }}</dt>
              <dd class="flex items-center gap-2">
                <Icon :icon="healthLatest.data[6].status === 'Ok' ? 'fluent-color:checkmark-circle-32' : 'fluent-color:dismiss-circle-32'" />
              </dd>
            </div>
            <div class="flex items-center justify-between">
              <dt class="text-muted-foreground">{{ healthLatest.data[7].label }}</dt>
              <dd class="flex items-center gap-2">
                {{ healthLatest.data[7].status }}
              </dd>
            </div>
            <div class="flex items-center justify-between">
              <dt class="text-muted-foreground">{{ healthLatest.data[2].label }}</dt>
              <dd class="flex items-center gap-2">
                {{ healthLatest.data[2].status }}
              </dd>
            </div>
          </dl>
        </div>
      </CardContent>
      <CardFooter class="flex flex-row items-center px-6 py-3 border-t bg-muted/50">
        <div class="text-xs text-muted-foreground">
          System Uptime:
          <time dateTime="2023-11-23">{{ SystemUptime }}</time>
        </div>
      </CardFooter>
    </Card>
  </div>
</template>
