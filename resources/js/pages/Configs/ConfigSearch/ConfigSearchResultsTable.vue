<script setup>
import Loading from '@/pages/Shared/Table/Loading.vue';
import PeekConfigDialog from '@/pages/Shared/Dialogs/PeekConfigDialog.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import Pagination from '@/pages/Shared/Table/Pagination.vue';
import { useResultsTable } from './useResultsTable';

const props = defineProps({
  filters: Object
});
const { changePage, currentPage, errors, formatters, isDialogOpen, isFetching, lastPage, openDialog, perPage, results, updatePerpage, viewDetailsPane } = useResultsTable(props);
</script>

<template>
  <div class="px-6">
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead class="w-[5%]">ID</TableHead>
          <TableHead class="w-[20%]">Device</TableHead>
          <TableHead class="w-[10%]">Command Group</TableHead>
          <TableHead class="w-[10%]">Filesize</TableHead>
          <TableHead class="w-[10%]">Config date</TableHead>
          <TableHead class="w-[10%]">Matches</TableHead>
          <TableHead class="w-[10%]">Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <Loading v-if="isFetching" />

        <template v-else>
          <TableRow
            v-for="row in results"
            :key="row.id">
            <TableCell class="text-start">{{ row.id }}</TableCell>
            <TableCell class="text-start">{{ row.device_name }}</TableCell>
            <TableCell class="text-start">{{ row.device_category }}</TableCell>
            <TableCell class="text-start">{{ formatters.formatFileSize(row.config_filesize) }}</TableCell>
            <TableCell class="text-start">{{ formatters.formatTime(row.config_date) }}</TableCell>
            <TableCell class="text-start">{{ row.matches.length ? row.matches.length : 'No' }} match{{ row.matches.length > 1 ? 'es' : '' }}</TableCell>
            <TableCell class="flex items-center">
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger as-child>
                    <button
                      @click="openDialog('peek-config-dialog-' + row.id)"
                      class="btn-ghost">
                      <Icon
                        icon="lets-icons:view-alt-fill"
                        class="size-6 text-muted-foreground hover:text-blue-500" />
                    </button>
                  </TooltipTrigger>
                  <TooltipContent class="text-white bg-rcgray-800">
                    <p>Peek Config</p>
                  </TooltipContent>
                </Tooltip>
              </TooltipProvider>

              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger as-child>
                    <button
                      @click="viewDetailsPane(row.id)"
                      class="ml-2 btn-ghost">
                      <Icon
                        icon="hugeicons:file-view"
                        class="size-5 text-muted-foreground hover:text-blue-500" />
                    </button>
                  </TooltipTrigger>
                  <TooltipContent class="text-white bg-rcgray-800">
                    <p>Open Config</p>
                  </TooltipContent>
                </Tooltip>
              </TooltipProvider>

              <PeekConfigDialog
                :editId="row.id"
                v-if="isDialogOpen('peek-config-dialog-' + row.id)" />
            </TableCell>
          </TableRow>
        </template>
      </TableBody>
    </Table>

    <div
      v-if="!isFetching && results.length === 0"
      class="flex items-center justify-center my-4">
      <div v-if="Object.keys(errors).length === 0">No results found.</div>
      <div v-if="Object.keys(errors).length">
        <span
          v-for="error in errors"
          :key="error"
          class="col-span-3 col-start-2 text-sm text-red-400">
          <br />
          {{ error[0] }}
        </span>
      </div>
    </div>

    <Pagination
      v-if="!isFetching && results.length > 0"
      :currentPage="currentPage"
      :lastPage="lastPage"
      :perPage="perPage"
      @update:currentPage="changePage($event)"
      @update:perPage="updatePerpage($event)" />

    <div
      v-if="!isFetching && currentPage >= lastPage"
      class="flex items-center justify-center pb-8">
      <hr class="flex-grow mx-8" />
      <div class="text-muted-foreground">No more results to load.</div>
      <hr class="flex-grow mx-8" />
    </div>
  </div>
</template>
