<script setup lang="ts">
import { h, ref, onMounted, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { Button } from '@/components/ui/button';
import { ColumnDef } from '@tanstack/vue-table';
import { Input } from '@/components/ui/input';
import { Checkbox } from '@/components/ui/checkbox';
import DataTable from './DataTable.vue';
import axios from 'axios';

const tags = ref<Tag[]>([]);
const isLoading = ref(true);
const currentPage = ref(1);
const last_page = ref(1);
const filters = ref<{ search?: string }>({});
const perPage = ref(10);
const searchTerm = ref('');

interface Tag {
  id: number;
  tagname: string;
  tagDescription: string;
}

const columns: ColumnDef<Tag>[] = [
  {
    id: 'select',
    header: ({ table }) =>
      h(Checkbox, {
        checked: table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
        'onUpdate:checked': value => table.toggleAllPageRowsSelected(!!value),
        ariaLabel: 'Select all'
      }),
    cell: ({ row }) =>
      h(Checkbox, {
        checked: row.getIsSelected(),
        'onUpdate:checked': value => row.toggleSelected(!!value),
        ariaLabel: 'Select row'
      }),
    enableSorting: false,
    enableHiding: false
  },
  {
    accessorKey: 'id',
    header: () => h('div', { class: 'text-left' }, 'ID'),
    cell: ({ row }) => h('div', { class: 'text-left' }, row.getValue('id'))
  },
  {
    accessorKey: 'tagname',
    header: () => h('div', { class: 'text-left' }, 'Tag Name'),
    cell: ({ row }) => h('div', { class: 'text-left' }, row.getValue('tagname'))
  },
  {
    accessorKey: 'tagDescription',
    header: () => h('div', { class: 'text-left' }, 'Tag Description'),
    cell: ({ row }) => h('div', { class: 'text-left' }, row.getValue('tagDescription'))
  }
];

const fetchTags = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get('/api/tags', {
      params: {
        page: currentPage.value,
        perPage: perPage.value,
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
</script>

<template>
  <div class="flex flex-col h-screen gap-1 text-center">
    <div class="flex items-center py-4">
      <Input
        class="max-w-sm ml-8"
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
      <Button
        class="ml-auto mr-8 bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
        variant="primary">
        New Tag
      </Button>
    </div>

    <div
      v-if="isLoading"
      class="flex items-center gap-2 ml-6 dark:text-gray-400">
      Loading
      <Icon icon="eos-icons:three-dots-loading" />
    </div>

    <div
      v-else
      class="container mx-auto">
      <DataTable
        :columns="columns"
        :data="tags" />

      <div class="flex items-center justify-end py-4 space-x-2">
        <div class="flex-1 text-sm text-muted-foreground">{{ currentPage }} of {{ last_page }} row(s) selected.</div>
        <div class="space-x-2">
          <Button
            @click="currentPage = Math.max(currentPage - 1, 1)"
            :disabled="currentPage === 1"
            variant="outline"
            size="sm">
            Previous
          </Button>
          <Button
            variant="outline"
            size="sm"
            @click="currentPage += 1"
            :disabled="currentPage >= last_page">
            Next
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>
