<script setup>
import { ref } from 'vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Badge } from '@/components/ui/badge';

defineProps({
  recordName: {
    type: String,
    required: true
  },
  items: {
    type: Array,
    required: true
  },
  displayField: {
    type: String,
    default: 'name'
  },
  displayCount: {
    type: Number,
    default: 100
  }
});
</script>

<template>
  <Popover>
    <PopoverTrigger as-child>
      <Button
        variant="outline"
        className="mt-1 inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground">
        ...
      </Button>
    </PopoverTrigger>
    <PopoverContent
      class="max-w-max"
      style="width: 800px">
      <div class="space-y-2">
        <!-- <h4 class="font-medium leading-none">Devices</h4> -->
        <p class="mb-2 text-sm text-muted-foreground">All command groups associated with {{ recordName }}. Showing {{ displayCount }} records.</p>
      </div>
      <Badge
        v-for="item in items.slice(0, displayCount)"
        :key="item[displayField]"
        variant="outline"
        class="py-1 mt-1 hover:bg-rcgray-900">
        <router-link :to="item.view_url">{{ item[displayField] }}</router-link>
      </Badge>
      <span
        class="text-xs text-muted-foreground"
        v-if="displayCount > displayCount - 1">
        <br />
        Displaying only {{ displayCount }} records. Visit the
        <router-link
          to="/commandgroups"
          class="text-blue-500 hover:underline">
          Command Groups
        </router-link>
        page to view all devices.
      </span>
    </PopoverContent>
  </Popover>
</template>
