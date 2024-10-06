<template>
    <div class="pf-c-card pf-m-expanded">
        <div class="pf-c-card__header">
            <div class="pf-c-card__actions">
                <button
                    class="pf-c-button pf-m-plain"
                    @click="removeDashboardHelpCard()"
                    alt="Click to remove this help card from the dashboard!"
                    title="Click to remove this help card from the dashboard!"
                    style="cursor: help"
                >
                    <span class="pf-c-button__icon">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </span>
                </button>
            </div>
            <div class="pf-c-card__title">Getting started with V6 Core</div>
        </div>
        <help-content></help-content>
    </div>
</template>

<script>
import { ref } from 'vue';
import HelpContent from '../../components/HelpContent.vue';

export default {
    props: {},
    components: { HelpContent },

    setup(props, { emit }) {
        function removeDashboardHelpCard() {
            localStorage.setItem('rconfig.dashboardHelpCardShow', false);
            emit('removeDashboardHelpCard');
        }

        return { removeDashboardHelpCard };
    }
};
</script>
