<script setup lang="ts">
import axios from 'axios';
import Loading from '@/pages/Shared/Loading.vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { ScrollArea } from '@/components/ui/scroll-area'; // Import ScrollArea component
import { onMounted, onUnmounted, ref, nextTick, watch, inject } from 'vue';
import { useClipboard } from '@vueuse/core';
import { useDebounceFn } from '@vueuse/core';
import { useDialogStore } from '@/stores/dialogActions';
import { useRoute, useRouter } from 'vue-router'; // Import the useRoute from Vue Router

const router = useRouter();
const dialogStore = useDialogStore();
const formatters = inject('formatters');
const hoverIcons = ref({});
const searchTerm = ref('');
const isLoadingResults = ref(false);
const { closeDialog, isDialogOpen, openDialog } = dialogStore;
const { text, copy, copied, isSupported } = useClipboard();
const searchResults = ref({
  devices: [],
  categories: [],
  commands: [],
  vendors: [],
  templates: [],
  tags: [],
  tasks: []
});
const selectedResult = ref({
  type: '',
  record: []
});

function handleKeyDown(event) {
  if (event.ctrlKey && event.key === '/') {
    // Ctrl + / to open the dialog
    event.preventDefault();
    customOpenDialog();
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
});

// Cleanup event listener on unmount
onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});

function customOpenDialog() {
  openDialog('QuickSearchDialog');
}

function customCloseDialog() {
  closeDialog('QuickSearchDialog');
  // clear searchResults
  searchResults.value = {
    devices: [],
    categories: [],
    commands: [],
    vendors: [],
    templates: [],
    tags: [],
    tasks: []
  };
  // clear selectedResult
  selectedResult.value = {
    type: '',
    record: []
  };
  searchTerm.value = '';
}

const debouncedFilter = useDebounceFn(() => {
  fetchSearch();
}, 500);

watch(searchTerm, () => {
  debouncedFilter();
});

const fetchSearch = () => {
  isLoadingResults.value = true;

  axios.get(`/api/search?q=${searchTerm.value}`).then(response => {
    searchResults.value = response.data;
    isLoadingResults.value = false;
  });
};

function selectRecord(type, record) {
  //clear selectedResult
  selectedResult.value = {
    type: '',
    record: []
  };
  selectedResult.value.type = type;
  selectedResult.value.record.push(record);
}

