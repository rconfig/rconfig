<template>
    <div class="pf-c-backdrop">
        <div class="pf-l-bullseye">
            <div class="pf-c-modal-box" style="--pf-c-modal-box--Width: 80rem">
                <slot name="wizard"></slot>
            </div>
        </div>
    </div>
</template>

<script>
import {} from 'vue';

export default {
    props: {},

    setup(props, { emit }) {
        function close() {
            emit('closeModal');
        }

        function action1() {
            emit('action1');
            close();
        }

        return { close, action1 };
    }
};
</script>
