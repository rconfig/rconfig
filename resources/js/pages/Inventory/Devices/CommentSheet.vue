<script setup>
import Loading from '@/pages/Shared/Loading.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Sheet, SheetContent, SheetDescription, SheetFooter, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { onMounted, ref } from 'vue';
import { useDeviceComments } from './useDeviceComments';
import { Switch } from '@/components/ui/switch';

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

const { changeView, addComment, closeSheet, comments, formatters, isLoading, isSheetOpen, saveComment, viewDevice, closeComment, activeCommentsView, closedCommentsView } = useDeviceComments(props, emit);
</script>

<template>
  <Sheet :open="isSheetOpen('DeviceCommentSheet')">
    <SheetContent
      class="h-[96vh] m-6 rounded-lg border"
      @escapeKeyDown="closeSheet('DeviceCommentSheet')"
      @pointerDownOutside="closeSheet('DeviceCommentSheet')"
      @closeClicked="closeSheet('DeviceCommentSheet')">
      <SheetHeader>
        <SheetTitle>
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
          <div class="flex justify-between">
            <div class="flex justify-start">
              <div class="flex items-center space-x-2">
                <Switch
                  id="airplane-mode"
                  @click="changeView()" />
                <Label for="airplane-mode">View {{ activeCommentsView ? 'Closed ' : 'Active ' }}Comments</Label>
              </div>
            </div>
            <Button
              variant="ghost"
              class="float-right"
              @click="addComment">
              <CommentAddIcon class="text-muted-foreground" />
            </Button>
          </div>
        </SheetDescription>
      </SheetHeader>
      <Loading v-if="isLoading" />
      <!-- ACTIVE COMMENTS -->
      <transition name="fade">
        <div v-if="!isLoading">
          <ScrollArea class="h-[85vh]">
            <div class="mt-4 mr-4 space-y-4">
              <Separator />

              <div
                v-if="comments.length === 0"
                class="flex flex-col items-center pt-48 text-sm text-center text-muted-foreground">
                <Icon
                  icon="fluent-color:chat-bubbles-question-20"
                  class="mb-2 text-4xl" />
                <span>No Comments, yet.</span>
              </div>

              <Card
                v-if="comments.length > 0"
                v-for="(comment, index) in comments"
                :key="index">
                <CardHeader class="p-2">
                  <CardTitle>
                    <div class="flex items-center justify-start">
                      <Icon
                        icon="ri:user-line"
                        class="text-muted-foreground" />
                      <span class="ml-2 text-muted-foreground">{{ comment.user.name }}</span>
                    </div>
                  </CardTitle>
                  <CardDescription></CardDescription>
                </CardHeader>
                <CardContent class="flex justify-end px-2 py-0">
                  <Textarea
                    v-if="comment.is_open"
                    v-model="comment.comment"
                    rows="1"
                    class="mt-2" />
                  <Textarea
                    v-else
                    class="border-none"
                    v-model="comment.comment"
                    disabled></Textarea>
                </CardContent>
                <CardFooter class="flex justify-between px-2 py-1">
                  <div class="text-sm text-muted-foreground">{{ formatters.formatTime(comment.created_at) }}</div>
                  <div class="flex items-center">
                    <Button
                      v-if="comment.is_open"
                      @click="saveComment(index)"
                      variant="outline"
                      size="sm"
                      class="">
                      Save
                    </Button>
                    <div v-if="comment.is_open">
                      <Button
                        title="Resolve Comment"
                        size="sm"
                        @click="closeComment(comment.id)"
                        variant="ghost"
                        class="py-2 group">
                        <Icon
                          icon="solar:check-circle-broken"
                          class="group-hover:text-green-500"></Icon>
                      </Button>
                    </div>
                  </div>
                </CardFooter>
              </Card>
            </div>
          </ScrollArea>
        </div>
      </transition>
      <!-- ACTIVE COMMENTS -->

      <SheetFooter>
        <div class="flex-1"></div>
      </SheetFooter>
    </SheetContent>
  </Sheet>
</template>
