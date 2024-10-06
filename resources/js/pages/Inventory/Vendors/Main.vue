<script setup lang="ts">
import ActionsMenu from '@/pages/Shared/Table/ActionsMenu.vue';
import Loading from '@/pages/Shared/Table/Loading.vue';
import NoResults from '@/pages/Shared/Table/NoResults.vue';
import Pagination from '@/pages/Shared/Table/Pagination.vue';
import axios from 'axios';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { h, ref, onMounted, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';

const vendors = ref([]);
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

const fetchVendors = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get('/api/vendors', {
      params: {
        page: currentPage.value,
        perPage: perPage.value,
        sort: sortParam.value,
        ...filters.value
      }
    });
    vendors.value = response.data;
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
  filters.value[`filter[vendorName]`] = searchTerm.value;
  currentPage.value = 1;
  fetchVendors();
}, 500);

onMounted(() => {
  fetchVendors();
});

watch([currentPage, perPage], () => {
  fetchVendors();
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
    selectedRows.value = vendors.value.data.map(row => row.id);
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
  fetchVendors();
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
          placeholder="Filter vendors..."
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
          Delete Selected {{ selectedRows.length }} Vendor(s)
        </Button>
        <Button
          class="px-2 py-1 ml-2 bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
          size="md"
          variant="primary">
          New Vendor
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
                @click="toggleSort('vendorName')">
                <span>Name</span>
                <Icon :icon="sortParam === 'vendorName' ? 'lucide:sort-asc' : sortParam === '-vendorName' ? 'lucide:sort-desc' : 'hugeicons:sorting-05'" />
              </Button>
            </TableHead>
            <TableHead class="w-[40%]">Devices</TableHead>
            <TableHead class="w-[10%]">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-if="isLoading">
            <Loading />
          </template>

          <template v-else-if="!isLoading && vendors.data.length > 0">
            <TableRow
              v-for="row in vendors.data"
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
                {{ row.vendorName }}
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
                  :onEdit="onEdit"
                  :onDelete="onDelete"
                  :onAssignRole="onAssignRole" />
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
    </div>
  </div>
</template>
