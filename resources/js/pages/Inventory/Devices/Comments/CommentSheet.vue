<script setup>
import Loading from '@/pages/Shared/Loading.vue';
import CommentsList from '@/pages/Inventory/Devices/Comments/CommentsList.vue';
import NewCommentCard from '@/pages/Inventory/Devices/Comments/NewCommentCard.vue';
import CommentsEmpty from '@/pages/Inventory/Devices/Comments/CommentsEmpty.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuPortal, DropdownMenuSeparator, DropdownMenuShortcut, DropdownMenuSub, DropdownMenuSubContent, DropdownMenuSubTrigger, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';

import { ScrollArea } from '@/components/ui/scroll-area';
import { Sheet, SheetContent, SheetDescription, SheetFooter, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { onMounted, ref } from 'vue';
import { useDeviceComments } from '@/pages/Inventory/Devices/Comments/useDeviceComments';

const emit = defineEmits(['commentsaved']);

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

const { changeView, addComment, closeSheet, comments, formatters, isLoading, isSheetOpen, saveComment, viewDevice, closeComment, activeCommentsView, closedCommentsView, newCommentKey } = useDeviceComments(props, emit);
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
          Comments
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

          <!-- <div class="flex justify-between">
            <Button
              variant="ghost"
              class="float-right"
              @click="addComment">
              <CommentAddIcon class="text-muted-foreground" />
            </Button>
          </div> -->
        </SheetDescription>
      </SheetHeader>
      <Loading
        v-if="isLoading"
        class="mt-4" />

      <transition name="fade">
        <div v-if="!isLoading">
          <ScrollArea class="h-[85vh]">
            <div class="mt-4 space-y-4">
              <div class="flex items-center justify-between ml-1 text-sm">
                <span>All Comments</span>
                <DropdownMenu>
                  <DropdownMenuTrigger as-child>
                    <Button variant="ghost"><Icon icon="mage:filter"></Icon></Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent
                    side="bottom"
                    align="end">
                    <DropdownMenuGroup>
                      <DropdownMenuItem>
                        <span class="cursor-pointer">Show resolved comments</span>
                        <!-- <DropdownMenuShortcut>⇧⌘P</DropdownMenuShortcut> -->
                      </DropdownMenuItem>
                    </DropdownMenuGroup>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
              <NewCommentCard
                :key="newCommentKey"
                class="mt-2"
                @submitComment="saveComment($event)" />
              <Separator />
              <CommentsList
                v-if="comments.length > 0"
                :comments="comments" />

              <CommentsEmpty v-if="comments.length === 0" />
            </div>
          </ScrollArea>
        </div>
      </transition>
      <!-- 
      <SheetFooter>
        <div class="flex-1"></div>
      </SheetFooter> -->
    </SheetContent>
  </Sheet>
</template>
