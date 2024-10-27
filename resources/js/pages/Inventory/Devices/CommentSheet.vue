<script setup>
import Loading from '@/pages/Shared/Loading.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import { Sheet, SheetContent, SheetDescription, SheetFooter, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { onMounted, ref } from 'vue';
import { useDeviceComments } from './useDeviceComments';

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

const { addComment, closeSheet, comments, formatters, isLoading, isSheetOpen, saveComment, viewDevice } = useDeviceComments(props);
</script>

<template>
  <Sheet :open="isSheetOpen('DeviceCommentSheet')">
    <SheetContent
      class="h-[96vh] m-6 rounded-lg border"
      @escapeKeyDown="closeSheet('DeviceCommentSheet')"
      @pointerDownOutside="closeSheet('DeviceCommentSheet')"
      @closeClicked="closeSheet('DeviceCommentSheet')">
      <Loading v-if="isLoading" />
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
          <Button
            variant="ghost"
            class="float-right"
            @click="addComment">
            <CommentAddIcon class="text-muted-foreground" />
          </Button>
        </SheetDescription>
      </SheetHeader>
      <transition name="fade">
        <div v-if="!isLoading">
          <div class="mt-4 space-y-4">
            <Card
              v-for="(comment, index) in comments"
              :key="index">
              <CardHeader class="p-2">
                <CardTitle>
                  <span>{{ comment.user.name }}</span>
                </CardTitle>
                <CardDescription></CardDescription>
              </CardHeader>
              <CardContent class="flex justify-end px-2 py-0">
                <Textarea
                  v-if="comment.isEditable"
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
                <div>
                  <Button
                    v-if="comment.isEditable"
                    @click="saveComment(index)"
                    variant="outline"
                    size="sm"
                    class="">
                    Save
                  </Button>
                  <div v-if="!comment.isEditable">
                    <Button
                      title="Resolve Comment"
                      v-if="comment.is_open"
                      size="sm"
                      variant="ghost"
                      class="py-2">
                      <Icon icon="solar:check-circle-broken"></Icon>
                    </Button>
                  </div>
                </div>
              </CardFooter>
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
