<script setup>
import { ref, inject } from 'vue';

const props = defineProps({});

const formatters = inject('formatters');
const username = inject('username');
const dataState = ref('default');
const showPlaceholder = ref(true);

// Function to handle focus/click
function handleFocus() {
  dataState.value = 'focus-within';
}

// Function to handle blur (click away)
function handleBlur() {
  dataState.value = 'default';
}
function handleInput(event) {
  const content = event.target.textContent.trim(); // Get the text content of the contenteditable
  showPlaceholder.value = content === ''; // Show placeholder only if content is empty
}
</script>

<template>
  <div style="transform: translateY(0px)">
    <div
      tabindex="0"
      @click="handleFocus"
      @focusout="handleBlur"
      class="sc-goMudc bRfJSJ sc-fkQHyZ cISAYm"
      aria-disabled="false"
      :data-state="dataState">
      <div class="sc-hbHicI fRjdVJ">
        <div
          dir="ltr"
          class="sc-fopvai JlwlM"
          style="position: relative; --radix-scroll-area-corner-width: 0px; --radix-scroll-area-corner-height: 0px">
          <div
            data-radix-scroll-area-viewport=""
            class="sc-ivtqZH dPbOdM sc-iuGutQ ddqiiE"
            style="overflow: hidden scroll">
            <div data-radix-scroll-area-content="">
              <div
                aria-hidden="true"
                class="sc-eFtBfi ewXSJs"></div>
              <div class="sc-beySPh fHovtt sc-ceBzPL btJxMM">
                <span class="relative inline-flex items-center justify-center w-4 h-4 overflow-hidden rounded-full">
                  <span class="flex items-center justify-center w-full h-full bg-blue-700 text-blue-100 font-semibold text-[10px] uppercase">{{ formatters.getFirstLetterCapitalized(username) }}</span>
                </span>
                <div class="sc-jDHDkZ QINEF">
                  <div class="sc-iUrAJr gdPCgy sc-hezcEq kQlPDI">
                    <div
                      class="sc-kDugJs mdVCi"
                      contenteditable="true"
                      role="textbox"
                      spellcheck="false"
                      style="user-select: text; white-space: pre-wrap; word-break: break-word"
                      data-lexical-editor="true"
                      @input="handleInput"
                      data-ms-editor="true">
                      <p><br /></p>
                    </div>
                    <div
                      v-if="showPlaceholder"
                      class="absolute top-0 left-0 font-sans text-sm font-medium leading-5 tracking-tight text-gray-500 pointer-events-none select-none sc-bCNnoN">
                      Add comment...
                    </div>
                  </div>
                </div>
              </div>
              <div
                aria-hidden="true"
                class="sc-eFtBfi ewXSJs"></div>
            </div>
          </div>
        </div>
        <div
          data-visible="false"
          data-position="bottom"
          aria-hidden="true"
          class="sc-bgykEL gibCdC"></div>
        <div class="sc-beySPh bzjAQf">
          <div class="sc-beySPh koquBe">
            <button
              type="button"
              aria-label="Mention"
              class="sc-dQmiwx hmLgnQ">
              <div
                data-is-small-ghost="true"
                class="sc-iVheDh cwjVZq">
                <Icon
                  icon="meteor-icons:at"
                  class="text-sm text-muted-foreground"></Icon>
              </div>
            </button>
          </div>
          <button
            v-if="dataState === 'default'"
            type="button"
            class="sc-dQmiwx bCTnUd">
            <div class="sc-jMbVJB ksLAyz">Comment</div>
          </button>
          <button
            v-if="dataState === 'focus-within'"
            type="button"
            class="sc-dQmiwx ihjlUO">
            <div class="sc-jMbVJB ksLAyz">Comment</div>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.cISAYm {
  margin-top: 8px;
}
.bRfJSJ {
  border-radius: 16px;
  background-color: rgb(33, 35, 39);
  box-shadow: rgb(49, 51, 55) 0px 0px 0px 1px inset;
  cursor: default;
  transition:
    background-color 140ms,
    border-color 140ms;
}
.bRfJSJ[data-state='focus-within'] {
  box-shadow:
    rgb(25, 83, 199) 0px 0px 0px 1px inset,
    rgb(49, 51, 55) 0px 0px 0px 1px;
}
.fRjdVJ {
  max-height: 25vh;
  min-height: 0px;
  display: flex;
  flex-direction: column;
}
.JlwlM {
  overflow: hidden;
  width: 100%;
  height: 100%;
  position: relative;
  display: flex;
  flex-direction: column;
}
.gibCdC[data-position='top'],
.gibCdC[data-position='bottom'] {
  width: 100%;
  height: 1px;
}
.gibCdC[data-visible='false'] {
  opacity: 0;
}
.gibCdC {
  flex: 0 0 auto;
  width: 100%;
  height: 1px;
  background-color: rgba(244, 245, 246, 0.04);
  transition: opacity 140ms;
  opacity: 1;
}
.bzjAQf {
  display: flex;
  flex: 0 0 auto;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding: 4px 8px 8px 12px;
}
.koquBe {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: flex-start;
  gap: 8px;
}
.bCTnUd {
  --button-background-base: #266df0;
  --button-background-hover: #407ff2;
  --button-background-active: #407ff2;
  --button-shadow: inset 0px 0px 0px 1px rgba(244, 245, 246, 0.1);
  margin: 0px;
  outline: none;
  border: none;
  text-decoration: none;
  position: relative;
  display: flex;
  flex-shrink: 0;
  flex-grow: 0;
  align-items: center;
  justify-content: center;
  opacity: 0.4;
  cursor: default;
  gap: 6px;
  background: var(--button-background-base);
  padding: 6px 10px;
  border-radius: 9px;
  box-shadow: var(--button-shadow);
  transition:
    background-color 0.2s,
    color 0.2s,
    box-shadow 0.2s;
}
.ksLAyz {
  display: inline-block;
  vertical-align: bottom;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: 14px;
  line-height: 20px;
  font-weight: 500;
  letter-spacing: -0.02em;
  color: rgb(229, 238, 255);
}
.ihjlUO {
  --button-background-base: #266df0;
  --button-background-hover: #407ff2;
  --button-background-active: #407ff2;
  --button-shadow: inset 0px 0px 0px 1px rgba(244, 245, 246, 0.1);
  margin: 0px;
  outline: none;
  border: none;
  text-decoration: none;
  position: relative;
  display: flex;
  flex-shrink: 0;
  flex-grow: 0;
  align-items: center;
  justify-content: center;
  opacity: 1;
  cursor: pointer;
  gap: 6px;
  background: var(--button-background-base);
  padding: 6px 10px;
  border-radius: 9px;
  box-shadow: var(--button-shadow);
  transition:
    background-color 0.2s,
    color 0.2s,
    box-shadow 0.2s;
}
.ddqiiE {
  flex: 1 1 auto;
  min-height: 0px;
  max-height: 400px;
  padding: 12px 12px 0px 0px;
}

