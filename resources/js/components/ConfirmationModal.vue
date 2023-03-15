<template>
    <div class="pf-c-backdrop">
        <div class="pf-l-bullseye">
            <div class="pf-c-modal-box pf-m-sm" ref="clickOutsidetargetConfirmModal">
                <button class="pf-c-button pf-m-plain" type="button" aria-label="Close dialog" @click="close">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
                <header class="pf-c-modal-box__header">
                    <h1 class="pf-c-modal-box__title" id="modal-md-title">
                        <slot name="title"></slot>
                    </h1>
                </header>
                <div class="pf-c-modal-box__body">
                    <p id="modal-md-description">
                        <slot name="question"></slot>
                    </p>
                </div>
                <footer class="pf-c-modal-box__footer">
                    <button class="pf-c-button pf-m-primary" type="button" @click.prevent="action1"><slot name="action1"></slot></button>
                    <button class="pf-c-button pf-m-link" type="button" @click.prevent="close" v-if="slots.action2"><slot name="action2"></slot></button>
                    <button class="pf-c-button pf-m-link" type="button" @click.prevent="action3" v-if="slots.action3"><slot name="action3"></slot></button>
                </footer>
            </div>
        </div>
    </div>
</template>

<script>
import { useSlots, ref } from 'vue';
import { onClickOutside } from '@vueuse/core';

export default {
    props: {},

    setup(props, { emit }) {
        const clickOutsidetargetConfirmModal = ref(null);
        const slots = useSlots();

        onClickOutside(clickOutsidetargetConfirmModal, (event) => close());

        function close() {
            emit('closeModal');
        }

        function action1() {
            emit('action1');
            close();
        }

        function action3() {
            emit('action3');
        }

        return { slots, close, action1, action3, clickOutsidetargetConfirmModal };
    }
};
</script>
