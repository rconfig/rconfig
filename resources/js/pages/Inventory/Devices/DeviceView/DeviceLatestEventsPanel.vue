<script setup>
import { ref, onMounted } from 'vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import DeviceNotificationHoverCard from '@/pages/Inventory/Devices/DeviceView/DeviceNotificationHoverCard.vue';

const props = defineProps({
  deviceId: Number
});
const notificationResults = ref([]);
const isLoading = ref(false);

onMounted(() => {
  getDeviceNotifications();
});

function getDeviceNotifications() {
  isLoading.value = true;
  axios
    .get('/api/activitylogs/last5/' + props.deviceId)
    .then(response => {
      // handle success
      notificationResults.value = response.data;
      isLoading.value = false;
    })
    .catch(error => {
      // handle error
      console.log(error);
    });
}
</script>

<template>
  <div>
    <div class="p-2 overflow-none">
      <div class="flex flex-row items-center justify-start gap-2 pt-1">
        <span class="inline-flex flex-row items-center h-[20px] rounded-md px-1.5 py-0.5 gap-1 shadow-[inset_0_0_0_1px_rgb(69,71,74)] bg-[#313337]">
          <span class="flex-[0_1_auto] min-w-0 overflow-hidden text-ellipsis whitespace-nowrap inline-flex line-clamp-1">Last 5 Events</span>
        </span>
        <span class="flex-[1_1_100%] bg-[#313337] flex flex-shrink-0 h-px w-full"></span>
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
        <Button
          class="flex w-full h-full border bg-rcgray-800 items-left"
          v-for="(notification, index) in notificationResults"
          :key="index">
          <div class="w-full">
            <div class="pl-6 border-l border-l-4 rounded-md border-l-rcgray-600">
              <div class="flex justify-between">
                <span
                  class="py-0.5 flex w-fit bg-green-100 text-green-800 text-xs font-medium me-2 px-1.5 py-0.5 rounded-lg dark:bg-blue-900 dark:text-blue-100"
                  v-if="notification.log_name === 'info'">
                  {{ notification.log_name }}
                </span>
                <span class="text-muted-foreground">Type: {{ notification.event_type }}</span>
              </div>
              <div class="py-2 space-y-1 text-left">
                <p class="text-sm text-white">
                  {{ notification.description }}
                </p>
                <div class="flex items-center justify-between">
                  <p class="text-muted-foreground">{{ new Date(notification.created_at).toLocaleString() }}</p>
                  <DeviceNotificationHoverCard :log="notification">
                    <Button
                      variant="outline"
                      class="h-6 px-2 text-sm text-muted-foreground">
                      View
                    </Button>
                  </DeviceNotificationHoverCard>
                </div>
              </div>
            </div>
          </div>
        </Button>
      </div>
      <div class="flex items-center justify-end mt-4 space-x-2">
        <Button
          variant="outline"
          class="text-center">
          View All >>
        </Button>
      </div>
    </div>
  </div>
</template>
