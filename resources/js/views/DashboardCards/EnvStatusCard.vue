<template>
    <div class="pf-c-card">
        <div class="pf-c-card__header">
            <div class="pf-c-card__actions">
                <button class="pf-c-button pf-m-plain" @click="refreshData()">
                    <span class="pf-c-button__icon">
                        <i class="fas fa-redo" aria-hidden="true" v-if="!isRefreshing"></i>

                        <span class="pf-c-spinner pf-m-md" role="progressbar" aria-valuetext="Loading..." style="--pf-c-spinner--Color: #6a6e72" v-if="isRefreshing"
                            ><span class="pf-c-spinner__clipper"></span><span class="pf-c-spinner__lead-ball"></span><span class="pf-c-spinner__tail-ball"></span
                        ></span>
                    </span>
                </button>
            </div>
            <div class="pf-c-card__title">
                <h2 class="pf-c-title pf-m-lg">Environment Status</h2>
            </div>
        </div>
        <div class="pf-c-card__body">
            <div class="pf-c-skeleton" v-if="isLoading"></div>

            <div class="pf-l-grid pf-m-all-6-col-on-sm pf-m-all-3-col-on-lg pf-m-gutter" v-if="!isLoading">
                <div class="pf-l-grid__item" v-for="healthCheck in healthChecks" :key="healthCheck.name">
                    <div class="pf-l-flex pf-m-space-items-sm">
                        <div class="pf-l-flex__item">
                            <i class="fas fa-check-circle pf-u-success-color-100" v-if="healthCheck.notificationMessage === 'ok'"></i>
                            <i class="fas fa-exclamation-triangle pf-u-warning-color-100" v-if="healthCheck.notificationMessage === 'warning'"></i>
                            <i class="fas fa-exclamation-circle pf-u-danger-color-100" v-if="healthCheck.notificationMessage === 'failed'"></i>
                        </div>
                        <div class="pf-l-flex__item">
                            <span>{{ healthCheck.label }}</span>
                        </div>
                        <div class="pf-l-flex__item pf-u-font-size-sm">
                            <span class="pf-u-color-300"
                                >{{ healthCheck.status }} <span v-if="healthCheck.shortSummary != ''"> - {{ healthCheck.shortSummary }}</span></span
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr class="pf-c-divider" v-if="failedItems != 0" />
        <div class="pf-c-card__footer" v-if="failedItems != 0" style="padding-top: 10px; padding-bottom: 10px">
            <router-link to="/settings/overview">View Help</router-link>
        </div>
    </div>
</template>

<script>
import { ref, reactive, inject, onMounted } from 'vue';

export default {
    props: {},

    setup(props) {
        const isLoading = ref(true);
        const isRefreshing = ref(false);
        const healthChecks = reactive([]);
        const failedItems = ref(0);
        const createNotification = inject('create-notification');

        onMounted(() => {
            getLatestHealth();
        });

        function refreshData() {
            console.log('refreshing environment details...');
            isRefreshing.value = true;
            getLatestHealth();
        }

        function getLatestHealth() {
            isLoading.value = true;
            axios
                .get('/api/dashboard/health-latest')
                .then((response) => {
                    Object.assign(healthChecks, response.data.data);
                    var failedItemsChk = healthChecks.filter((item) => item.notificationMessage.toLowerCase().includes('failed'));
                    var failedItemsChk2 = healthChecks.filter((item) => item.notificationMessage.toLowerCase().includes('warning'));
                    if (failedItemsChk.length > 0 || failedItemsChk2.length > 0) {
                        failedItems.value = failedItemsChk.length;
                    }
                    isRefreshing.value = false;
                    isLoading.value = false;
                })
                .catch((error) => {
                    createNotification({
                        type: 'danger',
                        title: 'Error',
                        message: error.response
                    });
                });
        }

        return {
            failedItems,
            healthChecks,
            isLoading,
            isRefreshing,
            refreshData
        };
    }
};
</script>
