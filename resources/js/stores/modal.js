// src/stores/modal.js
import { defineStore } from 'pinia';
import { reactive } from 'vue';

export const useModalStore = defineStore('modal', () => {
  // State: An object where keys are modal names and values are booleans
  const modals = reactive({});

  // Actions
  function openModal(name) {
    modals[name] = true;
  }

  function closeModal(name) {
    modals[name] = false;
  }

  function toggleModal(name) {
    modals[name] = !modals[name];
  }

  // Getter (optional): Check if a modal is open
  function isModalOpen(name) {
    return modals[name] || false;
  }

  return { modals, openModal, closeModal, toggleModal, isModalOpen };
});
