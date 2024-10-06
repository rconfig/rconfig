<script setup lang="ts">
import ActionsMenu from '@/components/Table/ActionsMenu.vue';
import Loading from '@/components/Table/Loading.vue';
import NoResults from '@/components/Table/NoResults.vue';
import Pagination from '@/components/Table/Pagination.vue';
import NewTagDialog from '@/components/Dialogs/NewTagDialog.vue';
import axios from 'axios';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { h, ref, onMounted, onUnmounted, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { useDialogStore } from '@/stores/dialogActions';
import { Input } from '@/components/ui/input';
import { useToaster } from '@/composables/useToaster'; // Import the composable
const { toastSuccess, toastError, toastInfo, toastWarning, toastDefault } = useToaster();

const tags = ref([]);
const isLoading = ref(true);
const currentPage = ref(1);
const last_page = ref(1);
const filters = ref({});
const perPage = ref(parseInt(localStorage.getItem('perPage') || '10'));
const searchTerm = ref('');
const sortParam = ref('-id');
const dialogStore = useDialogStore();
const { openDialog } = dialogStore;
const newTagModalKey = ref(1);
const editId = ref(0);

// Select Row Management
const selectedRows = ref([]);
const selectAll = ref(false);

function toastTest() {
  toastDefault('Default Message', 'There was a problem with your request.');
  toastError('Uh oh! Something went wrong.', 'There was a problem with your request.');
  toastWarning('Uh oh! Something went wrong.', 'There was a problem with your request.');
  toastInfo('Info: Happy days', 'There was a Info with your request.');
  toastSuccess('Success: Happy days', 'There was a Success with your request.');
}

function handleKeyDown(event: KeyboardEvent) {
  if (event.altKey && event.key === 'n') {
    event.preventDefault(); // Prevent default behavior (e.g., opening a new window in some browsers)
    openDialog('DialogNewTag');
  }
}

onMounted(() => {
  fetchTags();
  window.addEventListener('keydown', handleKeyDown);
});

// Cleanup event listener on unmount
onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});

function handleSave() {
  fetchTags(); // Fetch the updated tags after saving
  newTagModalKey.value = Math.random(); // Force re-render of the dialog component
}

const fetchTags = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get('/api/tags', {
      params: {
        page: currentPage.value,
        perPage: perPage.value,
        sort: sortParam.value,
        ...filters.value
      }
    });
    tags.value = response.data;
    last_page.value = response.data.last_page;
  } catch (error) {
    console.error('Error fetching data:', error);
  } finally {
    isLoading.value = false;
  }
};

function onEdit(id) {
  editId.value = id;
  newTagModalKey.value = Math.random(); // Force re-render of the dialog component
  openDialog('DialogNewTag');
}

function onDelete(id) {
  axios
    .delete(`/api/tags/${id}`)
    .then(() => {
      fetchTags();
      toastSuccess('Tag deleted', 'The tag has been deleted successfully.');
    })
    .catch(error => {
      console.error('Error deleting tag:', error);
      toastError('Error deleting tag', 'There was a problem deleting the tag.');
    });
}

const debouncedFilter = useDebounceFn(() => {
  filters.value[`filter[tagname]`] = searchTerm.value;
  currentPage.value = 1;
  fetchTags();
}, 500);

watch([currentPage, perPage], () => {
  fetchTags();
});

watch(searchTerm, () => {
  debouncedFilter();
});

watch(perPage, newVal => {
  localStorage.setItem('perPage', newVal.toString());
});

function toggleSelectAll() {
  selectAll.value = !selectAll.value;
  if (selectAll.value) {
    // Select all rows
    selectedRows.value = tags.value.data.map(row => row.id);
  } else {
    // Deselect all rows
    selectedRows.value = [];
  }
}

function toggleSelectRow(rowId: number) {
  if (selectedRows.value.includes(rowId)) {
    selectedRows.value = selectedRows.value.filter(id => id !== rowId);
  } else {
    selectedRows.value.push(rowId);
  }
}

function toggleSort(field) {
  if (sortParam.value === field) {
    sortParam.value = `-${field}`;
  } else {
    sortParam.value = field;
  }
  fetchTags();
}
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
          variant="primary">
          Delete Selected {{ selectedRows.length }} Tag(s)
        </Button>

        <Button
          type="submit"
          class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
          size="sm"
          @click.prevent="openDialog('DialogNewTag')"
          variant="primary">
          New Tag
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
                @click="toggleSelectAll()" />
            </TableHead>
            <TableHead class="w-[5%]">
              <Button
                class="flex justify-between w-full p-0 hover:bg-rcgray-800"
                variant="ghost"
                @click="toggleSort('id')">
                <span>ID</span>
                <Icon :icon="sortParam === 'id' ? 'lucide:sort-asc' : sortParam === '-id' ? 'lucide:sort-desc' : 'hugeicons:sorting-05'" />
              </Button>
            </TableHead>
            <TableHead class="w-[20%]">
              <Button
                class="flex justify-between w-full p-0 hover:bg-rcgray-800"
                variant="ghost"
                @click="toggleSort('tagname')">
                <span>Name</span>
                <Icon :icon="sortParam === 'tagname' ? 'lucide:sort-asc' : sortParam === '-tagname' ? 'lucide:sort-desc' : 'hugeicons:sorting-05'" />
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

          <template v-else-if="!isLoading && tags.data.length > 0">
            <TableRow
              v-for="row in tags.data"
              :key="row.id">
              <TableCell class="text-start">
                <Checkbox
                  class="cursor-pointer"
                  :id="'select-' + row.id"
                  @click="toggleSelectRow(row.id)" />
              </TableCell>
              <TableCell class="text-start">
                {{ row.id }}
              </TableCell>
              <TableCell class="text-start">
                {{ row.tagname }}
              </TableCell>
              <TableCell class="text-start">
                {{ row.tagDescription }}
              </TableCell>
              <TableCell class="text-start">
                <span
                  v-for="(device, index) in row.device.slice(0, 8)"
                  :key="device.device_name"
                  class="mr-2">
                  <Badge
                    variant="outline"
                    class="py-1 hover:bg-rcgray-800">
                    <router-link :to="device.view_url">{{ device.device_name }}</router-link>
                  </Badge>
                </span>
                <span
                  v-if="row.device.length > 8"
                  class="mr-2">
                  <Badge variant="outline">...</Badge>
                </span>
              </TableCell>
              <!-- ACTIONS MENU -->
              <TableCell class="text-start">
                <ActionsMenu
                  :rowData="row"
                  @onEdit="onEdit(row.id)"
                  @onDelete="onDelete(row.id)" />
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
        :lastPage="last_page"
        :perPage="perPage"
        @update:currentPage="currentPage = $event"
        @update:perPage="perPage = $event" />
      <!-- END PAGINATION -->

      <NewTagDialog
        @save="handleSave()"
        :key="newTagModalKey"
        :editId="editId" />

      <Button
        variant="outline"
        @click="toastTest()">
        Show Toast
      </Button>
      <Toaster />
    </div>
  </div>
</template>
