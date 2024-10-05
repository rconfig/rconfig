<script setup lang="ts">
import axios from 'axios';
import Loading from '@/components/Table/Loading.vue';
import Pagination from '@/components/Table/Pagination.vue';
import NoResults from '@/components/Table/NoResults.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { DropdownMenu, DropdownMenuContent, DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuPortal, DropdownMenuSeparator, DropdownMenuShortcut, DropdownMenuSub, DropdownMenuSubContent, DropdownMenuSubTrigger, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { h, ref, onMounted, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { Icon } from '@iconify/vue';

const tags = ref([]);
const isLoading = ref(true);
const currentPage = ref(1);
const last_page = ref(1);
const filters = ref({});
const perPage = ref(parseInt(localStorage.getItem('perPage') || '10'));
const searchTerm = ref('');
const sortParam = ref('-id');

// Select Row Management
const selectedRows = ref([]);
const selectAll = ref(false);

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

function onEdit(rowData) {
  // Handle edit action
  console.log('Edit:', rowData);
}

function onDelete(rowData) {
  // Handle delete action
  console.log('Delete:', rowData);
}

function onAssignRole(rowData) {
  // Handle assign role action
  console.log('Assign Role:', rowData);
}

const debouncedFilter = useDebounceFn(() => {
  filters.value[`filter[tagname]`] = searchTerm.value;
  currentPage.value = 1;
  fetchTags();
}, 500);

onMounted(() => {
  fetchTags();
});

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
          class="px-2 py-1 ml-2 bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
          size="md"
          variant="primary">
          New Tag
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
              <!-- Add Dropdown Button for Edit/Delete -->
              <TableCell class="text-start">
                <DropdownMenu>
                  <DropdownMenuTrigger as-child>
                    <Button
                      variant="ghost"
                      class="hover:animate-pulse">
                      ...
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent
                    class="w-56"
                    align="end"
                    side="bottom">
                    <DropdownMenuItem
                      class="cursor-pointer hover:bg-rcgray-800"
                      @click="() => onAssignRole(row.id)">
                      <span>Assign Roles</span>
                      <DropdownMenuShortcut>
                        <Icon icon="fluent-color:people-team-16" />
                      </DropdownMenuShortcut>
                    </DropdownMenuItem>
                    <DropdownMenuItem
                      class="cursor-pointer hover:bg-rcgray-800"
                      @click="() => onEdit(row.id)">
                      <span>Edit</span>
                      <DropdownMenuShortcut>
                        <Icon icon="fluent-color:text-edit-style-16" />
                      </DropdownMenuShortcut>
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem
                      class="cursor-pointer hover:bg-rcgray-800"
                      @click="() => onDelete(row.id)">
                      <span class="text-red-400">Delete</span>
                      <DropdownMenuShortcut>
                        <Icon icon="fluent-color:cloud-dismiss-48" />
                      </DropdownMenuShortcut>
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </TableCell>
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
    </div>
  </div>
</template>
