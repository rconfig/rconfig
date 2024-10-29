<script setup>
import PeekConfigDialog from '@/pages/Shared/Dialogs/PeekConfigDialog.vue';
import StatusGreenIcon from '@/pages/Shared/Icon/StatusGreenIcon.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { ref, onMounted, inject } from 'vue';
import { useDialogStore } from '@/stores/dialogActions';

const emit = defineEmits(['toggle-view']);

const props = defineProps({
  deviceId: Number
});
const dialogStore = useDialogStore();
const { openDialog, closeDialog, isDialogOpen } = dialogStore;

const latestConfigs = ref({});
const isLoading = ref(false);
const formatters = inject('formatters');

onMounted(() => {
  refreshConfigs();
});

function getlastConfigsForGivenDevice() {
  axios
    .get('/api/configs/latest-by-deviceid/' + props.deviceId)
    .then(response => {
      latestConfigs.value = response.data.data;
    })
    .catch(error => {
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
  emit('toggle-view', 'all');
}
</script>

<template>
  <div>
    <div class="p-2 overflow-none">
      <div class="flex flex-row items-center justify-start gap-2 pt-1">
        <span class="inline-flex flex-row items-center h-[24px] rounded-md px-1.5 gap-1 shadow-[inset_0_0_0_1px_rgb(69,71,74)] bg-[#313337]">
          <span class="flex-[0_1_auto] min-w-0 overflow-hidden text-ellipsis whitespace-nowrap inline-flex line-clamp-1 items-center">
            <StatusGreenIcon class="mr-2" />
            Lasted Good Config Downloads
          </span>
        </span>
        <span class="flex-1 bg-[rgb(49,51,55)] bg-[rgb(49,51,55)] flex-shrink-0 h-px w-full"></span>
        <Button
          type="button"
          title="Refresh"
          variant="ghost"
          @click="refreshConfigs"
          class="p-2 h-7">
          <Icon
            icon="charm:refresh"
            :class="isLoading ? 'w-4 h-4 text-blue-500 animate-spin' : 'w-4 h-4 text-gray-600 hover:text-blue-500'"
            class="w-4 h-4" />
        </Button>
      </div>

      <div
        class="mt-4 space-y-2"
        v-if="isLoading">
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

      <div
        class="mt-6 space-y-4"
        v-if="!isLoading">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead class="w-[2%]">ID</TableHead>
              <TableHead class="w-[10%]">Command</TableHead>
              <TableHead class="w-[10%]">Filename</TableHead>
              <TableHead class="w-[10%]">Filesize</TableHead>
              <TableHead class="w-[10%]">Downloaded At</TableHead>
              <TableHead class="w-[10%]">Status</TableHead>
              <TableHead class="w-[10%]">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="row in latestConfigs">
              <TableCell>1</TableCell>
              <TableCell>{{ row.command }}</TableCell>
              <TableCell>{{ row.config_filename }}</TableCell>
              <TableCell>{{ formatters.formatFileSize(row.config_filesize) }}</TableCell>
              <TableCell>{{ formatters.formatTime(row.created_at) }}</TableCell>
              <TableCell>
                <StatusRedIcon v-if="row.download_status === 0" />
                <StatusGreenIcon v-if="row.download_status === 1" />
                <StatusYellowIcon v-if="row.download_status === 2" />
              </TableCell>
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
            </TableRow>
          </TableBody>
        </Table>
      </div>
      <div class="flex items-center justify-end mt-4 space-x-2">
        <Button
          variant="outline"
          @click="emitToggleView"
          class="text-center">
          View All >>
        </Button>
      </div>
    </div>
  </div>
</template>
