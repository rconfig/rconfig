<script setup>
import { inject } from 'vue';
import { HoverCard, HoverCardContent, HoverCardTrigger } from '@/components/ui/hover-card';
import { useCopy } from '@/composables/useCopy';

const { copyItem, activeCopyIcon } = useCopy();
const formatters = inject('formatters');

const props = defineProps({
  notification: {
    type: Object,
    required: true
  }
});
</script>

<template>
  <HoverCard>
    <HoverCardTrigger as-child>
      <slot />
    </HoverCardTrigger>
    <HoverCardContent
      class="w-full"
      align="start">
      <div class="flex justify-between space-x-4">
        <slot name="leftIcon"></slot>
        <div class="space-y-1">
          <h4 class="text-sm font-semibold">{{ notification.log_name.charAt(0).toUpperCase() + notification.log_name.slice(1) }}</h4>
          <p class="text-sm">{{ notification.description }}</p>
          <p class="pt-4 text-sm">Raw Data:</p>
          <div class="text-sm">
            <pre v-highlightjs><code class="javascript">{{ notification }}</code></pre>
          </div>
          <div class="flex pt-2 items-between">
            <Icon
              icon="formkit:datetime"
              class="w-4 h-4 mr-2 opacity-70" />
            <span class="text-xs text-muted-foreground">Event Date: {{ formatters.formatTime(notification.created_at) }}</span>
            <Button
              class="h-6 p-1 ml-auto"
              variant="ghost"
              title="copy raw data"
              @click="copyItem(notification.id, notification)">
              <Icon
                :icon="activeCopyIcon[notification.id] ? 'material-symbols:check-circle-outline' : 'material-symbols:content-copy-outline'"
                :class="activeCopyIcon[notification.id] ? 'text-green-500' : ''" />
            </Button>
          </div>
        </div>
      </div>
    </HoverCardContent>
  </HoverCard>
</template>
