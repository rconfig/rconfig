<script setup>
import CommentsMainPanel from '@/pages/Inventory/Devices/Comments/CommentsMainPanel.vue';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetDescription, SheetFooter, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { ref, watch } from 'vue';
import { useCommentsStore } from '@/stores/useCommentsStore';
import { useDeviceComments } from '@/pages/Inventory/Devices/Comments/useDeviceComments';

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

const { closeSheet, isSheetOpen, viewDevice } = useDeviceComments(props);
const emit = defineEmits(['commentsaved']);
const commentsStore = useCommentsStore();
const commentCount = ref(commentsStore.commentCounters[props.deviceId] || 0);

// Watch for changes to the comment counter for the current device
watch(
  () => commentsStore.commentCounters[props.deviceId],
  (newCount, oldCount) => {
    // console.log(`Comment count for device ${props.deviceId} changed from ${oldCount} to ${newCount}`);
    // Add any additional logic here
    commentCount.value = newCount;
  }
);
</script>

<template>
  <Sheet :open="isSheetOpen('DeviceCommentSheet')">
    <SheetContent
      class="h-[96vh] m-6 rounded-lg border p-2"
      @escapeKeyDown="closeSheet('DeviceCommentSheet')"
      @pointerDownOutside="closeSheet('DeviceCommentSheet')"
      @closeClicked="closeSheet('DeviceCommentSheet')">
      <SheetHeader>
        <SheetTitle class="mt-2 ml-1">
          {{ commentCount }} Comments
          <span class="text-sm font-light text-muted-foreground">on</span>
          <Button
            class="px-2 py-0 hover:bg-rcgray-800 rounded-xl text-muted-foreground"
            variant="ghost"
            @click="viewDevice(deviceId)">
            <span class="border-b">{{ deviceName }}</span>
          </Button>
        </SheetTitle>
        <SheetDescription>
          <Separator />
        </SheetDescription>
      </SheetHeader>

      <transition name="fade">
        <div>
          <CommentsMainPanel
            @commentsaved="emit('commentsaved')"
            :deviceId="deviceId"
            :deviceName="deviceName" />
        </div>
      </transition>
      <!-- 
      <SheetFooter>
        <div class="flex-1"></div>
      </SheetFooter> -->
    </SheetContent>
  </Sheet>
</template>
