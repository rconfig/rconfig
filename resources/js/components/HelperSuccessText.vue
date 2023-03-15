<template>
    <div class="pf-c-helper-text__item pf-m-success pf-u-success-color-100" v-if="show">
        <span class="pf-c-helper-text__item-icon" aria-hidden="true"
            ><svg fill="currentColor" height="1em" width="1em" viewBox="0 0 512 512" aria-hidden="true" role="img" style="vertical-align: -0.125em">
                <path
                    d="M504 256c0 136.967-111.033 248-248 248S8 392.967 8 256 119.033 8 256 8s248 111.033 248 248zM227.314 387.314l184-184c6.248-6.248 6.248-16.379 0-22.627l-22.627-22.627c-6.248-6.249-16.379-6.249-22.628 0L216 308.118l-70.059-70.059c-6.248-6.248-16.379-6.248-22.628 0l-22.627 22.627c-6.248 6.248-6.248 16.379 0 22.627l104 104c6.249 6.249 16.379 6.249 22.628.001z"
                ></path></svg></span
        ><span class="pf-c-helper-text__item-text" v-if="show" style="padding-left: 5px"> {{ message }} </span>
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
