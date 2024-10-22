<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  content: {
    type: String,
    required: true
  }
});
</script>

<template>
  <Popover>
    <PopoverTrigger as-child>
      <Button
        variant="ghost"
        class="h-0 p-0 m-0 ml-1">
        <Icon icon="formkit:help" />
      </Button>
    </PopoverTrigger>
    <PopoverContent
      class="w-80"
      side="top"
      align="start">
      <div class="grid gap-4">
        <div class="space-y-2">
          <div class="flex items-center space-x-2">
            <Icon icon="formkit:help" />
            <h4 class="font-medium leading-none">{{ title }}</h4>
          </div>
          <p class="text-sm text-muted-foreground">{{ content }}</p>
        </div>
      </div>
    </PopoverContent>
  </Popover>
</template>
