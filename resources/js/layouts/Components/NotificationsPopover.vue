<script setup>
import axios from 'axios';
import { ref, onMounted, inject, watch } from 'vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import Loading from '@/pages/Shared/Table/Loading.vue';
import { EmitFlags } from 'typescript';

const emit = defineEmits(['hasNotifications']);
const props = defineProps({});
const isLoading = ref(true);
const notifications = ref([]);
const formatters = inject('formatters');

onMounted(() => {
  getNotifications();
});

function getNotifications() {
  isLoading.value = true;

  axios
    .get('/api/notifications', {
      params: {
        perPage: 5
      }
    })
    .then(response => {
      notifications.value = response.data.data;
      emit('notificationsLength', notifications.value.length);
      isLoading.value = false;
    })
    .catch(error => {
      console.log(error);
    });
}

function markAsRead() {
  // foreach notification, mark as read
  isLoading.value = true;
  notifications.value.forEach(notification => {
    axios
      .put(`/api/notifications/${notification.id}`, {
        read_at: new Date()
      })
      .then(response => {
        console.log(response);
        getNotifications();
      })
      .catch(error => {
        console.log(error);
      });
  });
}
</script>

<template>
  <Popover>
    <PopoverTrigger as-child>
      <slot />
    </PopoverTrigger>
    <PopoverContent
      class="ml-6 mt-14 w-96"
      side="right">
      <div class="grid gap-4">
        <div class="space-y-2">
          <h4 class="flex justify-start font-medium leading-none">
            <NotificationIcon />
            <span class="ml-2">Notifications</span>
          </h4>
          <p class="text-sm text-muted-foreground">System notifications</p>
        </div>
        <div class="grid gap-2">
          <template v-if="isLoading">
            <Loading />
          </template>

          <div v-if="!isLoading">
            <button
              v-for="item of notifications"
              :key="item.id"
              class="flex items-start justify-between gap-2 p-3 mb-2 text-sm text-left transition-all border rounded-lg hover:bg-accent bg-rcgray-900">
              <div class="">
                <!-- <component
                  :is="item.icon"
                  :class="item.iconClass" /> -->
              </div>
              <div>
                <div class="flex flex-col w-full gap-1">
                  <div class="flex items-center">
                    <div class="flex items-center gap-2">
                      <div class="font-semibold">
                        {{ item.data.title }}
                      </div>
                    </div>
                    <div class="ml-auto text-xs text-muted-foreground">
                      <span
                        v-if="item.data.category"
                        class="">
                        {{ formatters.formatTime(item.created_at) }}
                      </span>
                    </div>
                  </div>
                </div>
                <div class="text-xs line-clamp-2 text-muted-foreground">
                  <!-- {{ item.data.substring(0, 300) }} -->
                  {{ item.data.description.substring(0, 300) }}
                </div>
              </div>
            </button>
            <Button
              class="w-full"
              @click="markAsRead()"
              variant="ghost"
              to="/notifications">
              <StatusGreenIcon />
              <span class="ml-2">Mark as Read</span>
            </Button>
          </div>
        </div>
      </div>
    </PopoverContent>
  </Popover>
</template>
