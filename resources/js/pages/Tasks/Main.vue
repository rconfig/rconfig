<script setup>
import ActionsMenu from '@/pages/Shared/Table/ActionsMenu.vue';
import ClearFilters from '@/pages/Shared/Filters/ClearFilters.vue';
import Loading from '@/pages/Shared/Table/Loading.vue';
import NoResults from '@/pages/Shared/Table/NoResults.vue';
import Pagination from '@/pages/Shared/Table/Pagination.vue';
import TaskAddEditDialog from '@/pages/Tasks/TaskAddEditDialog.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { onMounted, onUnmounted } from 'vue';
import { useRowSelection } from '@/composables/useRowSelection';
import { useTasks } from '@/pages/Tasks/useTasks';

const { editId, tasks, currentPage, perPage, searchTerm, lastPage, isLoading, fetchTasks, viewEditDialog, createTask, deleteTask, handleSave, handleKeyDown, newTaskModalKey, toggleSort, sortParam } = useTasks();
const { selectedRows, selectAll, toggleSelectAll, toggleSelectRow } = useRowSelection(tasks);

onMounted(() => {
  fetchTasks();
  window.addEventListener('keydown', handleKeyDown);
});

// Cleanup event listener on unmount
onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
  <div class="flex flex-col h-full gap-1 text-center">
    <div class="flex items-center justify-between p-4">
      <div class="flex items-center">
        <Input
          class="max-w-sm ml-4 mr-2"
          autocomplete="off"
          data-1p-ignore
          data-lpignore="true"
          placeholder="Filter tasks by ID or Name..."
          v-model="searchTerm" />
        <ClearFilters
          v-if="searchTerm"
          @update:model-value="searchTerm = ''" />
      </div>
      <div class="flex">
        <Button
          v-if="selectedRows.length"
          class="px-2 py-1 bg-red-600 hover:bg-red-700 hover:animate-pulse"
          size="md"
          variant="primary">
          Delete Selected {{ selectedRows.length }} Task(s)
        </Button>

        <Button
          type="submit"
          class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
          size="sm"
          @click.prevent="createTask"
          variant="primary">
          New Task
          <div class="pl-2 ml-auto">
            <kbd class="rc-kdb-class2">ALT N</kbd>
          </div>
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
            <TableHead class="w-[20%]">
              <Button
                class="flex justify-start w-full p-0 hover:bg-rcgray-800"
                variant="ghost"
                @click="toggleSort('task_name')">
                <Icon :icon="sortParam === 'task_name' ? 'lucide:sort-asc' : sortParam === '-task_name' ? 'lucide:sort-desc' : 'hugeicons:sorting-05'" />
                <span class="ml-2">Name</span>
              </Button>
            </TableHead>

            <TableHead class="w-[30%]">Description</TableHead>
            <TableHead class="w-[10%]">Created</TableHead>
            <TableHead class="w-[10%]">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-if="isLoading">
            <Loading />
          </template>

          <template v-else-if="!isLoading">
            <TableRow
              v-for="row in tasks.data"
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
              <TableCell class="text-start">
                {{ row.task_name }}
              </TableCell>
              <TableCell class="text-start">
                {{ row.task_desc }}
              </TableCell>
              <TableCell class="text-start">
                {{ row.created_at }}
              </TableCell>
              <!-- ACTIONS MENU -->
              <TableCell class="text-start">
                <ActionsMenu
                  :rowData="row"
                  @onEdit="viewEditDialog(row.id)"
                  @onDelete="deleteTask(row.id)" />
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

      <TaskAddEditDialog
        @save="handleSave()"
        :key="newTaskModalKey"
        :editId="editId" />

      <Toaster />
    </div>
  </div>
</template>
