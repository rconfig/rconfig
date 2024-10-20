<script setup>
import ActionsMenu from '@/pages/Shared/Table/ActionsMenu.vue';
import ConfirmDeleteAlert from '@/pages/Shared/AlertDialog/ConfirmDeleteAlert.vue';
import CategoryListPopover from '@/pages/Shared/Popover/CategoryListPopover.vue';
import Loading from '@/pages/Shared/Table/Loading.vue';
import NoResults from '@/pages/Shared/Table/NoResults.vue';
import Pagination from '@/pages/Shared/Table/Pagination.vue';
import CommandAddEditDialog from '@/pages/Inventory/Commands/CommandAddEditDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { onMounted, onUnmounted } from 'vue';
import { useRowSelection } from '@/composables/useRowSelection';
import { useCommands } from '@/pages/Inventory/Commands/useCommands';
import { eventBus } from '@/composables/eventBus';

const { editId, commands, currentPage, perPage, searchTerm, lastPage, isLoading, fetchCommands, viewEditDialog, createCommand, deleteCommand, deleteManyCommands, handleSave, showConfirmDelete, handleKeyDown, newCommandModalKey, toggleSort, sortParam } = useCommands();
const { selectedRows, selectAll, toggleSelectAll, toggleSelectRow } = useRowSelection(commands);

onMounted(() => {
  fetchCommands();
  window.addEventListener('keydown', handleKeyDown);

  eventBus.on('deleteManyCommandsSuccess', () => {
    selectedRows.value = [];
    selectAll.value = false;
  });
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
          class="max-w-sm ml-4"
          autocomplete="off"
          data-1p-ignore
          data-lpignore="true"
          placeholder="Filter commands..."
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
          Delete Selected {{ selectedRows.length }} Command(s)
        </Button>
        <Button
          type="submit"
          class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
          size="sm"
          @click.prevent="createCommand"
          variant="primary">
          New Command
          <div class="pl-2 ml-auto">
            <kbd class="bxnAJf2">ALT N</kbd>
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
            <TableHead class="w-[20%]">
              <Button
                class="flex justify-start w-full p-0 hover:bg-rcgray-800"
                variant="ghost"
                @click="toggleSort('tagname')">
                <Icon :icon="sortParam === 'tagname' ? 'lucide:sort-asc' : sortParam === '-tagname' ? 'lucide:sort-desc' : 'hugeicons:sorting-05'" />
                <span class="ml-2">Command</span>
              </Button>
            </TableHead>
            <TableHead class="w-[20%]">Description</TableHead>
            <TableHead class="w-[40%]">Command Groups</TableHead>
            <TableHead class="w-[10%]">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-if="isLoading">
            <Loading />
          </template>

          <template v-else-if="!isLoading">
            <TableRow
              v-for="row in commands.data"
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
                {{ row.command }}
              </TableCell>
              <TableCell class="text-start">
                {{ row.description }}
              </TableCell>
              <TableCell class="text-start">
                <span
                  v-for="(category, index) in row.category.slice(0, 8)"
                  :key="category.categoryName"
                  class="mr-2">
                  <Badge
                    variant="outline"
                    :class="category.badgeColor ? category.badgeColor : 'bg-gray-600 text-gray-200 border-gray-500'"
                    class="py-1 mt-1 hover:bg-rcgray-800">
                    <router-link :to="category.view_url">{{ category.categoryName }}</router-link>
                  </Badge>
                </span>
                <span
                  v-if="row.category.length > 8"
                  class="mr-2">
                  <CategoryListPopover
                    :recordName="row.command"
                    :items="row.category"
                    displayField="categoryName" />
                </span>
              </TableCell>
              <!-- ACTIONS MENU -->
              <TableCell class="text-start">
                <ActionsMenu
                  :rowData="row"
                  @onEdit="viewEditDialog(row.id)"
                  @onDelete="deleteCommand(row.id)" />
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

      <CommandAddEditDialog
        @save="handleSave()"
        :key="newCommandModalKey"
        :editId="editId" />

      <!-- FOR MULTIPLE DELETE -->
      <ConfirmDeleteAlert
        :ids="selectedRows"
        :showConfirmDelete="showConfirmDelete"
        @close="showConfirmDelete = false"
        @handleDelete="deleteManyCommands(selectedRows)" />
      <!-- FOR MULTIPLE DELETE -->

      <Toaster />
    </div>
  </div>
</template>
