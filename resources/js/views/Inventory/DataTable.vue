<script setup lang="ts" generic="TData, TValue">
import { ref, computed } from 'vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { DropdownMenu, DropdownMenuContent, DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuPortal, DropdownMenuSeparator, DropdownMenuShortcut, DropdownMenuSub, DropdownMenuSubContent, DropdownMenuSubTrigger, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';

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
function onEdit(rowData) {
  // Handle edit action
  console.log('Edit:', rowData);
}

function onDelete(rowData) {
  // Handle delete action
  console.log('Delete:', rowData);
}
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
            <!-- Add Dropdown Button for Edit/Delete -->
            <TableCell>
              <DropdownMenu>
                <DropdownMenuTrigger as-child>
                  <Button variant="outline">...</Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                  class="w-56"
                  align="end"
                  side="bottom">
                  <DropdownMenuItem>
                    <span>Assign Roles</span>
                    <DropdownMenuShortcut><Icon icon="fluent-color:people-team-16" /></DropdownMenuShortcut>
                  </DropdownMenuItem>
                  <DropdownMenuItem>
                    <span>Edit</span>
                    <DropdownMenuShortcut><Icon icon="fluent-color:text-edit-style-16" /></DropdownMenuShortcut>
                  </DropdownMenuItem>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem>
                    <spa class="text-red-500">Delete</spa>
                    <DropdownMenuShortcut><Icon icon="fluent-color:cloud-dismiss-48" /></DropdownMenuShortcut>
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
              <!-- <DropdownMenu>
                <DropdownMenuContent>
                  <DropdownMenuGroup>
                    <DropdownMenuItem @click="() => onEdit(row.original)">Edit</DropdownMenuItem>
                    <DropdownMenuItem @click="() => onDelete(row.original)">Delete</DropdownMenuItem>
                  </DropdownMenuGroup>
                </DropdownMenuContent>
              </DropdownMenu> -->
            </TableCell>
          </TableRow>
        </template>
        <template v-else>
          <TableRow>
            <TableCell
              :colspan="props.columns.length + 1"
              class="h-24 text-center">
              No results.
            </TableCell>
          </TableRow>
        </template>
      </TableBody>
    </Table>
  </div>
</template>