.dPbOdM {
  display: flex;
  flex-direction: column;
  width: 100%;
  height: 100%;
}
:where([data-radix-scroll-area-viewport]) {
  display: flex;
  flex-direction: column;
  align-items: stretch;
}
.ewXSJs {
  width: 100%;
}
.btJxMM {
  padding-left: 14px;
}
.fHovtt {
  display: flex;
  flex-direction: row;
  align-items: flex-start;
  justify-content: flex-start;
  gap: 8px;
  padding-bottom: 4px;
}

.QINEF {
  display: flex;
  flex-direction: column;
  flex: 1 1 0%;
  min-width: 0px;
}

.ihGDqD {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  cursor: default;
  user-select: none;
  background-color: rgb(38, 109, 240);
  color: rgb(229, 238, 255);
  font-weight: 500;
  font-size: 10px;
  text-transform: uppercase;
  line-height: 1;
  transition: background-color 0.2s;
}
.kQlPDI {
  position: relative;
  font-family: Inter;
  letter-spacing: -0.02em;
  font-weight: 500;
  line-height: 20px;
  font-size: 14px;
  color: rgb(238, 239, 241);
  font-feature-settings: 'liga', 'calt';
}
input,
textarea,
[contenteditable='true'] {
  caret-color: rgb(38, 109, 240);
}
.mdVCi {
  outline: none;
}
</style>
