<script setup>
import ActionsMenu from '@/pages/Shared/Table/ActionsMenu.vue';
import ConfirmDeleteAlert from '@/pages/Shared/AlertDialog/ConfirmDeleteAlert.vue';
import DeviceListPopover from '@/pages/Shared/Popover/DeviceListPopover.vue';
import Loading from '@/pages/Shared/Table/Loading.vue';
import NoResults from '@/pages/Shared/Table/NoResults.vue';
import Pagination from '@/pages/Shared/Table/Pagination.vue';
import Spinner from '@/pages/Shared/Icon/Spinner.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { eventBus } from '@/composables/eventBus';
import { onMounted, onUnmounted } from 'vue';
import { useRowSelection } from '@/composables/useRowSelection';
import { useTemplates } from '@/pages/Inventory/Templates/useTemplates';
import { useTemplatesGithub } from '@/pages/Inventory/Templates/useTemplatesGithub';

const emit = defineEmits(['createTemplate', 'viewTemplateDetailsPane']);
const { templates, isLoading, currentPage, perPage, lastPage, editId, newTemplateModalKey, searchTerm, openDialog, fetchTemplates, createTemplate, viewTemplateDetailsPane, deleteTemplate, deleteManyTemplates, handleSave, handleKeyDown, viewEditDialog, toggleSort, sortParam, showConfirmDelete } = useTemplates(emit);
const { importTemplates, importingTemplates } = useTemplatesGithub();
const { selectedRows, selectAll, toggleSelectAll, toggleSelectRow } = useRowSelection(templates);

onMounted(() => {
  fetchTemplates();
  window.addEventListener('keydown', handleKeyDown);

  eventBus.on('deleteManyTemplatesSuccess', () => {
    selectedRows.value = [];
    selectAll.value = false;
    document.getElementById('selectAll').checked = false;
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
          placeholder="Filter templates..."
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
          Delete Selected {{ selectedRows.length }} Template(s)
        </Button>

        <Button
          type="close"
          class="px-2 py-1 ml-2 text-sm hover:bg-gray-700"
          @click="importTemplates()"
          variant="outline">
          <Icon
            v-if="!importingTemplates"
            icon="mdi:github"
            class="mr-2" />
          <Spinner :state="importingTemplates" />
          Import Templates
        </Button>

        <Button
          type="submit"
          class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
          size="sm"
          @click.prevent="createTemplate(editId)"
          variant="primary">
          New Template
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
                @click="toggleSort('templateName')">
                <Icon :icon="sortParam === 'templateName' ? 'lucide:sort-asc' : sortParam === '-templateName' ? 'lucide:sort-desc' : 'hugeicons:sorting-05'" />
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
              v-for="row in templates.data"
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
                <Button
                  class="px-2 py-0 text-sm hover:bg-rcgray-800 rounded-xl"
                  variant="ghost"
                  @click="viewTemplateDetailsPane(row.id)">
                  <span class="border-b">{{ row.templateName }}</span>
                </Button>
              </TableCell>
              <TableCell class="text-start">
                {{ row.description }}
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
                    :recordName="row.templateName"
                    :items="row.device"
                    displayField="device_name" />
                </span>
              </TableCell>
              <!-- ACTIONS MENU -->
              <TableCell class="text-start">
                <ActionsMenu
                  :rowData="row"
                  @onEdit="viewTemplateDetailsPane(row.id)"
                  @onDelete="deleteTemplate(row.id)" />
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
        @handleDelete="deleteManyTemplates(selectedRows)" />
      <!-- FOR MULTIPLE DELETE -->

      <Toaster />
    </div>
  </div>
</template>
