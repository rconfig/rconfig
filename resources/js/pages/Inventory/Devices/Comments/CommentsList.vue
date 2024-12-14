<script setup>
import { ref, inject } from 'vue';
import CommentsListMenu from '@/pages/Inventory/Devices/Comments/CommentsListMenu.vue';

const props = defineProps({
  comments: {
    type: Array,
    required: true
  }
});
const formatters = inject('formatters');
</script>

<template>
  <!-- COMMENTS LIST -->
  <div v-for="comment in comments">
    <div
      class="relative p-3 transition-colors border border-gray-700 shadow-inner cursor-pointer rounded-xl bg-rcgray-800 hover:bg-rcgray-900 group"
      style="border-color: rgb(49, 51, 55)">
      <div class="flex flex-col items-stretch justify-start gap-2 group">
        <div class="relative flex flex-col gap-1">
          <div class="flex flex-row items-start justify-between gap-1.5 h-6 mb-[-1]">
            <div class="flex flex-row items-center gap-1.5">
              <span class="relative inline-flex items-center justify-center w-4 h-4 overflow-hidden rounded-full">
                <span class="flex items-center justify-center w-full h-full bg-blue-700 text-blue-100 font-semibold text-[10px] uppercase">{{ formatters.getFirstLetterCapitalized(comment.user.name) }}</span>
              </span>
              <div class="flex flex-row items-baseline justify-start min-w-0 gap-1">
                <div
                  class="text-sm font-semibold text-white truncate"
                  style="color: rgb(238, 239, 241)">
                  {{ comment.user.name }}
                </div>
                <div class="text-xs text-gray-400 truncate">{{ formatters.timeFrom(comment.created_at) }}</div>
              </div>
            </div>
          </div>
          <div
            data-indent="true"
            class="flex pl-[22px]">
            <div class="flex flex-1 flex-col items-stretch justify-start gap-1.5 min-w-0">
              <div class="min-w-0">
                <div
                  class="flex flex-wrap text-sm font-medium text-white break-words truncate"
                  data-expanded="false"
                  style="color: rgb(238, 239, 241)">
                  <div>
                    <div
                      aria-autocomplete="none"
                      aria-readonly="true"
                      contenteditable="false"
                      role="textbox"
                      spellcheck="true"
                      class="break-words whitespace-pre-wrap select-text">
                      <p dir="ltr">
                        <span>{{ comment.comment }}</span>
                      </p>
                    </div>
                  </div>
                </div>
              </div>
              <div></div>
            </div>
          </div>
        </div>
      </div>
      <transition name="fade">
        <div class="hidden group-hover:block">
          <CommentsListMenu />
        </div>
      </transition>
    </div>
  </div>
</template>
