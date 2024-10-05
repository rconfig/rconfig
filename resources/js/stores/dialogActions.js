// src/stores/dialog.js
import { defineStore } from 'pinia';
import { reactive } from 'vue';

export const useDialogStore = defineStore('dialog', () => {
  // State: An object where keys are dialog names and values are booleans
  const dialogs = reactive({});

  // Actions
  function openDialog(name) {
    dialogs[name] = true;
  }

  function closeDialog(name) {
    dialogs[name] = false;
  }

  function toggleDialog(name) {
    dialogs[name] = !dialogs[name];
  }

  // Getter (optional): Check if a dialog is open
  function isDialogOpen(name) {
    return dialogs[name] || false;
  }

  return { dialogs, openDialog, closeDialog, toggleDialog, isDialogOpen };
});
