<script setup>
import { ref, onMounted, inject } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import useClipboard from 'vue-clipboard3';

const isLoading = ref(false);
const comments = ref([]);
const formatters = inject('formatters');

const props = defineProps({
  isLoading: Boolean,
  deviceId: Number
});

onMounted(() => {
  getComments();
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
      isLoading.value = false;
    });
}
</script>

<template>
  <div>
    <Card class="overflow-hidden">
      <CardHeader class="flex flex-row items-start p-4 bg-muted/50">
        <div class="grid gap-0.5">
          <CardTitle class="flex items-center gap-2 text-lg group">Device Comments</CardTitle>
          <CardDescription>All comments</CardDescription>
        </div>
        <div class="flex items-center gap-1 ml-auto">
          <Button
            @click="refresh()"
            size="sm"
            variant="ghost"
            class="">
            <CommentAddIcon class="text-muted-foreground" />
          </Button>
        </div>
      </CardHeader>
      <CardContent class="p-4 pt-0 text-sm">
        <div
          class="space-y-2"
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
        <transition name="fade">
          <div
            class="grid gap-3"
            v-if="!isLoading && comments">
            <dl
              class="grid gap-3"
              v-for="comment in comments">
              <div class="flex items-center justify-between">
                <dt class="text-muted-foreground">{{ formatters.formatTime(comment.created_at) }}</dt>
                <dd class="flex items-center gap-2">{{ comment.comment }}</dd>
              </div>
            </dl>
          </div>
        </transition>
      </CardContent>
      <CardFooter class="flex flex-row items-center px-6 py-3 border-t bg-muted/50">
        <div class="flex items-center gap-2 text-xs text-muted-foreground">
          <Icon icon="logos:laravel" />
          Total Comments: {{ comments.length }}
        </div>
      </CardFooter>
    </Card>
  </div>
</template>
