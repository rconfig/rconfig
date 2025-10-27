<script setup>
import { ref } from 'vue';
import { ScrollArea } from '@/components/ui/scroll-area';
import { useDeviceComments } from '@/pages/Inventory/Devices/Comments/useDeviceComments';
import CommentsEmpty from '@/pages/Inventory/Devices/Comments/CommentsEmpty.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuGroup, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import CommentsList from '@/pages/Inventory/Devices/Comments/CommentsList.vue';
import Loading from '@/pages/Shared/Loaders/Loading.vue';
import NewCommentCard from '@/pages/Inventory/Devices/Comments/NewCommentCard.vue';

const emit = defineEmits(['commentsaved']);

const props = defineProps({
  deviceId: {
    type: Number,
    required: true
  },
  deviceName: {
    type: String,
    required: true
  },
  isDeviceCommentsPanelView: {
    type: Boolean,
    required: false,
    default: false
  }
});
const { changeView, comments, isLoading, saveComment, closeComment, deleteComment, activeCommentsView, newCommentKey } = useDeviceComments(props, emit);
</script>

<template>
  <ScrollArea class="h-[90vh]">
    <div class="mt-4 mr-3 space-y-4">
      <div class="flex items-center justify-between ml-1 text-sm">
        <span>All {{ activeCommentsView ? 'Open' : 'Closed' }} Comments</span>
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="ghost"><Icon icon="mage:filter"></Icon></Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent
            side="bottom"
            align="end">
            <DropdownMenuGroup>
              <DropdownMenuItem @click="changeView()">
                <span class="cursor-pointer">Show {{ activeCommentsView ? 'resolved' : 'open' }} comments</span>
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

      <Loading
        v-if="isLoading"
        class="mt-4" />

      <div
        v-if="!isLoading"
        class="space-y-4">
        <CommentsList
          v-if="comments.length > 0"
          @resolveComment="closeComment($event)"
          @deleteComment="deleteComment($event)"
          :comments="comments" />

        <CommentsEmpty v-if="comments.length === 0" />
      </div>
    </div>
  </ScrollArea>
</template>
