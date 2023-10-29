<template>
  <div class="pf-c-backdrop">
    <div class="pf-l-bullseye">
      <div
        class="pf-c-modal-box pf-m-sm pf-m-warning"
        role="dialog"
        ref="clickOutsidetargetModal">
        <button
          class="pf-c-button pf-m-plain"
          type="button"
          aria-label="Close"
          @click="close">
          <i
            class="fas fa-times"
            aria-hidden="true"></i>
        </button>
        <header class="pf-c-modal-box__header">
          <h1
            class="pf-c-modal-box__title"
            id="modal-title-modal-basic-example-modal">
            Clear device logs for {{ editid }}?
          </h1>
        </header>

        <div
          class="pf-c-modal-box__body"
          id="modal-description">
          <p>Are you absolutely sure you want to clear all logs device? You will not be able to retrieve cleared logs after this operation.</p>
        </div>
        <footer class="pf-c-modal-box__footer">
          <button
            class="pf-c-button pf-m-primary pf-m-small"
            type="button"
            @click="confirmClear()">
            Confirm
          </button>
          <button
            class="pf-c-button pf-m-link"
            type="button"
            @click="close()">
            Cancel
          </button>
        </footer>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import { onClickOutside } from '@vueuse/core';

export default {
  props: {
    editid: {
      type: [Number, Array, String, Object],
      required: true
    }
  },

  setup(props, { emit }) {
    const clickOutsidetargetModal = ref(null);

    onClickOutside(clickOutsidetargetModal, event => close());

    function close() {
      emit('closeModal');
    }

    function confirmClear() {
      emit('confirmClear', props.editid);
    }

    return {
      clickOutsidetargetModal,
      close,
      confirmClear
    };
  }
};
</script>
