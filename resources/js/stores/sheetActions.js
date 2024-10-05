// src/stores/sheet.js
import { defineStore } from 'pinia';
import { reactive } from 'vue';

export const useSheetStore = defineStore('sheet', () => {
  // State: An object where keys are sheet names and values are booleans
  const sheets = reactive({});

  // Actions
  function openSheet(name) {
    sheets[name] = true;
  }

  function closeSheet(name) {
    sheets[name] = false;
  }

  function toggleSheet(name) {
    sheets[name] = !sheets[name];
  }

  // Getter (optional): Check if a sheet is open
  function isSheetOpen(name) {
    return sheets[name] || false;
  }

  return { sheets, openSheet, closeSheet, toggleSheet, isSheetOpen };
});
