<script setup>
import { onMounted, ref } from 'vue';
import { Sheet, SheetClose, SheetContent, SheetDescription, SheetFooter, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useSheetStore } from '@/stores/sheetActions';
import Loading from '@/pages/Shared/Loading.vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';

const props = defineProps({
  deviceId: {
    type: Number,
    required: true
  },
  deviceName: {
    type: String,
    required: true
  }
});
const sheetStore = useSheetStore();
const { openSheet, closeSheet, isSheetOpen } = sheetStore;
const comments = ref([]);
const isLoading = ref(false);

onMounted(() => {
  if (isSheetOpen('DeviceCommentSheet')) {
    getComments();
  }
});

function getComments() {
  isLoading.value = true;
  axios
    .get(`/api/device-comments/${props.deviceId}`)
    .then(response => {
      comments.value = response.data;
      isLoading.value = false;
    })
    .catch(error => {
      console.error(error);
    });
}
function viewDevice(deviceId) {
  closeSheet('DeviceCommentSheet');
  router.push({ name: 'device', params: { id: deviceId } });
}
</script>

<template>
  <Sheet :open="isSheetOpen('DeviceCommentSheet')">
    <!-- <SheetTrigger></SheetTrigger> -->
    <SheetContent
      class="h-[96vh] m-6 rounded-lg border"
      @escapeKeyDown="closeSheet('DeviceCommentSheet')"
      @pointerDownOutside="closeSheet('DeviceCommentSheet')"
      @closeClicked="closeSheet('DeviceCommentSheet')">
      <Loading v-if="isLoading" />

      <br />
      <SheetHeader v-if="!isLoading">
        <SheetTitle>Comments</SheetTitle>
        <SheetDescription>
          <span class="text-muted-foreground">Comments on</span>
          <Button
            class="px-2 py-0 hover:bg-rcgray-800 rounded-xl"
            variant="ghost"
            @click="viewDevice(deviceId)">
            <span class="border-b">{{ deviceName }}</span>
          </Button>
        </SheetDescription>
      </SheetHeader>
      <transition name="fade">
        <div v-if="!isLoading">
          <div class="mt-4">
            <Card v-for="comment in comments">
              <CardHeader class="p-2 pt-0 md:p-4">
                <CardTitle>
                  <div class="flex justify-between">
                    <span>{{ comment.user.name }}</span>
                    <span class="text-sm text-muted-foreground">{{ new Date(comment.created_at).toLocaleString() }}</span>
                  </div>
                </CardTitle>
                <CardDescription>{{ comment.comment }}</CardDescription>
              </CardHeader>
              <CardContent class="flex justify-end p-2 pt-0 md:p-4 md:pt-0">
                <Button
                  v-if="comment.is_open"
                  size="sm"
                  variant="ghost"
                  class="py-2">
                  <Icon icon="solar:check-circle-broken"></Icon>
                </Button>
              </CardContent>
            </Card>
          </div>
        </div>
      </transition>
      <SheetFooter>
        <div class="flex-1"></div>
      </SheetFooter>
    </SheetContent>
  </Sheet>
</template>
