<script setup>
import { onMounted } from 'vue';
import { useSystemLogs } from '@/pages/Settings/Panels/Components/useSystemLogs';
import ActionsMenu from '@/pages/Shared/Table/ActionsMenu.vue';
import ConfirmDeleteAlert from '@/pages/Shared/AlertDialog/ConfirmDeleteAlert.vue';
import Loading from '@/pages/Shared/Table/Loading.vue';
import NoResults from '@/pages/Shared/Table/NoResults.vue';
import Pagination from '@/pages/Shared/Table/Pagination.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useRowSelection } from '@/composables/useRowSelection';
import { eventBus } from '@/composables/eventBus';
import SystemLogsTableHoverCard from '@/pages/Settings/Panels/Components/SystemLogsTableHoverCard.vue';

const { currentPage, deleteLog, deleteManyLogs, fetchLogs, filters, formatters, isLoading, lastPage, logs, perPage, searchTerm, showConfirmDelete, sortParam, toggleSort } = useSystemLogs();
const { selectedRows, selectAll, toggleSelectAll, toggleSelectRow } = useRowSelection(logs);

const props = defineProps({});

onMounted(() => {
  eventBus.on('deleteManyLogsSuccess', () => {
    selectedRows.value = [];
    selectAll.value = false;
  });
});
</script>

<template>
  <div class="flex flex-col h-full gap-1 text-center">
    <div class="flex items-center justify-between p-4">
      <div class="flex items-center">
        <Input
          class="max-w-sm ml-4"
          autocomplete="off"
          data-1p-ignore
          data-lpignore="true"
          placeholder="Filter tags..."
          v-model="searchTerm" />
        <Button
          class="ml-2 hover:bg-gray-800"
          variant="outline"
          @click="searchTerm = ''">
          Clear Filter
        </Button>
      </div>
      <div class="flex">
        <Button
          v-if="selectedRows.length"
          class="px-2 py-1 bg-red-600 hover:bg-red-700 hover:animate-pulse"
          size="md"
          @click.prevent="showConfirmDelete = true"
          variant="primary">
          Delete Selected {{ selectedRows.length }} Tag(s)
        </Button>
      </div>
    </div>

    <div class="px-6">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead class="w-[2%]">
              <Checkbox
                id="selectAll"
                v-model="selectAll"
                :checked="selectAll"
                @click="toggleSelectAll()" />
            </TableHead>
            <TableHead class="w-[5%]">
              <Button
                class="flex justify-start w-full p-0 hover:bg-rcgray-800"
                variant="ghost"
                @click="toggleSort('id')">
                <Icon :icon="sortParam === 'id' ? 'lucide:sort-asc' : sortParam === '-id' ? 'lucide:sort-desc' : 'hugeicons:sorting-05'" />
                <span class="ml-2">ID</span>
              </Button>
            </TableHead>
            <TableHead class="w-[5%]">
              <Button
                class="flex justify-start w-full p-0 hover:bg-rcgray-800"
                variant="ghost"
                @click="toggleSort('log_name')">
                <Icon :icon="sortParam === 'log_name' ? 'lucide:sort-asc' : sortParam === '-log_name' ? 'lucide:sort-desc' : 'hugeicons:sorting-05'" />
                <span class="ml-2">Name</span>
              </Button>
            </TableHead>
            <TableHead class="w-[50%]">Description</TableHead>
            <TableHead class="w-[10%]">Event time</TableHead>
            <TableHead class="w-[10%]">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-if="isLoading">
            <Loading />
          </template>

          <template v-else-if="!isLoading">
            <TableRow
              v-for="row in logs.data"
              :key="row.id">
              <TableCell class="text-start">
                <Checkbox
                  class="cursor-pointer"
                  :id="'select-' + row.id"
                  :checked="selectedRows.includes(row.id) ? true : false"
                  @click="toggleSelectRow(row.id)" />
              </TableCell>
              <TableCell class="text-start">
                {{ row.id }}
              </TableCell>
              <TableCell>
                <SystemLogsTableHoverCard :log="row">
                  <Button
                    variant="outline"
                    class="h-6 p-1">
                    <div
                      class="flex items-center"
                      v-if="row.log_name === 'warn'">
                      <Icon
                        icon="fluent-color:error-circle-24"
                        class="mr-2" />
                      {{ row.log_name.charAt(0).toUpperCase() + row.log_name.slice(1) }}
                    </div>
                    <div
                      class="flex items-center"
                      v-else-if="row.log_name === 'info'">
                      <Icon
                        icon="flat-color-icons:info"
                        class="mr-2" />
                      {{ row.log_name.charAt(0).toUpperCase() + row.log_name.slice(1) }}
                    </div>
                    <div
                      class="flex items-center"
                      v-else-if="row.log_name === 'error'">
                      <Icon
                        icon="fluent-color:dismiss-circle-32"
                        class="mr-2" />
                      {{ row.log_name.charAt(0).toUpperCase() + row.log_name.slice(1) }}
                    </div>
                  </Button>
                  <template v-slot:leftIcon>
                    <template v-if="row.log_name === 'warn'">
                      <Icon
                        icon="fluent-color:error-circle-24"
                        class="w-16 h-16 text-red-500" />
                    </template>
                    <template v-else-if="row.log_name === 'info'">
                      <Icon
                        icon="flat-color-icons:info"
                        class="w-16 h-16 text-blue-500" />
                    </template>
                    <template v-else-if="row.log_name === 'error'">
                      <Icon
                        icon="fluent-color:dismiss-circle-32"
                        class="w-16 h-16 text-red-500" />
                    </template>
                  </template>
                </SystemLogsTableHoverCard>
              </TableCell>
              <TableCell class="text-start">
                {{ row.description }}
              </TableCell>
              <TableCell class="text-start">
                {{ formatters.formatTime(row.created_at) }}
              </TableCell>
              <!-- ACTIONS MENU -->
              <TableCell class="text-start">
                <ActionsMenu
                  :showEditBtn="false"
                  :rowData="row"
                  @onDelete="deleteLog(row.id)" />
              </TableCell>
              <!-- ACTIONS MENU -->
            </TableRow>
          </template>
          <template v-else>
            <NoResults />
          </template>
        </TableBody>
      </Table>

      <!-- PAGINATION -->
      <Pagination
        :currentPage="currentPage"
        :lastPage="lastPage"
        :perPage="perPage"
        @update:currentPage="currentPage = $event"
        @update:perPage="perPage = $event" />
      <!-- END PAGINATION -->

      <!-- FOR MULTIPLE DELETE -->
      <ConfirmDeleteAlert
        :ids="selectedRows"
        :showConfirmDelete="showConfirmDelete"
        @close="showConfirmDelete = false"
        @handleDelete="deleteManyLogs(selectedRows)" />
      <!-- FOR MULTIPLE DELETE -->

      <Toaster />
    </div>
  </div>
</template>
