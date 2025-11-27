<script setup>
import PeekConfigDialog from "@/pages/Shared/Dialogs/PeekConfigDialog.vue";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { ref, onMounted, inject } from "vue";
import { useDialogStore } from "@/stores/dialogActions";
import { useRouter } from "vue-router";
import NoResults from "@/pages/Shared/Table/NoResults.vue";
import Loading from "@/pages/Shared/Table/Loading.vue";
import axios from "axios";

// Icons
import { RefreshCw, Eye as ViewAltFill, FileText as FileView } from "lucide-vue-next";

const emit = defineEmits(["toggle-view"]);

const props = defineProps({
  deviceId: Number,
});

const dialogStore = useDialogStore();
const { openDialog, closeDialog, isDialogOpen } = dialogStore;

const latestConfigs = ref({});
const isLoading = ref(false);
const formatters = inject("formatters");
const router = useRouter();
const currentRouteName = router.currentRoute.value.name;

onMounted(() => {
  refreshConfigs();
});

function getlastConfigsForGivenDevice() {
  axios
    .get("/api/configs/latest-by-deviceid/" + props.deviceId)
    .then((response) => {
      latestConfigs.value = response.data.data;
    })
    .catch((error) => {
      console.log(error);
    });
}

function refreshConfigs() {
  isLoading.value = true;
  getlastConfigsForGivenDevice();
  setTimeout(() => {
    isLoading.value = false;
  }, 1000);
}

function emitToggleView() {
  emit("toggle-view", "all");
}

function viewDetailsPane(configId) {
  router.push({
    name: "config-view",
    params: { id: parseInt(configId) },
    query: { ref: currentRouteName || "device-view", refId: props.deviceId },
  });
}
</script>

<template>
  <div>
    <div class="p-2 overflow-none">
      <div class="flex flex-row items-center justify-start gap-2 pt-1">
        <span
          class="inline-flex flex-row items-center h-[24px] rounded-md px-1.5 gap-1 shadow-[inset_0_0_0_1px_rgb(69,71,74)] bg-[#313337]"
        >
          <span
            class="flex-[0_1_auto] min-w-0 overflow-hidden text-ellipsis whitespace-nowrap inline-flex line-clamp-1 items-center"
          >
            <RcIcon name="status-green" class="mr-2" />
            <div class="ml-2">Latest Configs</div>
          </span>
        </span>
        <span class="flex-1 bg-[rgb(49,51,55)] flex-shrink-0 h-px w-full"></span>
        <Button type="button" title="Refresh" variant="ghost" @click="refreshConfigs" class="p-2 h-7">
          <RefreshCw :size="16" :class="isLoading ? 'text-blue-500 animate-spin' : 'text-gray-600 hover:text-blue-500'" />
        </Button>
      </div>

      <div class="mt-4 space-y-2" v-if="isLoading">
        <Skeleton class="w-1/2 h-4" />
        <Skeleton class="w-full h-4" />
        <Skeleton class="w-full h-4" />
        <Skeleton class="w-full h-4" />
        <Skeleton class="w-full h-4" />
        <Skeleton class="w-full h-4" />
        <Skeleton class="w-full h-4" />
        <Skeleton class="w-full h-4" />
        <Skeleton class="w-full h-4" />
      </div>

      <div class="mt-6 space-y-4" v-if="!isLoading">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead class="w-[2%]">ID</TableHead>
              <TableHead class="w-[10%]">Command</TableHead>
              <TableHead class="w-[10%]">Filename</TableHead>
              <TableHead class="w-[10%]">File size</TableHead>
              <TableHead class="w-[10%]">Downloaded at</TableHead>
              <TableHead class="w-[10%]">Status</TableHead>
              <TableHead class="w-[10%]">Actions</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <template v-if="isLoading">
              <Loading />
            </template>

            <template v-else-if="!isLoading && Object.keys(latestConfigs).length">
              <TableRow v-for="row in latestConfigs" :key="row.id">
                <TableCell>{{ row.id }}</TableCell>
                <TableCell>{{ row.command }}</TableCell>
                <TableCell>
                  <Button class="px-2 py-0 hover:bg-rcgray-800 rounded-xl" variant="ghost" @click="viewDetailsPane(row.id)">
                    <span class="border-b">{{ row.config_filename }}</span>
                  </Button>
                </TableCell>
                <TableCell>{{ row.config_filesize ? formatters.formatFileSize(row.config_filesize) : "" }}</TableCell>
                <TableCell>{{ formatters.formatTime(row.created_at) }}</TableCell>
                <TableCell>
                  <RcIcon name="status-red" v-if="row.download_status === 0" />
                  <RcIcon name="status-green" v-if="row.download_status === 1" />
                  <RcIcon name="status-yellow" v-if="row.download_status === 2" />
                </TableCell>
                <TableCell class="flex items-center">
                  <TooltipProvider>
                    <Tooltip>
                      <TooltipTrigger as-child>
                        <Button variant="ghost" @click="openDialog('peek-config-dialog-' + row.id)">
                          <ViewAltFill size="16" class="text-muted-foreground hover:text-blue-500" />
                        </Button>
                      </TooltipTrigger>
                      <TooltipContent class="text-white bg-rcgray-800">
                        <p>Peek config</p>
                      </TooltipContent>
                    </Tooltip>
                  </TooltipProvider>

                  <TooltipProvider>
                    <Tooltip>
                      <TooltipTrigger as-child>
                        <Button variant="ghost" @click="viewDetailsPane(row.id)">
                          <FileView size="16" class="text-muted-foreground hover:text-blue-500" />
                        </Button>
                      </TooltipTrigger>
                      <TooltipContent class="text-white bg-rcgray-800">
                        <p>Open config</p>
                      </TooltipContent>
                    </Tooltip>
                  </TooltipProvider>

                  <PeekConfigDialog :editId="row.id" v-if="isDialogOpen('peek-config-dialog-' + row.id)" />
                </TableCell>
              </TableRow>
            </template>

            <template v-else>
              <NoResults />
            </template>
          </TableBody>
        </Table>
      </div>

      <div class="flex items-center justify-end mt-4 space-x-2">
        <Button variant="outline" @click="emitToggleView" class="text-center">View all</Button>
      </div>
    </div>
  </div>
</template>
