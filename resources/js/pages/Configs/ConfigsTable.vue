<script setup>
import PeekConfigDialog from '@/pages/Shared/Dialogs/PeekConfigDialog.vue';
import ClearFilters from '@/pages/Shared/Filters/ClearFilters.vue';
import ConfirmDeleteAlert from '@/pages/Shared/AlertDialog/ConfirmDeleteAlert.vue';
import Loading from '@/pages/Shared/Table/Loading.vue';
import NoResults from '@/pages/Shared/Table/NoResults.vue';
import Pagination from '@/pages/Shared/Table/Pagination.vue';
import StatusFilter from '@/pages/Configs/Filters/StatusFilter.vue';
import CommandFilter from '@/pages/Configs/Filters/CommandFilter.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { eventBus } from '@/composables/eventBus';
import { onMounted, onUnmounted } from 'vue';
import { useConfigsTable } from '@/pages/Configs/useConfigsTable';
import { useRowSelection } from '@/composables/useRowSelection';

const props = defineProps({
  configsId: {
    type: Number,
    default: 0
  }
});
const { viewDetailsPane, filterCommand, configs, filterStatus, isLoading, currentPage, perPage, lastPage, editId, clearFilters, formatters, searchTerm, openDialog, isDialogOpen, getTabledata, createConfig, updateConfig, deleteConfig, deleteManyConfigs, handleSave, handleKeyDown, viewEditDialog, toggleSort, sortParam, showConfirmDelete } = useConfigsTable(props);
const { selectedRows, selectAll, toggleSelectAll, toggleSelectRow } = useRowSelection(configs);

onMounted(() => {
  getTabledata();

  window.addEventListener('keydown', handleKeyDown);

  eventBus.on('deleteManyConfigsSuccess', () => {
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
          placeholder="Filter configs..."
          v-model="searchTerm" />
        <Separator
          orientation="vertical"
          class="relative w-px h-6 mx-4 shrink-0 bg-border" />

        <div class="flex gap-2">
          <CommandFilter
            v-model="filterCommand"
            :deviceId="configsId" />
          <StatusFilter v-model="filterStatus" />
          <ClearFilters
            v-if="searchTerm || filterStatus.length"
            @update:model-value="clearFilters" />
        </div>
      </div>
      <div class="flex">
        <Button
          v-if="selectedRows.length"
          class="px-2 py-1 bg-red-600 hover:bg-red-700 hover:animate-pulse"
          size="md"
          @click.prevent="showConfirmDelete = true"
          variant="primary">
          Delete Selected {{ selectedRows.length }} Config(s)
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
            <TableHead class="w-[2%]">
              <Button
                class="flex justify-start w-full p-0 hover:bg-rcgray-800"
                variant="ghost"
                @click="toggleSort('id')">
                <Icon :icon="sortParam === 'id' ? 'lucide:sort-asc' : sortParam === '-id' ? 'lucide:sort-desc' : 'hugeicons:sorting-05'" />
                <span class="ml-2">ID</span>
              </Button>
            </TableHead>
            <TableHead class="w-[2%]">
              <Button
                class="flex justify-start w-full p-0 hover:bg-rcgray-800"
                variant="ghost"
                @click="toggleSort('download_status')">
                <Icon :icon="sortParam === 'download_status' ? 'lucide:sort-asc' : sortParam === '-download_status' ? 'lucide:sort-desc' : 'hugeicons:sorting-05'" />
                <span class="ml-2"></span>
              </Button>
            </TableHead>
            <TableHead class="w-[10%]">
              <Button
                class="flex justify-start w-full p-0 hover:bg-rcgray-800"
                variant="ghost"
                @click="toggleSort('device_name')">
                <Icon :icon="sortParam === 'device_name' ? 'lucide:sort-asc' : sortParam === '-device_name' ? 'lucide:sort-desc' : 'hugeicons:sorting-05'" />
                <span class="ml-2">Filename</span>
              </Button>
            </TableHead>
            <TableHead class="w-[15%]">
              <Button
                class="flex justify-start w-full p-0 hover:bg-rcgray-800"
                variant="ghost"
                @click="toggleSort('command')">
                <Icon :icon="sortParam === 'command' ? 'lucide:sort-asc' : sortParam === '-command' ? 'lucide:sort-desc' : 'hugeicons:sorting-05'" />
                <span class="ml-2">Command</span>
              </Button>
            </TableHead>
            <TableHead class="w-[10%]">Device Name</TableHead>
            <TableHead class="w-[10%]">Filesize</TableHead>
            <TableHead class="w-[10%]">Downloaded</TableHead>
            <TableHead class="w-[10%]"></TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-if="isLoading">
            <Loading />
          </template>

          <template v-else-if="!isLoading">
            <TableRow
              v-for="row in configs.data"
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
                <StatusRedIcon v-if="row.download_status === 0" />
                <StatusGreenIcon v-if="row.download_status === 1" />
                <StatusYellowIcon v-if="row.download_status === 2" />
                <StatusGrayIcon v-if="row.download_status === 100" />
              </TableCell>
              <TableCell class="text-start">
                <Button
                  class="px-2 py-0 hover:bg-rcgray-800 rounded-xl"
                  variant="ghost"
                  @click="viewDetailsPane(row.id)">
                  <span class="border-b">{{ row.config_filename }}</span>
                </Button>
              </TableCell>
              <TableCell class="text-start">
                {{ row.command }}
              </TableCell>
              <TableCell class="text-start">
                {{ row.device_name }}
              </TableCell>
              <TableCell class="text-start">
                {{ row.config_filesize ? formatters.formatFileSize(row.config_filesize) : '' }}
              </TableCell>
              <TableCell class="text-start">
                {{ formatters.formatTime(row.created_at) }}
              </TableCell>
              <!-- ACTIONS MENU -->
              <TableCell class="text-start">
                <TableCell class="flex items-center">
                  <TooltipProvider>
                    <Tooltip>
                      <TooltipTrigger as-child>
                        <Button
                          variant="ghost"
                          @click="openDialog('peek-config-dialog-' + row.id)">
                          <Icon
                            icon="lets-icons:view-alt-fill"
                            class="size-6 text-muted-foreground hover:text-blue-500" />
                        </Button>
                      </TooltipTrigger>
                      <TooltipContent class="text-white bg-rcgray-800">
                        <p>Peek Config</p>
                      </TooltipContent>
                    </Tooltip>
                  </TooltipProvider>

                  <TooltipProvider>
                    <Tooltip>
                      <TooltipTrigger as-child>
                        <Button variant="ghost">
                          <Icon
                            icon="hugeicons:file-view"
                            class="size-5 text-muted-foreground hover:text-blue-500" />
                        </Button>
                      </TooltipTrigger>
                      <TooltipContent class="text-white bg-rcgray-800">
                        <p>Open Config</p>
                      </TooltipContent>
                    </Tooltip>
                  </TooltipProvider>

                  <PeekConfigDialog
                    :editId="row.id"
                    v-if="isDialogOpen('peek-config-dialog-' + row.id)"></PeekConfigDialog>
                </TableCell>
              </TableCell>
              <!-- ACTIONS MENU -->
            </TableRow>
          </template>
          <template v-else>
            <NoResults />
            P
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
        @handleDelete="deleteManyConfigs(selectedRows)" />
      <!-- FOR MULTIPLE DELETE -->

      <Toaster />
    </div>
  </div>
</template>
