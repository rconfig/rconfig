<script setup>
import { HoverCard, HoverCardContent, HoverCardTrigger } from '@/components/ui/hover-card';
import { useCopy } from '@/composables/useCopy';

const { copy, activeCopyIcon } = useCopy();

const props = defineProps({
  log: {
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
          <h4 class="text-sm font-semibold">{{ log.log_name.charAt(0).toUpperCase() + log.log_name.slice(1) }}</h4>
          <p class="text-sm">{{ log.description }}</p>
          <p class="pt-4 text-sm">Raw Data:</p>
          <div class="text-sm">
            <pre v-highlightjs><code class="javascript">{{ log }}</code></pre>
          </div>
          <div class="flex pt-2 items-between">
            <Icon
              icon="formkit:datetime"
              class="w-4 h-4 mr-2 opacity-70" />
            <span class="text-xs text-muted-foreground">Event Date: {{ log.created_at }}</span>
            <Button
              class="h-6 p-1 ml-auto"
              variant="ghost"
              title="copy raw data"
              @click="copy(log.id, log)">
              <Icon :icon="activeCopyIcon[log.id] ? 'material-symbols:check-circle-outline' : 'material-symbols:content-copy-outline'" />
            </Button>
          </div>
        </div>
      </div>
    </HoverCardContent>
  </HoverCard>
</template>
