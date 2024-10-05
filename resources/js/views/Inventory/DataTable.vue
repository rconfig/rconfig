<script setup lang="ts" generic="TData, TValue">
import { ref, computed } from 'vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Pagination, PaginationEllipsis, PaginationFirst, PaginationLast, PaginationList, PaginationListItem, PaginationNext, PaginationPrev } from '@/components/ui/pagination';

const props = defineProps<{
  columns: ColumnDef<TData, TValue>[];
  data: TData[];
}>();

const currentPage = ref(1);
const rowsPerPage = ref(10);

// Calculate the total number of pages
// const totalPages = computed(() => Math.ceil(props.data.length / rowsPerPage.value));

// Slice the data for the current page
// const paginatedData = computed(() => {
//   const start = (currentPage.value - 1) * rowsPerPage.value;
//   return props.data.slice(start, start + rowsPerPage.value);
// });

const table = useVueTable({
  get data() {
    return props.data.data;
  },
  get columns() {
    return props.columns;
  },
  getCoreRowModel: getCoreRowModel()
});
</script>

<template>
  <div class="border rounded-md">
    <Table>
      <TableHeader>
        <TableRow
          v-for="headerGroup in table.getHeaderGroups()"
          :key="headerGroup.id">
          <TableHead
            v-for="header in headerGroup.headers"
            :key="header.id">
            <FlexRender
              v-if="!header.isPlaceholder"
              :render="header.column.columnDef.header"
              :props="header.getContext()" />
          </TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <template v-if="table.getRowModel().rows?.length">
          <TableRow
            v-for="row in table.getRowModel().rows"
            :key="row.id"
            :data-state="row.getIsSelected() ? 'selected' : undefined">
            <TableCell
              v-for="cell in row.getVisibleCells()"
              :key="cell.id">
              <FlexRender
                :render="cell.column.columnDef.cell"
                :props="cell.getContext()" />
            </TableCell>
          </TableRow>
        </template>
        <template v-else>
          <TableRow>
            <TableCell
              :colspan="props.columns.length"
              class="h-24 text-center">
              No results.
            </TableCell>
          </TableRow>
        </template>
      </TableBody>
    </Table>
  </div>
</template>
