<template>
    <div class="pf-c-backdrop">
        <div class="pf-l-bullseye">
            <div class="pf-c-modal-box pf-m-sm pf-m-warning" role="dialog" ref="clickOutsidetargetDeleteModal">
                <button class="pf-c-button pf-m-plain" type="button" aria-label="Close dialog" @click="close">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
                <header class="pf-c-modal-box__header">
                    <h1 class="pf-c-modal-box__title" id="warning-alert-title">
                        <span class="pf-c-modal-box__title-text">Delete Record with ID {{ editid }}?</span>
                    </h1>
                </header>
                <div class="pf-c-modal-box__body" id="modal-description">
                    <p>Are you absolutley sure you want to delete this record? You may not be able to retrieve it after.</p>
                </div>
                <footer class="pf-c-modal-box__footer">
                    <button class="pf-c-button pf-m-primary pf-m-small" type="button" @click="confirmDelete()">Confirm</button>
                    <button class="pf-c-button pf-m-link" type="button" @click="close()">Cancel</button>
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
        const clickOutsidetargetDeleteModal = ref(null);

        onClickOutside(clickOutsidetargetDeleteModal, (event) => close());

        function close() {
            emit('closeModal');
        }

        function confirmDelete() {
            emit('confirmDelete', props.editid);
        }

        return {
            clickOutsidetargetDeleteModal,
            close,
            confirmDelete
        };
    }
};
</script>
