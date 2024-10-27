<script setup>
import ActionsMenu from '@/pages/Shared/Table/ActionsMenu.vue';
import ConfirmDeleteAlert from '@/pages/Shared/AlertDialog/ConfirmDeleteAlert.vue';
import CredentialsAddEditDialog from '@/pages/Settings/Panels/Components/CredentialsAddEditDialog.vue';
import Loading from '@/pages/Shared/Table/Loading.vue';
import NoResults from '@/pages/Shared/Table/NoResults.vue';
import Pagination from '@/pages/Shared/Table/Pagination.vue';
import DeviceListPopover from '@/pages/Shared/Popover/DeviceListPopover.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { eventBus } from '@/composables/eventBus';
import { onMounted } from 'vue';
import { useCredentials } from '@/pages/Settings/Panels/Components/useCredentials';
import { useRowSelection } from '@/composables/useRowSelection';

const { createCred, creds, currentPage, deleteCredential, deleteManyCredentials, editId, formatters, handleSave, isLoading, lastPage, newCredModalKey, perPage, searchTerm, showConfirmDelete, sortParam, toggleSort } = useCredentials();
const { selectedRows, selectAll, toggleSelectAll, toggleSelectRow } = useRowSelection(creds);

const props = defineProps({});

onMounted(() => {
  eventBus.on('deleteManyCredentialsSuccess', () => {
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
        <Button
          type="submit"
          class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
          size="sm"
          @click.prevent="createCred()"
          variant="primary">
          New Credentials
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
                @click="toggleSort('cred_name')">
                <Icon :icon="sortParam === 'cred_name' ? 'lucide:sort-asc' : sortParam === '-cred_name' ? 'lucide:sort-desc' : 'hugeicons:sorting-05'" />
                <span class="ml-2">Name</span>
              </Button>
            </TableHead>
            <TableHead class="w-[30%]">Description</TableHead>
            <TableHead class="w-[30%]">Devices</TableHead>
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
              v-for="row in creds.data"
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
                {{ row.cred_name }}
              </TableCell>
              <TableCell class="text-start">
                {{ row.cred_description }}
              </TableCell>
              <TableCell class="text-start">
                <span
                  v-for="(device, index) in row.device.slice(0, 4)"
                  :key="device.device_name"
                  class="mr-2">
                  <Badge
                    variant="outline"
                    class="py-1 mt-1 hover:bg-rcgray-800">
                    <router-link :to="device.view_url">{{ device.device_name }}</router-link>
                  </Badge>
                </span>
                <span
                  v-if="row.device.length > 4"
                  class="mr-2">
                  <DeviceListPopover
                    :recordName="row.device_name"
                    :items="row.device"
                    displayField="device_name" />
                </span>
              </TableCell>
              <TableCell class="text-start">
                {{ formatters.formatTime(row.created_at) }}
              </TableCell>
              <!-- ACTIONS MENU -->
              <TableCell class="text-start">
                <ActionsMenu
                  :showEditBtn="false"
                  :rowData="row"
                  @onDelete="deleteCredential(row.id)" />
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

      <CredentialsAddEditDialog
        @save="handleSave()"
        :key="newCredModalKey"
        :editId="editId" />

      <!-- FOR MULTIPLE DELETE -->
      <ConfirmDeleteAlert
        :ids="selectedRows"
        :showConfirmDelete="showConfirmDelete"
        @close="showConfirmDelete = false"
        @handleDelete="deleteManyCredentials(selectedRows)" />
      <!-- FOR MULTIPLE DELETE -->

      <Toaster />
    </div>
  </div>
</template>
