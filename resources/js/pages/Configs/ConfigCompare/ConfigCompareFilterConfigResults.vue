<script setup>
import Loading from '@/pages/Shared/Table/Loading.vue';
import NoResults from '@/pages/Shared/Table/NoResults.vue';
import Pagination from '@/pages/Shared/Table/Pagination.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { watch, inject } from 'vue';
import { useCompareConfigResults } from './useCompareConfigResults';
import { useRowSelection } from '@/composables/useRowSelection';
import { Badge } from '@/components/ui/badge';

const formatters = inject('formatters');
const emit = defineEmits(['updateSelectedRows']);

const props = defineProps({
  filterData: Object,
  comparePosition: String
});

const { currentPage, isLoading, lastPage, perPage, results } = useCompareConfigResults(props, emit);
const { selectedRows, selectAll, toggleSelectAll, toggleSingleSelectRow } = useRowSelection(results);

watch(selectedRows, val => {
  emit('updateSelectedRows', val);
});
</script>

<template>
  <div class="w-full">
    <!-- First element -->
    <!-- NO RESULTS -->
    <div
      class="min-h-[35dvh] flex items-center justify-center"
      v-if="filterData.device.length === 0">
      <div class="text-sm text-center">
        <span class="text-muted-foreground">{{ formatters.capitalize(comparePosition) }} selection. No results, yet!</span>
        <br />
        <span class="text-muted-foreground">Add filters to select configurations for comparison</span>
      </div>
    </div>

    <!-- NO RESULTS -->
    <div
      class="min-h-[35dvh] flex-1 w-full"
      v-if="filterData.device.length > 0">
      <div class="px-6">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead class="w-[2%]">
                <!-- <Checkbox
                id="selectAll"
                v-model="selectAll"
                :checked="selectAll"
                @click="toggleSelectAll()" /> -->
              </TableHead>
              <TableHead class="w-[5%]">
                <Button
                  class="flex justify-start w-full p-0 hover:bg-rcgray-800"
                  variant="ghost"
                  @click="toggleSort('id')">
                  <span class="ml-2">ID</span>
                </Button>
              </TableHead>
              <TableHead class="w-[20%]">
                <Button
                  class="flex justify-start w-full p-0 hover:bg-rcgray-800"
                  variant="ghost"
                  @click="toggleSort('cred_name')">
                  <span class="ml-2">Name</span>
                </Button>
              </TableHead>
              <TableHead class="w-[30%]">Command</TableHead>
              <TableHead class="w-[30%]">Filename</TableHead>
              <TableHead class="w-[10%]">Created</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <template v-if="isLoading">
              <Loading />
            </template>

            <template v-else-if="!isLoading">
              <TableRow
                @click="toggleSingleSelectRow(row.id)"
                class="cursor-pointer hover:bg-rcgray-800"
                v-for="row in results.data"
                :key="row.id">
                <TableCell class="text-start">
                  <Checkbox
                    class="cursor-pointer"
                    :id="'select-' + row.id"
                    :checked="selectedRows.includes(row.id) ? true : false"
                    @click="toggleSingleSelectRow(row.id)" />
                </TableCell>
                <TableCell class="text-start">
                  {{ row.id }}
                </TableCell>
                <TableCell class="text-start">
                  <!-- <Button
                    class="px-2 py-0 text-sm hover:bg-rcgray-800 rounded-xl"
                    variant="ghost"
                    @click="editCred(row.id)">
                    <span class="border-b">{{ row.cred_name }}</span>
                  </Button> -->
                </TableCell>
                <TableCell class="text-start">
                  {{ row.command }}
                </TableCell>
                <TableCell class="text-start">
                  {{ row.config_filename }}
                </TableCell>
                <TableCell class="text-start">
                  {{ formatters.formatTime(row.created_at) }}
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
          :lastPage="lastPage"
          :perPage="perPage"
          @update:currentPage="currentPage = $event"
          @update:perPage="perPage = $event" />
        <!-- END PAGINATION -->

        <div
          class="flex items-center mt-4"
          v-if="selectedRows.length > 0">
          <Badge
            variant="outline"
            class="py-1 mt-1 bg-rcgray-800">
            <Icon
              icon="mdi:check-circle"
              class="mr-2 text-green-500" />
            <span class="text-sm">Config ID {{ selectedRows }} selected for comparison</span>
          </Badge>
        </div>
      </div>
    </div>

    <!-- Separator -->
    <div class="w-full my-4 border-t"></div>
  </div>
</template>
