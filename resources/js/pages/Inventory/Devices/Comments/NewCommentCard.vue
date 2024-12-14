<script setup>
import { ref, inject } from 'vue';

const props = defineProps({});
const emit = defineEmits(['submitComment']);

const formatters = inject('formatters');
const username = inject('username');
const dataState = ref('default');
const showPlaceholder = ref(true);
const commentContent = ref('');

// Function to handle focus/click
function handleFocus() {
  dataState.value = 'focus-within';
}

// Function to handle blur (click away)
function handleBlur() {
  dataState.value = 'default';
}

function handleInput(event) {
  const content = event.target.textContent.trim();
  commentContent.value = content; // Update commentContent
  showPlaceholder.value = content === ''; // Show placeholder if empty
}

function addComment() {
  if (commentContent.value.trim() !== '') {
    emit('submitComment', commentContent.value.trim());
  }
}
</script>

<template>
  <div style="transform: translateY(0px)">
    <div
      tabindex="0"
      @click="handleFocus"
      @focusout="handleBlur"
      class="mt-4 new-comment-border"
      aria-disabled="false"
      :data-state="dataState">
      <div class="max-h-[25vh] min-h-0 flex flex-col">
        <div
          dir="ltr"
          class="relative flex flex-col w-full h-full overflow-hidden"
          style="position: relative; --radix-scroll-area-corner-width: 0px; --radix-scroll-area-corner-height: 0px">
          <div
            data-radix-scroll-area-viewport=""
            class="flex flex-col w-full h-full flex-auto min-h-0 max-h-[400px] p-[12px_12px_0_0]"
            style="overflow: hidden scroll">
            <div data-radix-scroll-area-content="">
              <div
                aria-hidden="true"
                class="w-full"></div>
              <div class="flex flex-row items-start justify-start gap-2 pb-1 pl-[14px]">
                <span class="relative inline-flex items-center justify-center w-4 h-4 overflow-hidden rounded-full">
                  <span class="flex items-center justify-center w-full h-full bg-blue-700 text-blue-100 font-semibold text-[10px] uppercase">{{ formatters.getFirstLetterCapitalized(username) }}</span>
                </span>
                <div class="flex flex-col flex-1 min-w-0">
                  <div class="relative font-sans tracking-tight font-medium leading-5 text-sm text-[#eef0f1] [font-feature-settings:'liga','calt']">
                    <div
                      class="outline-none"
                      contenteditable="true"
                      role="textbox"
                      spellcheck="false"
                      style="user-select: text; white-space: pre-wrap; word-break: break-word"
                      data-lexical-editor="true"
                      @input="handleInput"
                      data-ms-editor="true">
                      <p>
                        <br />
                        <!-- {{ commentContent }} -->
                      </p>
                    </div>
                    <div
                      v-if="showPlaceholder"
                      class="absolute top-0 left-0 font-sans text-sm font-medium leading-5 tracking-tight text-gray-500 pointer-events-none select-none">
                      Add comment...
                    </div>
                  </div>
                </div>
              </div>
              <div
                aria-hidden="true"
                class="w-full"></div>
            </div>
          </div>
        </div>
        <div
          data-visible="false"
          data-position="bottom"
          aria-hidden="true"
          class="w-full h-px flex-none bg-[rgba(244,245,246,0.04)] transition-opacity duration-[140ms] opacity-1 data-[visible='false']:opacity-0"></div>
        <div class="flex flex-none flex-row items-center justify-between gap-2 p-[4px_8px_8px_12px]">
          <div class="flex flex-row items-center justify-start gap-2">
            <button
              type="button"
              aria-label="Mention"
              class="">
              <div
                data-is-small-ghost="true"
                class="">
                <Icon
                  icon="meteor-icons:at"
                  class="text-sm text-muted-foreground hover:text-white"></Icon>
              </div>
            </button>
          </div>

          <button
            v-if="dataState === 'default'"
            type="button"
            class="relative flex shrink-0 grow-0 items-center justify-center opacity-40 cursor-default gap-1.5 bg-[#266df0] p-[6px_10px] rounded-[9px] shadow-[inset_0_0_0_1px_rgba(244,245,246,0.1)]">
            <div class="inline-block overflow-hidden text-sm font-medium leading-5 tracking-tight align-bottom text-ellipsis whitespace-nowrap text-gray-50">Comment</div>
          </button>
          <button
            v-if="dataState === 'focus-within'"
            @mousedown.prevent
            type="button"
            @click="addComment()"
            class="relative flex shrink-0 grow-0 items-center justify-center opacity-100 cursor-pointer gap-1.5 bg-[#266df0] p-[6px_10px] rounded-[9px] shadow-[inset_0_0_0_1px_rgba(244,245,246,0.1)] transition-colors transition-shadow">
            <div class="inline-block overflow-hidden text-sm font-medium leading-5 tracking-tight align-bottom text-ellipsis whitespace-nowrap text-gray-50">Comment</div>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.new-comment-border {
  border-radius: 16px;
  background-color: rgb(33, 35, 39);
  box-shadow: rgb(49, 51, 55) 0px 0px 0px 1px inset;
  cursor: default;
  transition:
    background-color 140ms,
    border-color 140ms;
}
.new-comment-border[data-state='focus-within'] {
  box-shadow:
    rgb(25, 83, 199) 0px 0px 0px 1px inset,
    rgb(49, 51, 55) 0px 0px 0px 1px;
}

:where([data-radix-scroll-area-viewport]) {
  display: flex;
  flex-direction: column;
  align-items: stretch;
}

/* ///////// */

input,
textarea,
[contenteditable='true'] {
  caret-color: rgb(38, 109, 240);
}
</style>
