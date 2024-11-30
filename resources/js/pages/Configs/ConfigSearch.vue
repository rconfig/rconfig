<script setup>
import ConfigSearchFilterCard from '@/pages/Configs/ConfigSearch/ConfigSearchFilterCard.vue';
import Loading from '@/pages/Shared/Loading.vue';
import axios from 'axios';
import { ScrollArea } from '@/components/ui/scroll-area';
import { ref, onBeforeUnmount, onMounted, reactive } from 'vue';

const currentPage = ref(0); // Start at 0 to ensure the first request runs
const errors = ref([]);
const isFetching = ref(false);
const isLoading = ref(false);
const lastPage = ref(1); // Start at 1 to ensure we make at least one request
const page = ref(1); // Tracks the current page for pagination
const results = ref([]);
let scrollContainer = null;
const searchModel = reactive({
  device_name: '',
  command: '',
  device_category: '',
  search_string: '',
  lines_before: 5,
  lines_after: 5,
  latest_version_only: ref(true),
  ignore_case: ref(true),
  start_date: '',
  end_date: ''
});

const onSearchCompleted = newFilters => {
  // clear results and errors
  results.value = [];
  errors.value = [];
  currentPage.value = 0;
  lastPage.value = 1;
  page.value = 1;
  searchModel.device_name = newFilters.device_name;
  searchModel.command = newFilters.command;
  searchModel.device_category = newFilters.device_category;
  searchModel.search_string = newFilters.search_string;
  searchModel.lines_before = newFilters.lines_before;
  searchModel.lines_after = newFilters.lines_after;
  searchModel.latest_version_only = newFilters.latest_version_only;
  searchModel.ignore_case = newFilters.ignore_case;
  searchModel.start_date = newFilters.start_date;
  searchModel.end_date = newFilters.end_date;

  fetchResults();
};

// Function to handle the API call to fetch more results
const fetchResults = () => {
  if (searchModel.search_string === '') {
    results.value = [];
    return;
  }

  if (isFetching.value || currentPage.value >= lastPage.value) return;

  isFetching.value = true;

  axios
    .post('/api/configs/search', {
      device_name: searchModel.device_name,
      command: searchModel.command,
      device_category: searchModel.device_category,
      search_string: searchModel.search_string,
      lines_before: searchModel.lines_before,
      lines_after: searchModel.lines_after,
      ignore_case: searchModel.ignore_case,
      start_date: searchModel.start_date + ' 00:00:00',
      end_date: searchModel.end_date + ' 23:59:59',
      latest_version_only: searchModel.latest_version_only,
      page: page.value,
      per_page: 5
    })
    .then(response => {
      if (response.data.data.length) {
        results.value = [...(results.value || []), ...response.data.data];
        currentPage.value = response.data.current_page;
        lastPage.value = response.data.last_page;
      }
      isFetching.value = false;
    })
    .catch(error => {
      console.log(error);
      errors.value = error.response.data.errors;
      isFetching.value = false;
    });
};

// Handle scroll event
const handleScroll = () => {
  if (scrollContainer) {
    const scrollBottom = scrollContainer.scrollHeight - scrollContainer.scrollTop - scrollContainer.clientHeight;

    if (scrollBottom < 200 && !isFetching.value) {
      page.value++;
      fetchResults();
    }
  }
};

onMounted(() => {
  // Find the scrollable container using the specific attribute
  scrollContainer = document.querySelector('[data-radix-scroll-area-viewport]');

  if (scrollContainer) {
    scrollContainer.addEventListener('scroll', handleScroll);
  }
});

onBeforeUnmount(() => {
  if (scrollContainer) {
    scrollContainer.removeEventListener('scroll', handleScroll);
  }
});
</script>

<template>
  <div>
    <ResizablePanelGroup
      direction="horizontal"
      class="">
      <ResizablePanel
        :default-size="25"
        :max-size="30"
        :min-size="10"
        collapsible
        :collapsed-size="0"
        ref="panelElement2"
        class="min-h-[100vh]">
        <h1 class="m-2 text-sm font-semibold">Filter Options</h1>
        <ConfigSearchFilterCard
          :isLoading="isLoading"
          @searchCompleted="onSearchCompleted" />
      </ResizablePanel>
      <ResizableHandle with-handle />
      <ResizablePanel class="min-h-[100vh]">
        <ScrollArea class="border border-none rounded-md">
          <div
            class="flex items-center justify-center"
            style="height: 60vh"
            v-if="isLoading && results.length === 0">
            <Loading class="flex justify-center" />
          </div>

          <div class="h-[90dvh]">
            <h1
              class="m-2 text-sm font-semibold"
              v-if="!isFetching">
              Results
            </h1>

            <!-- Render search results here -->
            <div
              v-for="result in results"
              :key="result.id">
              <pre>{{ result }}</pre>
            </div>

            <!-- Notice if end of results reached -->
            <div
              v-if="!isFetching && currentPage >= lastPage"
              class="flex items-center justify-center pb-8 my-8">
              <hr class="flex-grow mx-8" />
              <div>No more results to load.</div>
              <hr class="flex-grow mx-8" />
            </div>

            <!-- Loading more results indicator -->
            <div
              v-if="isFetching"
              class="flex items-center justify-center my-4">
              <Loading class="flex justify-center" />
            </div>

            <!-- No more results to load -->
            <div
              v-if="!isFetching && results.length === 0"
              class="flex items-center justify-center my-4">
              <div v-if="errors.length === 0">No results found.</div>

              <div v-if="Object.keys(errors).length > 0">
                <span
                  class="col-span-3 col-start-2 text-sm text-red-400"
                  v-if="errors.command">
                  <br />
                  {{ errors.command[0] }}
                </span>
                <span
                  class="col-span-3 col-start-2 text-sm text-red-400"
                  v-if="errors.search_string">
                  <br />
                  {{ errors.search_string[0] }}
                </span>
              </div>
            </div>
          </div>
        </ScrollArea>
      </ResizablePanel>
    </ResizablePanelGroup>
  </div>
</template>
