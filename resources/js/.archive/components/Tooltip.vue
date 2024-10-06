<template>
    <div class="pf-c-tooltip pf-m-top-left" role="tooltip" style="z-index: 999; position: absolute; top: 26%">
        <div class="pf-c-tooltip__arrow"></div>
        <div class="pf-c-tooltip__content" id="tooltip-top-content">
            <slot></slot>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue';

export default {
    props: {},

    setup(props) {
        return {};
    }
};
</script>
