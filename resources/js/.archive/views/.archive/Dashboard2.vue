<template>
    <main class="pf-c-page__main" tabindex="-1">
        <page-header :pagename="pagename" :desc="pagedesc"></page-header>
        <div class="pf-c-divider" role="separator"></div>
        <section class="pf-c-page__main-section pf-m-no-padding">
            <!-- Drawer -->

            <div class="pf-c-drawer pf-m-expanded pf-m-inline-on-2xl">
                <div class="pf-c-drawer__main">
                    <!-- Content -->
                    <div class="pf-c-drawer__content pf-m-no-background">
                        <div class="pf-c-drawer__body pf-m-padding">
                            <div class="pf-l-grid pf-m-gutter">
                                <div class="pf-l-grid__item pf-m-12-col pf-m-3-col-on-md">
                                    <server-details></server-details>
                                </div>
                                <div class="pf-l-grid__item pf-m-12-col pf-m-6-col-on-md">
                                    <config-status-card></config-status-card>
                                    <env-status-card class="pf-u-mt-md"></env-status-card>
                                    <getting-started
                                        v-if="gettingStartedShow !== 'false'"
                                        @removeDashboardHelpCard="gettingStartedShow = 'false'"
                                        class="pf-u-mt-md pf-l-grid__item pf-m-all-6-col-on-sm pf-m-all-4-col-on-md pf-m-all-2-col-on-lg pf-m-all-1-col-on-xl"
                                    ></getting-started>
                                </div>
                                <common-actions-menu class="pf-l-grid__item pf-m-12-col pf-m-3-col-on-md"></common-actions-menu>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</template>

<script>
import CommonActionsMenu from './DashboardCards/CommonActionsMenu.vue';
import ConfigStatusCard from './DashboardCards/ConfigStatusCard.vue';
import EnvStatusCard from './DashboardCards/EnvStatusCard.vue';
import GettingStarted from './DashboardCards/GettingStarted.vue';
import PageHeader from '../components/PageHeader.vue';
import ServerDetails from './DashboardCards/ServerDetails.vue';
import { onMounted, ref, watchEffect } from 'vue';

export default {
    components: {
        CommonActionsMenu,
        ConfigStatusCard,
        EnvStatusCard,
        GettingStarted,
        PageHeader,
        ServerDetails
    },
    setup() {
        const pagename = ref('Dashboard');
        const pagedesc = ref('Overview of rConfig activity and statistics');
        const gettingStartedShow = ref(true);

        onMounted(() => {
            gettingStartedShow.value = localStorage.getItem('rconfig.dashboardHelpCardShow');
        });

        return {
            pagename,
            pagedesc,
            gettingStartedShow
        };
    }
};
</script>
