<script setup>
import ActionsMenu from '@/pages/Shared/Table/ActionsMenu.vue';
import Loading from '@/pages/Shared/Table/Loading.vue';
import NoResults from '@/pages/Shared/Table/NoResults.vue';
import Pagination from '@/pages/Shared/Table/Pagination.vue';
import DeviceAddEditDialog from '@/pages/Inventory/Devices/DeviceAddEditDialog.vue';
import TagListPopover from '@/pages/Shared/Popover/TagListPopover.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { onMounted, onUnmounted } from 'vue';
import { useRowSelection } from '@/composables/useRowSelection';
import { useDevices } from '@/pages/Inventory/Devices/useDevices';

const { editId, devices, currentPage, perPage, searchTerm, lastPage, isLoading, fetchDevices, viewEditDialog, createDevice, deleteDevice, handleSave, handleKeyDown, newDeviceModalKey, toggleSort, sortParam } = useDevices();
const { selectedRows, selectAll, toggleSelectAll, toggleSelectRow } = useRowSelection(devices);

onMounted(() => {
  fetchDevices();
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
          class="ml-2 min-w-32 lg:min-w-60"
          autocomplete="off"
          placeholder="Filter devices by ID, name or IP..."
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
          variant="primary">
          Delete Selected {{ selectedRows.length }} Device(s)
        </Button>

        <Button
          type="submit"
          class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
          size="sm"
          @click.prevent="createDevice"
          variant="primary">
          New Device
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
                @click="toggleSort('device_name')">
                <Icon :icon="sortParam === 'device_name' ? 'lucide:sort-asc' : sortParam === '-device_name' ? 'lucide:sort-desc' : 'hugeicons:sorting-05'" />
                <span class="ml-2">Name</span>
              </Button>
            </TableHead>
            <TableHead class="w-[10%]">IP Address</TableHead>
            <TableHead class="w-[10%]">Vendor</TableHead>
            <TableHead class="w-[10%]">Model</TableHead>
            <TableHead class="w-[10%]">Config Count</TableHead>
            <TableHead class="w-[10%]">Config Failures</TableHead>
            <TableHead class="w-[10%]">Last Config</TableHead>
            <TableHead class="w-[10%]">Tags</TableHead>
            <TableHead class="w-[10%]">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-if="isLoading">
            <Loading />
          </template>

          <template v-else-if="!isLoading">
            <TableRow
              v-for="row in devices.data"
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
                {{ row.device_name }}
              </TableCell>
              <TableCell class="text-start">
                {{ row.device_ip }}
              </TableCell>
              <TableCell class="text-start">
                <span v-if="row.vendor.length > 0">{{ row.vendor[0].vendorName }}</span>
                <span v-else>--</span>
              </TableCell>
              <TableCell class="text-start">
                {{ row.device_model }}
              </TableCell>
              <TableCell class="text-start">
                {{ row.config_good_count }}
              </TableCell>
              <TableCell class="text-start">
                <span v-if="row.config_bad_count">{{ row.config_bad_count }}</span>
                <span v-else>--</span>
              </TableCell>
              <TableCell class="text-start">
                <span v-if="row.last_config">{{ new Date(row.last_config.created_at).toLocaleString() }}</span>
                <span v-else>--</span>
              </TableCell>
              <TableCell class="text-start">
                <span
                  v-for="(tag, index) in row.tag.slice(0, 3)"
                  :key="tag.tagname"
                  class="mr-2">
                  <Badge
                    variant="outline"
                    class="py-1 mt-1 hover:bg-rcgray-800">
                    <router-link :to="tag.view_url">{{ tag.tagname }}</router-link>
                  </Badge>
                </span>
                <span
                  v-if="row.tag.length > 3"
                  class="mr-2">
                  <TagListPopover
                    :recordName="row.device_name"
                    :items="row.tag"
                    displayField="tagname" />
                </span>
              </TableCell>
              <!-- ACTIONS MENU -->
              <TableCell class="text-start">
                <ActionsMenu
                  :rowData="row"
                  @onEdit="viewEditDialog(row.id)"
                  @onDelete="deleteDevice(row.id)" />
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

      <DeviceAddEditDialog
        @save="handleSave()"
        :key="newDeviceModalKey"
        :editId="editId" />

      <Toaster />
    </div>
  </div>
</template>
