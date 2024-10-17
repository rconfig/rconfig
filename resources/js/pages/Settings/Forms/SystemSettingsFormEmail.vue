<script setup>
import { ref } from 'vue';

const test = ref(false);

const props = defineProps({});
</script>

<template>
  <div class="grid w-full max-w-full items-center gap-1.5">
    <Label for="picture">Banner</Label>
    EMAIL FIELDS
    <p class="text-sm text-muted-foreground">The standard banner to display on the login page.</p>

    <div class="flex justify-end jap-2">
      <Button
        variant="outline"
        class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
        size="sm">
        Save
      </Button>

      <Button
        variant="outline"
        class="px-2 py-1 ml-2 text-sm bg-rcgray-900 hover:bg-rcgray-800 hover:animate-pulse"
        size="sm">
        Reset
      </Button>
    </div>
  </div>
</template>
