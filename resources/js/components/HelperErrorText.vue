<template>
    <div class="pf-c-helper-text__item pf-m-error pf-u-danger-color-100" v-if="show">
        <span class="pf-c-helper-text__item-icon">
            <i class="fas fa-fw fa-exclamation-circle" aria-hidden="true"></i>
        </span>
        <span class="pf-c-helper-text__item-text" v-if="show" style="padding-left: 5px">{{ message }}</span>
    </div>
</template>

<script>
import { ref } from 'vue';

export default {
    props: {
        show: { type: Boolean, default: false },
        message: { type: String, default: '' }
    },

    setup(props) {
        return {};
    }
};
</script>
