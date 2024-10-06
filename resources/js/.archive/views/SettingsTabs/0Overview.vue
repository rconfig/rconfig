<template>
    <div class="pf-c-panel pf-m-raised">
        <div class="pf-c-panel__header">System Overview</div>
        <hr class="pf-c-divider" />
        <div class="pf-c-panel__main">
            <div class="pf-c-panel__main-body">
                <div class="pf-c-card pf-m-expanded">
                    <div class="pf-c-card__header">
                        <div class="pf-c-card__title">Getting started with V6 Core</div>
                    </div>
                    <help-content></help-content>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue';
import HelpContent from '../../components/HelpContent.vue';

export default {
    props: {},
    components: { HelpContent },

    setup(props) {
        return {};
    }
};
</script>
