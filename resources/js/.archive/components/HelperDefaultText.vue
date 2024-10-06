<template>
    <div class="pf-c-helper-text" v-if="show">
        <div class="pf-c-helper-text__item">
            <span class="pf-c-helper-text__item-text" style="padding-left: 5px">{{ message }}</span>
        </div>
    </div>
</template>

<script>
import {} from 'vue';

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
