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
      No results.
    </TableCell>
  </TableRow>
</template>
