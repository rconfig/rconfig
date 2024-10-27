<script setup>
import { ref, inject } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import useClipboard from 'vue-clipboard3';

const hoverIcons = ref({});
const activeIcons = ref({});
const { toClipboard } = useClipboard();
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
          <CardTitle class="flex items-center gap-2 text-lg group">Configuration Summary</CardTitle>
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
            class="grid gap-2 text-sm"
            v-if="!isLoading && deviceData">
            <dl class="grid gap-2">
              <div class="flex items-center justify-between">
                <dt class="flex items-center gap-1 text-muted-foreground">
                  <StatusGreenIcon />
                  > Good Configs
                </dt>
                <dd class="flex items-center gap-2">
                  {{ deviceData.config_good_count }}
                </dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="flex items-center gap-1 text-muted-foreground">
                  <StatusRedIcon />
                  Failed Configs
                </dt>
                <dd class="flex items-center gap-2">{{ deviceData.config_bad_count }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="flex items-center gap-1 text-muted-foreground">
                  <StatusYellowIcon />
                  Unknown Configs
                </dt>
                <dd class="flex items-center gap-2">{{ deviceData.config_unknown_count }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="flex items-center gap-1 text-muted-foreground">
                  <Icon
                    icon="formkit:datetime"
                    class="text-indigo-400" />
                  Last Download
                </dt>
                <dd class="flex items-center gap-2">{{ formatters.formatTime(deviceData.last_config) }}</dd>
              </div>
            </dl>
          </div>
        </transition>
      </CardContent>
      <!-- <CardFooter class="flex flex-row items-center px-6 py-3 border-t bg-muted/50">
        <div class="flex items-center gap-2 text-xs text-muted-foreground">
          <Icon icon="logos:laravel" />
          Laravel Version: sysinfo.LaravelVersion
        </div>
      </CardFooter> -->
    </Card>
  </div>
</template>