function formatKey(key) {
  return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function formatValue(value) {
  if (typeof value === 'string' && !isNaN(Date.parse(value))) {
    return formatters.formatTime(value);
  }
  return value;
}

const handleMouseOver = key => {
  hoverIcons.value[key] = true;
};

const handleMouseLeave = key => {
  hoverIcons.value[key] = false;
};

function openRecord() {
  switch (selectedResult.value.type) {
    case 'device':
      router.push({ path: '/device/view/' + selectedResult.value.record[0].id });
      break;
    case 'category':
      router.push({ path: '/commandgroups' });
      break;
    case 'command':
      router.push({ path: '/commands' });
      break;
    case 'vendor':
      router.push({ path: '/vendors' });
      break;
    case 'template':
      router.push({ path: '/templates/view/' + selectedResult.value.record[0].id });
      break;
    case 'tag':
      router.push({ path: '/tags' });
      break;
    case 'task':
      router.push({ path: '/tasks' });
      break;
    default:
      console.log('No record selected');
  }
  customCloseDialog();
}
</script>

<template>
  <Dialog :open="isDialogOpen('QuickSearchDialog')">
    <DialogTrigger as-child>
      <Button
        @click="customOpenDialog()"
        id="quickSearchBtn"
        variant=" "
        class="w-full dark:border-rcgray-600 border-rcgray-400 dark:hover:bg-rcgray-800 m-0 outline-none no-underline flex items-center flex-shrink-0 overflow-hidden rounded-lg shadow-inner shadow-[0_0_0_1px_rgb(49,51,55)] dark:bg-rcgray-900 bg-rcgray-200 hover:bg-rcgray-100 h-7 p-[4px_6px] gap-1.5 relative transition-[background]"
        data-full-width="true">
        <Icon icon="carbon:search" />

        <div class="font-inter tracking-[-0.02em] font-medium leading-[20px] text-[14px] text-rcgray-900 dark:text-rcgray-400">Actions & Search</div>
        <div class="ml-auto">
          <kbd class="rc-kdb-class">Ctrl /</kbd>
        </div>
      </Button>
    </DialogTrigger>
    <DialogContent
      @escapeKeyDown="customCloseDialog()"
      @close="customCloseDialog()"
      @pointerDownOutside="customCloseDialog()"
      class="grid-rows-[auto_minmax(0,1fr)_auto] p-0 gap-0 max-h-[50dvh] focus:outline-none"
      :class="selectedResult['record'].length > 0 ? 'sm:max-w-[80dvh]' : 'sm:max-w-[60dvh]'">
      <DialogHeader class="rc-dialog-header">
        <DialogTitle class="w-full p-0">
          <Input
            :id="'quickSearchInput'"
            v-model="searchTerm"
            :focus="true"
            class="text-sm font-normal leading-loose border-none placeholder:text-rcgray-400 placeholder:font-light placeholder:leading-loose"
            placeholder="Search Records..." />
        </DialogTitle>
      </DialogHeader>
      <div
        class="grid gap-4 px-6"
        v-if="searchTerm === ''">
        <div class="flex w-full justify-center items-center h-[40dvh]">
          <div class="text-center text-muted-foreground">Type to search for records</div>
        </div>
      </div>

      <div
        class="grid gap-4 px-6"
        v-if="searchTerm != ''">
        <div class="flex w-full justify-between h-[40dvh]">
          <ScrollArea
            class="h-full pr-4"
            :class="selectedResult['record'].length > 0 ? 'w-1/2 border-r' : 'w-full '">
            <!-- Scrollable RECORDS section -->
            <div class="h-full">
              <!-- {{ searchResults }} -->

              <!-- LOADER -->
              <div
                class="grid gap-4 px-6"
                v-if="isLoadingResults">
                <div class="flex w-full justify-center items-center h-[40dvh]">
                  <div class="text-center text-muted-foreground">
                    <Loading />
                  </div>
                </div>
              </div>
              <!-- LOADER -->

              <div class="w-full py-1 mt-2 text-xs uppercase text-muted-foreground">SEARCH RESULTS</div>

              <!-- DEVICES RESULTS -->
              <div
                class="flex flex-wrap justify-between w-full gap-1 p-1"
                v-if="searchResults['devices'].length > 0">
                <div
                  class="flex items-center justify-between w-full px-2 py-1 text-sm cursor-pointer hover:bg-rcgray-800 rounded-xl"
                  :key="device.id"
                  @click="selectRecord('device', device)"
                  v-for="device in searchResults['devices']">
                  <div class="flex flex-wrap items-center">
                    <div class="text-sm">{{ device.device_name }}</div>
                    <div class="ml-2 text-xs text-muted-foreground">{{ device.device_ip }}</div>
                  </div>
                  <div class="hidden text-xs text-muted-foreground sm:block">
                    <Badge class="flex justify-end px-1 scroll-py-0.5 border border-blue-800 bg-blue-900/30 hover:bg-blue-900/30 text-slate-50 min-w-fit">
                      <DeviceIcon class="w-4 h-4" />
                      <span class="ml-1 text-rcgray-300">Device</span>
                    </Badge>
                  </div>
                </div>
              </div>

              <!-- COMMAND GROUP RESULTS -->
              <div
                class="flex flex-wrap justify-between w-full gap-1 p-1"
                v-if="searchResults['categories'].length > 0">
                <div
                  class="flex items-center justify-between w-full px-2 py-1 text-sm cursor-pointer hover:bg-rcgray-800 rounded-xl"
                  :key="category.id"
                  @click="selectRecord('category', category)"
                  v-for="category in searchResults['categories']">
                  <div class="flex flex-wrap items-center">
                    <div class="text-sm">{{ category.categoryName }}</div>
                    <!-- <div class="ml-2 text-xs text-muted-foreground">{{ device.categoryName }}</div> -->
                  </div>
                  <div class="hidden text-xs text-muted-foreground sm:block">
                    <Badge class="flex justify-end px-1 scroll-py-0.5 border border-emerald-800 bg-emerald-900/30 hover:bg-emerald-900/30 text-slate-50 min-w-fit">
                      <CommandGroupIcon class="w-4 h-4" />
                      <span class="ml-1 text-rcgray-300">Command Group</span>
                    </Badge>
                  </div>
                </div>
              </div>

              <!-- COMMANDS RESULTS -->
              <div
                class="flex flex-wrap justify-between w-full gap-1 p-1"
                v-if="searchResults['commands'].length > 0">
                <div
                  class="flex items-center justify-between w-full px-2 py-1 text-sm cursor-pointer hover:bg-rcgray-800 rounded-xl"
                  :key="command.id"
                  @click="selectRecord('command', command)"
                  v-for="command in searchResults['commands']">
                  <div class="flex flex-wrap items-center">
                    <div class="text-sm">{{ command.command }}</div>
                    <!-- <div class="ml-2 text-xs text-muted-foreground">{{ device.categoryName }}</div> -->
                  </div>
                  <div class="hidden text-xs text-muted-foreground sm:block">
                    <Badge class="flex justify-end px-1 scroll-py-0.5 border border-cyan-800 bg-cyan-900/30 hover:bg-cyan-900/30 text-slate-50 min-w-fit">
                      <CommandsIcon class="w-4 h-4" />
                      <span class="ml-1 text-rcgray-300">Command</span>
                    </Badge>
                  </div>
                </div>
              </div>

              <!-- VENDOR RESULTS -->
              <div
                class="flex flex-wrap justify-between w-full gap-1 p-1"
                v-if="searchResults['vendors'].length > 0">
                <div
                  class="flex items-center justify-between w-full px-2 py-1 text-sm cursor-pointer hover:bg-rcgray-800 rounded-xl"
                  :key="vendor.id"
                  @click="selectRecord('vendor', vendor)"
                  v-for="vendor in searchResults['vendors']">
                  <div class="flex flex-wrap items-center">
                    <div class="text-sm">{{ vendor.vendorName }}</div>
                    <!-- <div class="ml-2 text-xs text-muted-foreground">{{ device.categoryName }}</div> -->
                  </div>
                  <div class="hidden text-xs text-muted-foreground sm:block">
                    <Badge class="flex justify-end px-1 scroll-py-0.5 border border-emerald-800 bg-emerald-900/30 hover:bg-emerald-900/30 text-slate-50 min-w-fit">
                      <VendorIcon class="w-4 h-4" />
                      <span class="ml-1 text-rcgray-300">Vendor</span>
                    </Badge>
                  </div>
                </div>
              </div>

              <!-- TEMPLATE RESULTS -->
              <div
                class="flex flex-wrap justify-between w-full gap-1 p-1"
                v-if="searchResults['templates'].length > 0">
                <div
                  class="flex items-center justify-between w-full px-2 py-1 text-sm cursor-pointer hover:bg-rcgray-800 rounded-xl"
                  :key="template.id"
                  @click="selectRecord('template', template)"
                  v-for="template in searchResults['templates']">
                  <div class="flex flex-wrap items-center">
                    <div class="text-sm">{{ template.templateName }}</div>
                    <!-- <div class="ml-2 text-xs text-muted-foreground">{{ device.categoryName }}</div> -->
                  </div>
                  <div class="hidden text-xs text-muted-foreground sm:block">
                    <Badge class="flex justify-end px-1 scroll-py-0.5 border border-teal-800 bg-teal-900/30 hover:bg-teal-900/30 text-slate-50 min-w-fit">
                      <TemplateIcon class="w-4 h-4" />
                      <span class="ml-1 text-rcgray-300">Template</span>
                    </Badge>
                  </div>
                </div>
              </div>

              <!-- TAGS RESULTS -->
              <div
                class="flex flex-wrap justify-between w-full gap-1 p-1"
                v-if="searchResults['tags'].length > 0">
                <div
                  class="flex items-center justify-between w-full px-2 py-1 text-sm cursor-pointer hover:bg-rcgray-800 rounded-xl"
                  :key="tag.id"
                  @click="selectRecord('tag', tag)"
                  v-for="tag in searchResults['tags']">
                  <div class="flex flex-wrap items-center">
                    <div class="text-sm">{{ tag.tagname }}</div>
                    <!-- <div class="ml-2 text-xs text-muted-foreground">{{ tag.categoryName }}</div> -->
                  </div>
                  <div class="hidden text-xs text-muted-foreground sm:block">
                    <Badge class="flex justify-end px-1 scroll-py-0.5 border border-indigo-800 bg-indigo-900/30 hover:bg-indigo-900/30 text-slate-50 min-w-fit">
                      <TagIcon class="w-4 h-4" />
                      <span class="ml-1 text-rcgray-300">Tag</span>
                    </Badge>
                  </div>
                </div>
              </div>

              <!-- TASKS RESULTS -->
              <div
                class="flex flex-wrap justify-between w-full gap-1 p-1"
                v-if="searchResults['tasks'].length > 0">
                <div
                  class="flex items-center justify-between w-full px-2 py-1 text-sm cursor-pointer hover:bg-rcgray-800 rounded-xl"
                  :key="task.id"
                  @click="selectRecord('task', task)"
                  v-for="task in searchResults['tasks']">
                  <div class="flex flex-wrap items-center">
                    <div class="text-sm">{{ task.task_name }}</div>
                    <!-- <div class="ml-2 text-xs text-muted-foreground">{{ tag.categoryName }}</div> -->
                  </div>
                  <div class="hidden text-xs text-muted-foreground sm:block">
                    <Badge class="flex justify-end px-1 scroll-py-0.5 border border-orange-800 bg-orange-900/30 hover:bg-orange-900/30 text-slate-50 min-w-fit">
                      <TasksIcon class="w-4 h-4" />
                      <span class="ml-1 text-rcgray-300">Task</span>
                    </Badge>
                  </div>
                </div>
              </div>
            </div>
          </ScrollArea>
          <transition name="fade">
            <ScrollArea
              :class="selectedResult['record'].length > 0 ? 'w-1/2 h-full pl-4' : 'hidden'"
              v-if="selectedResult['record'].length > 0">
              <!-- Scrollable DETAILS section -->
              <div class="h-full">
                <div class="flex justify-between w-full py-1 pr-4 mt-2 text-xs uppercase border-b text-muted-foreground">
                  <div>DETAILS - {{ selectedResult['type'] }}</div>
                  <Icon
                    :icon="copied ? 'material-symbols:check-circle-outline' : !copied ? 'material-symbols:content-copy' : 'material-symbols:content-copy-outline'"
                    :class="copied ? 'text-green-500' : 'text-gray-500'"
                    class="ml-2 cursor-pointer hover:text-gray-700"
                    @mouseover="handleMouseOver('record')"
                    @mouseleave="handleMouseLeave('record')"
                    @click="copy(JSON.stringify(selectedResult.record[0]))" />
                </div>
                <!-- <pre> {{ selectedResult }}</pre> -->
                <div class="grid gap-3">
                  <dl class="grid gap-1">
                    <div
                      v-for="(value, key) in selectedResult.record[0]"
                      :key="key">
                      <div
                        class="flex items-center justify-between pr-4"
                        v-if="!['id', 'created_at', 'view_url'].includes(key)">
                        <dt class="text-sm text-muted-foreground">{{ formatKey(key) }}</dt>
                        <dd class="flex items-center gap-2 text-sm">{{ formatValue(value) }}</dd>
                      </div>
                    </div>
                  </dl>
                </div>
              </div>
            </ScrollArea>
          </transition>
        </div>
      </div>
      <DialogFooter class="flex flex-row justify-between gap-2 pt-2 pb-2 pl-3 pr-2 border-t sm:justify-between bg-rcgray-800">
        <div class="flex items-center text-muted-foreground">
          <Icon
            icon="solar:arrow-up-linear"
            class="border" />
          <Icon
            icon="solar:arrow-down-linear"
            class="mx-1 border" />
          <span class="pl-2 text-sm">Navigate</span>
        </div>
        <div>
          <Button
            type="close"
            variant="outline"
            class="px-2 py-1 ml-2 text-sm hover:bg-gray-700 hover:animate-pulse"
            @click="customCloseDialog()"
            size="sm">
            Close/
            <div class="pl-2 ml-auto">
              <kbd class="rc-kdb-class">ESC</kbd>
            </div>
          </Button>

          <Button
            v-if="selectedResult.record[0]"
            type="submit"
            class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
            size="sm"
            @click="openRecord()"
            variant="primary">
            Open Record
            <div class="pl-2 ml-auto">
              <kbd class="rc-kdb-class2">
                Ctrl&nbsp;
                <Icon
                  icon="uil:enter"
                  class="" />
              </kbd>
            </div>
          </Button>
        </div>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
