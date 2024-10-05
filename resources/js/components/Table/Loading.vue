<script setup>
import { ref } from 'vue';
import { TableCell, TableRow } from '@/components/ui/table';

defineProps({});
</script>

<template>
  <TableRow>
    <TableCell
      :colspan="12"
      class="h-24 text-center">
      <div class="flex items-center justify-center text-sm text-muted-foreground">
        <span>Loading</span>
        <Icon
          icon="eos-icons:three-dots-loading"
          class="ml-2" />
      </div>
    </TableCell>
  </TableRow>
</template>
