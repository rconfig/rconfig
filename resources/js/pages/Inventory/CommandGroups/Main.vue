<script setup>
import ActionsMenu from '@/pages/Shared/Table/ActionsMenu.vue';
import ClearFilters from '@/pages/Shared/Filters/ClearFilters.vue';
import ConfirmDeleteAlert from '@/pages/Shared/AlertDialog/ConfirmDeleteAlert.vue';
import DeviceListPopover from '@/pages/Shared/Popover/DeviceListPopover.vue';
import Loading from '@/pages/Shared/Table/Loading.vue';
import NoResults from '@/pages/Shared/Table/NoResults.vue';
import Pagination from '@/pages/Shared/Table/Pagination.vue';
import CommandGroupAddEditDialog from '@/pages/Inventory/CommandGroups/CommandGroupAddEditDialog.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { onMounted, onUnmounted } from 'vue';
import { useRowSelection } from '@/composables/useRowSelection';
import { useCommandGroups } from '@/pages/Inventory/CommandGroups/useCommandGroups';
import { eventBus } from '@/composables/eventBus';

const { editId, categories, currentPage, perPage, searchTerm, lastPage, isLoading, fetchCommandGroups, viewEditDialog, createCommandGroup, deleteCommandGroup, deleteManyCommandGroups, handleSave, showConfirmDelete, handleKeyDown, newCommandGroupModalKey, toggleSort, sortParam } = useCommandGroups();
const { selectedRows, selectAll, toggleSelectAll, toggleSelectRow } = useRowSelection(categories);

onMounted(() => {
  fetchCommandGroups();
  window.addEventListener('keydown', handleKeyDown);

  eventBus.on('deleteManyCommandGroupsSuccess', () => {
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
          class="max-w-sm ml-4 mr-2"
          autocomplete="off"
          data-1p-ignore
          data-lpignore="true"
          placeholder="Filter command groups..."
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
          @click.prevent="showConfirmDelete = true"
          variant="primary">
          Delete Selected {{ selectedRows.length }} CommandGroup(s)
        </Button>
        <Button
          type="submit"
          class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
          size="sm"
          @click.prevent="createCommandGroup"
          variant="primary">
          New Command Group
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
                @click="toggleSort('categoryName')">
                <Icon :icon="sortParam === 'categoryName' ? 'lucide:sort-asc' : sortParam === '-categoryName' ? 'lucide:sort-desc' : 'hugeicons:sorting-05'" />
                <span class="ml-2">Name</span>
              </Button>
            </TableHead>
            <TableHead class="w-[20%]">Description</TableHead>
            <TableHead class="w-[40%]">Devices</TableHead>
            <TableHead class="w-[10%]">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-if="isLoading">
            <Loading />
          </template>

          <template v-else-if="!isLoading">
            <TableRow
              v-for="row in categories.data"
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
                {{ row.categoryName }}
              </TableCell>
              <TableCell class="text-start">
                {{ row.categoryDescription }}
              </TableCell>
              <TableCell class="text-start">
                <span
                  v-for="(device, index) in row.device.slice(0, 8)"
                  :key="device.device_name"
                  class="mr-2">
                  <Badge
                    variant="outline"
                    class="py-1 mt-1 hover:bg-rcgray-800">
                    <router-link :to="device.view_url">{{ device.device_name }}</router-link>
                  </Badge>
                </span>
                <span
                  v-if="row.device.length > 8"
                  class="mr-2">
                  <DeviceListPopover
                    :recordName="row.categoryName"
                    :items="row.device"
                    displayField="device_name" />
                </span>
              </TableCell>
              <!-- ACTIONS MENU -->
              <TableCell class="text-start">
                <ActionsMenu
                  :rowData="row"
                  @onEdit="viewEditDialog(row.id)"
                  @onDelete="deleteCommandGroup(row.id)" />
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

      <CommandGroupAddEditDialog
        @save="handleSave()"
        :key="newCommandGroupModalKey"
        :editId="editId" />

      <!-- FOR MULTIPLE DELETE -->
      <ConfirmDeleteAlert
        :ids="selectedRows"
        :showConfirmDelete="showConfirmDelete"
        @close="showConfirmDelete = false"
        @handleDelete="deleteManyCommandGroups(selectedRows)" />
      <!-- FOR MULTIPLE DELETE -->

      <Toaster />
    </div>
  </div>
</template>
